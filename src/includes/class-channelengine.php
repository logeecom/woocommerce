<?php

namespace ChannelEngine;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use ChannelEngine\BusinessLogic\Authorization\Contracts\AuthorizationService;
use ChannelEngine\BusinessLogic\Cancellation\Domain\CancellationItem;
use ChannelEngine\BusinessLogic\Cancellation\Domain\CancellationRequest;
use ChannelEngine\BusinessLogic\Cancellation\Handlers\CancellationRequestHandler;
use ChannelEngine\BusinessLogic\Notifications\Contracts\NotificationService;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Shipments\Contracts\ShipmentsService;
use ChannelEngine\BusinessLogic\Shipments\Domain\CreateShipmentRequest;
use ChannelEngine\BusinessLogic\Shipments\Handlers\ShipmentsCreateRequestHandler;
use ChannelEngine\BusinessLogic\Webhooks\Contracts\WebhooksService;
use ChannelEngine\Components\Bootstrap_Component;
use ChannelEngine\Components\Exceptions\Cancellation_Rejected_Exception;
use ChannelEngine\Components\Exceptions\Shipment_Rejected_Exception;
use ChannelEngine\Components\Hooks\Product_Hooks;
use ChannelEngine\Components\Services\Plugin_Status_Service;
use ChannelEngine\Controllers\Channel_Engine_Frontend_Controller;
use ChannelEngine\Controllers\Channel_Engine_Index;
use ChannelEngine\Controllers\Channel_Engine_Order_Overview_Controller;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Migrations\Exceptions\Migration_Exception;
use ChannelEngine\Repositories\Plugin_Options_Repository;
use ChannelEngine\Utility\Currency_Check;
use ChannelEngine\Utility\Database;
use ChannelEngine\Utility\Logging_Callable;
use ChannelEngine\Utility\Shop_Helper;
use ChannelEngine\Utility\View;
use Exception;
use WC_Logger;
use WC_Order;
use WC_Shipping_Zones;
use WP_Post;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );


class ChannelEngine {

	const VERSION = '1.0.0';

	/**
	 * @var ChannelEngine
	 */
	protected static $instance;

	/**
	 * @var string
	 */
	private $channelengine_plugin_file;

	/**
	 * @var Database
	 */
	private $database;
	/**
	 * Flag that signifies that the plugin is initialized.
	 *
	 * @var bool
	 */
	private $is_initialized = false;

	/**
	 * ChannelEngine_Plugin constructor.
	 *
	 * @param string $channelengine_plugin_file
	 */
	private function __construct( $channelengine_plugin_file ) {
		$this->channelengine_plugin_file = $channelengine_plugin_file;
		$this->database                  = new Database( new Plugin_Options_Repository() );
	}

