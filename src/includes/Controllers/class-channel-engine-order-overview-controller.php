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
use WP_Post;

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
	 * @param WP_Post $wp_post
	 */
	public function render( WP_Post $wp_post ) {
		Script_Loader::load_css( [ '/css/meta-post-box.css' ] );
		Script_Loader::load_js( [
			'/channelengine/js/AjaxService.js',
			'/js/TrackAndTrace.js',
		] );

		$order = get_post( $wp_post->ID );

		echo View::file( '/meta_post_box.php' )->render( [
			'order_id'               => get_post_meta( $wp_post->ID, '_channel_engine_order_id', true ),
			'channel_name'           => get_post_meta( $wp_post->ID, '_channel_engine_channel_name', true ),
			'channel_order_no'       => get_post_meta( $wp_post->ID, '_channel_engine_channel_order_no', true ),
			'payment_method'         => get_post_meta( $wp_post->ID, '_channel_engine_payment_method', true ),
			'track_and_trace'        => get_post_meta( $wp_post->ID, '_shipping_ce_track_and_trace', true ),
			'chosen_shipping_method' => get_post_meta( $wp_post->ID, '_shipping_ce_shipping_method', true ),
			'shipping_methods'       => WC()->shipping() ? WC()->shipping()->load_shipping_methods() : [],
			'post_id'                => $wp_post->ID,
			'order_cancelled'        => $order->post_status === 'wc-cancelled',
		] );
	}

	/**
	 * Saves ChannelEngine track and trace and shipping method data.
	 */
	public function save() {
		$rawJson = $this->get_raw_input();
		$raw     = json_decode( $rawJson, true );

		if ( empty( $raw['postId'] ) ) {
			$this->redirect404();
		}

		try {
			$this->handle_order_update( $raw );
		} catch ( BaseException $e ) {
			$this->return_json(
				[
					'success' => false,
					'message' => $e->getMessage(),
				]
			);
		}

		if ( ! empty( $raw['trackAndTrace'] ) ) {
			update_post_meta( $raw['postId'], '_shipping_ce_track_and_trace', $raw['trackAndTrace'] );
		}

		if ( ! empty( $raw['shippingMethod'] ) ) {
			update_post_meta( $raw['postId'], '_shipping_ce_shipping_method', $raw['shippingMethod'] );
		}

		$this->return_json( [ 'success' => true ] );
	}

	/**
	 * @param $raw_data
	 *
	 * @throws FailedToRetrieveOrdersChannelSupportEntityException
	 * @throws QueryFilterInvalidParamException
	 * @throws RepositoryNotRegisteredException
	 */
	protected function handle_order_update( $raw_data ) {
		$id = $raw_data['postId'];

		$track_and_trace = ! empty( $raw_data['trackAndTrace'] ) ?
			$raw_data['trackAndTrace'] : get_post_meta( $id, '_shipping_ce_track_and_trace', true );
		$shipping_method = ! empty( $raw_data['shippingMethod'] ) ?
			$raw_data['shippingMethod'] : get_post_meta( $id, '_shipping_ce_shipping_method', true );
		$order           = get_post( $id );
		$order_mappings  = $this->get_order_config_service()->getOrderSyncConfig();

		if ( ! $track_and_trace || ! $shipping_method
		     || ! $order_mappings || ! ( $order instanceof WP_Post )
		     || $order->post_status !== $order_mappings->getShippedOrders()
		     || ! $order_mappings->isEnableShipmentInfoSync()
		) {
			return;
		}

		$request = new UpdateShipmentRequest(
			$id,
			false,
			$shipping_method,
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
		if ( $this->order_config_service === null ) {
			$this->order_config_service = ServiceRegister::getService( OrdersConfigurationService::class );
		}

		return $this->order_config_service;
	}
}
