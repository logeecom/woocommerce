<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Orders\ChannelSupport\Exceptions\FailedToRetrieveOrdersChannelSupportEntityException;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Shipments\Domain\UpdateShipmentRequest;
use ChannelEngine\BusinessLogic\Shipments\Handlers\ShipmentsUpdateRequestHandler;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Utility\Script_Loader;
use ChannelEngine\Utility\View;
use WC_Order;
use WC_Shipping_Zones;

/**
 * Class Channel_Engine_Order_Overview_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Order_Overview_Controller extends Channel_Engine_Base_Controller {
	/**
	 * @var OrdersConfigurationService
	 */
	protected $order_config_service;

	/**
	 * Renders ChannelEngine order overview box content.
	 *
	 * @param string $postId
	 */
	public function render( string $postId ) {
		Script_Loader::load_css( array( '/css/meta-post-box.css' ) );
		Script_Loader::load_js(
			array(
				'/channelengine/js/AjaxService.js',
				'/js/TrackAndTrace.js',
			)
		);

		$order = wc_get_order( $postId );

		echo wp_kses(
			View::file( '/meta_post_box.php' )->render(
				array(
					'order_id'               => $order->get_meta( '_channel_engine_order_id' ),
					'channel_name'           => $order->get_meta( '_channel_engine_channel_name' ),
					'channel_order_no'       => $order->get_meta( '_channel_engine_channel_order_no' ),
					'vat_number'             => $order->get_meta( '_channel_engine_vat_number' ),
					'payment_method'         => $order->get_meta( '_channel_engine_payment_method' ),
					'track_and_trace'        => $order->get_meta( '_shipping_ce_track_and_trace' ),
					'chosen_shipping_method' => (int) $order->get_meta( '_shipping_ce_shipping_method' ),
					'type_of_fulfillment'    => $order->get_meta( '_channel_engine_type_of_fulfillment' ),
					'shipping_methods'       => $this->get_shipping_methods(),
					'post_id'                => $postId,
					'order_cancelled'        => $order->get_status() === 'cancelled',
				)
			),
			View::get_allowed_tags()
		);
	}

	/**
	 * Saves ChannelEngine track and trace and shipping method data.
	 */
	public function save() {
		$rawJson = $this->get_raw_input();
		$raw     = json_decode( $rawJson, true );
		$raw     = array_map( 'sanitize_text_field', $raw );
		if ( empty( $raw['postId'] ) ) {
			$this->redirect404();
		}

		$order = wc_get_order( $raw['postId'] );
		try {
			$this->handle_order_update( $order, $raw );
		} catch ( BaseException $e ) {
			$this->return_json(
				array(
					'success' => false,
					'message' => $e->getMessage(),
				)
			);
		}

		if ( ! empty( $raw['trackAndTrace'] ) ) {
			$order->update_meta_data( '_shipping_ce_track_and_trace', $raw['trackAndTrace'] );
			$order->save();
		}

		if ( ! empty( $raw['shippingMethod'] ) ) {
			$order->update_meta_data( '_shipping_ce_shipping_method', $raw['shippingMethod'] );
			$order->save();
		}

		$this->return_json( array( 'success' => true ) );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws FailedToRetrieveOrdersChannelSupportEntityException
	 * @throws QueryFilterInvalidParamException
	 * @throws RepositoryNotRegisteredException
	 */
	protected function handle_order_update( WC_Order $order, $raw_data ) {
		$track_and_trace = ! empty( $raw_data['trackAndTrace'] ) ?
			$raw_data['trackAndTrace'] : $order->get_meta( '_shipping_ce_track_and_trace' );
		$shipping_method = ! empty( $raw_data['shippingMethod'] ) ?
			$raw_data['shippingMethod'] : $order->get_meta( '_shipping_ce_shipping_method' );
		$order_mappings  = $this->get_order_config_service()->getOrderSyncConfig();

		if ( ! $track_and_trace || ! $shipping_method
			 || ! $order_mappings || ! stripos( $order_mappings->getShippedOrders(), $order->get_status() )
			 || ! $order_mappings->isEnableShipmentInfoSync()
		) {
			return;
		}

		$shipping_methods      = $this->get_shipping_methods();
		$shipping_method_title = array_key_exists( $shipping_method, $shipping_methods )
			? $shipping_methods[ $shipping_method ]->get_title()
			: $shipping_method;

		$request = new UpdateShipmentRequest(
			$order->get_id(),
			false,
			$shipping_method_title,
			$track_and_trace,
			'',
			''
		);

		$handler = new ShipmentsUpdateRequestHandler();
		$handler->handle( $request );
	}

	/**
	 * @return OrdersConfigurationService
	 */
	protected function get_order_config_service() {
		if ( null === $this->order_config_service ) {
			$this->order_config_service = ServiceRegister::getService( OrdersConfigurationService::class );
		}

		return $this->order_config_service;
	}

	/**
	 * Get all shipping methods
	 *
	 * @return array
	 */
	private function get_shipping_methods() {
		$methods = array();
		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			$methods = $zone['shipping_methods'] + $methods;
		}

		return $methods;
	}
}