	/**
	 * Initialize the plugin and returns instance of the plugin
	 *
	 * @param $channelengine_plugin_file
	 *
	 * @return ChannelEngine
	 */
	public static function init( $channelengine_plugin_file ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $channelengine_plugin_file );
		}

		self::$instance->initialize();

		return self::$instance;
	}

	/**
	 * Defines global constants and hooks actions to appropriate events
	 */
	private function initialize() {
		if ( $this->is_initialized ) {
			return;
		}

		$this->channelengine_bootstrap();

		add_action( 'add_meta_boxes', array( $this, 'add_channel_engine_overview_box' ), 10, 2 );
		register_deactivation_hook( $this->channelengine_plugin_file, array( $this, 'deactivate' ) );
		add_action( 'init', new Logging_Callable( array( $this, 'channelengine_init' ) ) );
		add_filter( 'query_vars', array( $this, 'plugin_add_trigger' ) );
		add_action( 'template_redirect', array( $this, 'plugin_trigger_check' ) );
		add_action( 'plugins_loaded', new Logging_Callable( array( $this, 'channelengine_bootstrap' ) ) );
		add_action( 'woocommerce_before_order_object_save', array( $this, 'before_order_object_save' ) );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'before_order_cancel_status_transition' ) );
		add_filter( 'post_updated_messages', array( $this, 'change_order_success_message' ), 20 );
		add_action( 'admin_notices', array( $this, 'render_notifications' ) );
		add_action( 'woocommerce_loaded', array( $this, 'add_order_change_hook' ) );
		add_action( 'wp_loaded', array( $this, 'update_database' ) );
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( FeaturesUtil::class ) ) {
					FeaturesUtil::declare_compatibility( 'custom_order_tables', static::get_plugin_dir_path(), true );
				}
			}
		);

		try {
			$auth_info = $this->get_auth_service()->getAuthInfo();
			if ( $auth_info ) {
				add_action( 'update_option_woocommerce_currency', array( $this, 'check_currency' ), 10, 2 );
			}
		} catch ( BaseException $e ) {
			// Client has not connected their account yet, so we cannot check if woocommerce currency
			// is the same as the one on ChannelEngine.
			if ( class_exists( WC_Logger::class ) ) {
				Logger::logInfo( 'Client has not connected their account yet.' );
			}
		}

		if ( is_multisite() ) {
			add_action( 'delete_blog', array( $this, 'uninstall_plugin_from_deleted_site' ) );
		}

		if ( Shop_Helper::is_plugin_enabled() ) {
			$this->load_channel_engine_admin_menu();
			$this->add_plugin_action_links();
			$this->add_plugin_hooks();
		}

		$this->is_initialized = true;
	}

	public function add_order_change_hook() {

		$config = $this->get_order_config_service()->getOrderSyncConfig();
		if ( $config ) {
			$order_statuses = wc_get_order_statuses();
			$status         = $order_statuses[ $config->getShippedOrders() ];
			add_action(
				'woocommerce_order_status_' . $status,
				array(
					$this,
					'before_order_shipped_status_transition',
				)
			);
		}
	}

	/**
	 * @param $messages
	 *
	 * @return array
	 */
	public function change_order_success_message( $messages ) {
		$handler_notification = get_option( '_channel_engine_order_save_note' );

		if ( $handler_notification ) {
			/**
			 * When order is saved, success message is always shown.
			 * In order to stop WooCommerce from displaying success message
			 * if order save has failed, we have to unset that message.
			 *
			 * @see WC_Admin_Post_Types::post_updated_messages
			 */
			unset( $messages['shop_order'][1] );
		}

		return $messages;
	}

	public function update_database() {
		try {
			$this->database->update( is_multisite() );
		} catch ( Migration_Exception $e ) {
			Logger::logError( 'Failed to update database because ' . $e->getMessage() );
		}
	}

	/**
	 * Action on plugin loaded.
	 */
	public function channelengine_bootstrap() {
		Bootstrap_Component::init();
	}

	/**
	 * Returns base directory path
	 *
	 * @return string
	 */
	public static function get_plugin_dir_path() {
		return rtrim( plugin_dir_path( __DIR__ ), '/' );
	}

	/**
	 * Returns url for the provided directory
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public static function get_plugin_url( $path ) {
		return rtrim( plugins_url( "/{$path}/", __DIR__ ), '/' );
	}

	/**
	 * Loads translations
	 */
	public function channelengine_init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		load_plugin_textdomain( 'channelengine-integration', false, basename( dirname( $this->channelengine_plugin_file ) ) . '/i18n/languages/' );
	}

	/**
	 * Adds ChannelEngine item to backend administrator menu.
	 */
	public function load_channel_engine_admin_menu() {
		if ( is_admin() && ! is_network_admin() ) {
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
		}
	}

	/**
	 * Creates ChannelEngine item in administrator menu.
	 */
	public function create_admin_menu() {
		$controller = new Channel_Engine_Frontend_Controller();
		add_submenu_page(
			'woocommerce',
			'ChannelEngine',
			'ChannelEngine',
			'manage_options',
			'channel-engine',
			array( $controller, 'render' )
		);
	}

	/**
	 * Adds plugin action links.
	 */
	public function add_plugin_action_links() {
		add_filter(
			'plugin_action_links_' . plugin_basename( Shop_Helper::get_plugin_name() ),
			array(
				$this,
				'channel_engine_action_links',
			)
		);
	}

	/**
	 * Adds ChannelEngine action links.
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function channel_engine_action_links( $actions ) {
		$actions[] = '<a href="' . Shop_Helper::get_plugin_page_url() . '">Settings</a>';

		return $actions;
	}

	/**
	 * Adds ChannelEngine query variable.
	 *
	 * @param array $vars Filter variables.
	 *
	 * @return array Filter variables.
	 */
	public function plugin_add_trigger( $vars ) {
		$vars[] = 'channel_engine_controller';

		return $vars;
	}

	/**
	 * Trigger action on calling plugin controller.
	 */
	public function plugin_trigger_check() {
		$controller_name = get_query_var( 'channel_engine_controller' );
		if ( ! empty( $controller_name ) ) {
			$controller = new Channel_Engine_Index();
			$controller->index_admin();
		}
	}

	/**
	 * Adds ChannelEngine order overview meta post box.
	 *
	 * @param string $page
	 * @param WP_Post $post
	 */
	public function add_channel_engine_overview_box( $page, $post ) {
		if ( ( 'shop_order' === $page && $post && $post->__isset( '_channel_engine_order_id' ) )
			 || ( 'woocommerce_page_wc-orders' === $page && $post instanceof WC_Order && $post->get_meta( '_channel_engine_order_id' ) ) ) {
			$controller = new Channel_Engine_Order_Overview_Controller();
			$screen     = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
				? wc_get_page_screen_id( 'shop-order' )
				: 'shop_order';
			add_meta_box(
				'channel-engine-order-overview',
				'ChannelEngine',
				function ( $data ) use ( $controller ) {
					$data_id = $data->ID;
					$controller->render( $data_id );
				},
				$screen,
				'side',
				'core'
			);
		}
	}

	/**
	 * Plugin deactivation function.
	 *
	 * @param bool $is_network_wide Is plugin network wide.
	 */
	public function deactivate( $is_network_wide ) {
		if ( ! Shop_Helper::is_woocommerce_active() ) {
			return;
		}

		if ( $is_network_wide && is_multisite() ) {
			foreach ( get_sites() as $site ) {
				switch_to_blog( $site->blog_id );
				$this->get_plugin_status_service()->disable();
				restore_current_blog();
			}
		} else {
			$this->get_plugin_status_service()->disable();
		}
	}

	/**
	 * Checks if new store currency is same as channel engine currency.
	 *
	 * @param $old_value
	 * @param $new_value
	 */
	public function check_currency( $old_value, $new_value ) {
		if ( ! Currency_Check::match( $new_value ) ) {
			$this->get_plugin_status_service()->disable();
		}
	}

	/**
	 * Plugin uninstall method.
	 */
	public function uninstall() {
		if ( is_multisite() ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				$this->switch_to_site_and_uninstall_plugin( $site->blog_id );
			}
		} else {
			$this->uninstall_plugin_from_site();
			delete_option( 'CHANNELENGINE_SCHEMA_VERSION' );
		}
	}

	/**
	 * Hook that triggers when network site is deleted
	 * and removes plugin data related to that site from the network.
	 *
	 * @param int $site_id Site identifier.
	 */
	public function uninstall_plugin_from_deleted_site( $site_id ) {
		$this->switch_to_site_and_uninstall_plugin( $site_id );
	}

	/**
	 * Renders ChannelEngine notifications.
	 */
	public function render_notifications() {
		$notifications = $this->get_notification_service()->find( array( 'isRead' => false ) );

		if ( $notifications ) {
			$notification = end( $notifications );
			echo wp_kses(
				'<div class="notice notice-warning"><p><strong>' .
						  __( 'ChannelEngine', 'channelengine-integration' ) . '</strong> ' .
						vsprintf(
							sprintf( '%s', $notification->getMessage() ),
							$notification->getArguments()
						) . ' <a href="' . Shop_Helper::get_plugin_page_url() . '">'
				. __( 'Show details.', 'channelengine-integration' ) . '</a></p></div>',
				View::get_allowed_tags()
			);
		}

		$handler_notification = get_option( '_channel_engine_order_save_note' );

		if ( $handler_notification ) {
			echo wp_kses(
				'<div class="notice notice-error"><p>' .
				sprintf( '%s', $handler_notification ) . '</p></div>',
				View::get_allowed_tags()
			);

			delete_option( '_channel_engine_order_save_note' );
		}

		$handler_success = get_option( '_channel_engine_order_save_success' );

		if ( $handler_success ) {
			echo wp_kses(
				'<div class="notice notice-success"><p>' .
				sprintf( '%s', $handler_success ) . '</p></div>',
				View::get_allowed_tags()
			);

			delete_option( '_channel_engine_order_save_success' );
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function before_order_object_save( WC_Order $order ) {
		if ( ! $this->get_plugin_status_service()->is_enabled() ) {
			return;
		}

		$ce_order_id = $order->get_meta( '_channel_engine_order_id' );

		if ( ! $ce_order_id ) {
			return;
		}

		$order_config = $this->get_order_config_service()->getOrderSyncConfig();

		if ( $order_config && 'cancelled' === strtolower( $order->get_status() ) && $order_config->isEnableOrderCancellationSync() ) {
			$this->handle_order_cancellation( $order );
		}

		if ( $order_config && wc_get_order_status_name( $order->get_status() ) === wc_get_order_status_name( $order_config->getShippedOrders() ) && $order_config->isEnableShipmentInfoSync() ) {
			$this->handle_order_shipment( $order );
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 */
	public function handle_order_cancellation( WC_Order $order ) {
		if ( $order->get_meta( '_ce_order_cancelled' ) ) {
			return;
		}

		$request = new CancellationRequest(
			$order->get_id(),
			$order->get_id(),
			$this->get_cancellation_items( $order ),
			false,
			CancellationRequest::REASON_OTHER
		);

		$handler = new CancellationRequestHandler();
		try {
			$handler->handle( $request, '' );
			$order->update_meta_data( '_ce_order_cancelled', true );
			$order->save();
			update_option(
				'_channel_engine_order_save_success',
				__( 'Cancellation request successfully sent to ChannelEngine.', 'channelengine-integration' )
			);
		} catch ( Cancellation_Rejected_Exception $exception ) {
			update_option( '_channel_engine_order_save_note', $exception->getMessage() );
			throw new Exception( 'ChannelEngine status change not allowed' );
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws BusinessLogic\Orders\ChannelSupport\Exceptions\FailedToRetrieveOrdersChannelSupportEntityException
	 * @throws Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
	 * @throws Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
	 * @throws Exception
	 */
	public function handle_order_shipment( WC_Order $order ) {
		if ( $order->get_meta( '_ce_order_shipped' ) ) {
			return;
		}

		$methods = array();
		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			$methods = $zone['shipping_methods'] + $methods;
		}

		$track_trace_no        = $order->get_meta( '_shipping_ce_track_and_trace' );
		$shipping_method       = $order->get_meta( '_shipping_ce_shipping_method' );
		$shipping_method_title = array_key_exists( $shipping_method, $methods )
			? $methods[ $shipping_method ]->get_title()
			: $shipping_method;

		$request = new CreateShipmentRequest(
			$order->get_id(),
			$this->get_shipments_service()->getAllItems( $order->get_id() ),
			false,
			$order->get_id(),
			$order->get_id(),
			$track_trace_no,
			'',
			'',
			$shipping_method_title
		);

		$handler = new ShipmentsCreateRequestHandler();
		try {
			$handler->handle( $request );
			$order->update_meta_data( '_ce_order_shipped', true );
			$order->save();
			update_option(
				'_channel_engine_order_save_success',
				__( 'Shipment request successfully sent to ChannelEngine.', 'channelengine-integration' )
			);
		} catch ( Shipment_Rejected_Exception $exception ) {
			update_option( '_channel_engine_order_save_note', $exception->getMessage() );
			throw new Exception( 'ChannelEngine status change not allowed' );
		}
	}

	/**
	 * @throws Exception
	 */
	public function before_order_cancel_status_transition() {
		if ( ! $this->get_plugin_status_service()->is_enabled() ) {
			return;
		}

		$this->prevent_order_save();
	}

	/**
	 * @throws Exception
	 */
	public function before_order_shipped_status_transition() {
		if ( ! $this->get_plugin_status_service()->is_enabled() ) {
			return;
		}

		$this->prevent_order_save();
	}

	/**
	 * @throws Exception
	 */
	public function prevent_order_save() {
		$orderNote = get_option( '_channel_engine_order_save_note' );

		if ( ! empty( $orderNote ) ) {
			throw new Exception( 'ChannelEngine status change not allowed' );
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	protected function get_cancellation_items( WC_Order $order ) {
		$line_items = array();

		foreach ( $order->get_items() as $item ) {
			$line_items[] = new CancellationItem(
				$item['variation_id'] ? $item['variation_id'] : $item['product_id'],
				$item['qty'],
				true
			);
		}

		return $line_items;
	}

	/**
	 * Switches to site with provided ID and removes plugin from that site.
	 *
	 * @param int $site_id Site identifier.
	 */
	private function switch_to_site_and_uninstall_plugin( $site_id ) {
		switch_to_blog( $site_id );
		$this->uninstall_plugin_from_site();
		delete_option( 'CHANNELENGINE_SCHEMA_VERSION' );
		restore_current_blog();
	}

	/**
	 * Removes plugin tables and configuration from the current site.
	 */
	private function uninstall_plugin_from_site() {
		try {
			/** @var WebhooksService $webhooks_service */
			$webhooks_service = ServiceRegister::getService( WebhooksService::class );
			$webhooks_service->delete();
		} catch ( Exception $e ) {
			Logger::logError( 'Failed to delete webhook because: ' . $e->getMessage() );
		}

		$installer = new Database( new Plugin_Options_Repository() );
		$installer->uninstall();
	}

	private function add_plugin_hooks() {
		if ( $this->get_plugin_status_service()->is_enabled() ) {
			add_action( 'woocommerce_new_product', Product_Hooks::class . '::on_product_create' );
			add_action( 'woocommerce_update_product', Product_Hooks::class . '::on_product_create' );
			add_action( 'woocommerce_new_product_variation', Product_Hooks::class . '::on_variant_create', 10, 2 );
			add_action( 'woocommerce_update_product_variation', Product_Hooks::class . '::on_variant_create', 10, 2 );
			add_action( 'woocommerce_delete_product', Product_Hooks::class . '::on_product_deleted' );
			add_action( 'woocommerce_delete_product_variation', Product_Hooks::class . '::on_product_deleted' );
			add_action( 'woocommerce_trash_product', Product_Hooks::class . '::on_product_deleted' );
			add_action( 'woocommerce_trash_product_variation', Product_Hooks::class . '::on_product_deleted' );
			add_action( 'wp_trash_post', Product_Hooks::class . '::on_product_deleted' );
		}
	}

	/**
	 * Retrieves an instance of Plugin_Status_Service.
	 *
	 * @return Plugin_Status_Service
	 */
	private function get_plugin_status_service() {
		return ServiceRegister::getService( Plugin_Status_Service::class );
	}

	/**
	 * Retrieves an instance of AuthorizationService.
	 *
	 * @return AuthorizationService
	 */
	private function get_auth_service() {
		return ServiceRegister::getService( AuthorizationService::class );
	}

	/**
	 * @return NotificationService
	 */
	private function get_notification_service() {
		return ServiceRegister::getService( NotificationService::class );
	}

	/**
	 * @return OrdersConfigurationService
	 */
	private function get_order_config_service() {
		return ServiceRegister::getService( OrdersConfigurationService::class );
	}

	/**
	 * @return ShipmentsService
	 */
	private function get_shipments_service() {
		return ServiceRegister::getService( ShipmentsService::class );
	}
}
