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
     * @param string $postId
	 */
	public function render( string $postId ) {
		Script_Loader::load_css( [ '/css/meta-post-box.css' ] );
		Script_Loader::load_js( [
			'/channelengine/js/AjaxService.js',
			'/js/TrackAndTrace.js',
		] );

        $order      = get_post( $postId );
        $wcGetOrder = wc_get_order( $postId );

		echo View::file( '/meta_post_box.php' )->render( [
            'order_id'               => $wcGetOrder->get_meta( '_channel_engine_order_id' ),
            'channel_name'           => $wcGetOrder->get_meta( '_channel_engine_channel_name' ),
            'channel_order_no'       => $wcGetOrder->get_meta( '_channel_engine_channel_order_no' ),
            'payment_method'         => $wcGetOrder->get_meta( '_channel_engine_payment_method' ),
            'track_and_trace'        => $wcGetOrder->get_meta( '_shipping_ce_track_and_trace' ),
            'chosen_shipping_method' => $wcGetOrder->get_meta( '_shipping_ce_shipping_method' ),
			'shipping_methods'       => WC()->shipping() ? WC()->shipping()->load_shipping_methods() : [],
			'post_id'                => $postId,
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

        $wcGetOrder = wc_get_order( $id );

		$track_and_trace = ! empty( $raw_data['trackAndTrace'] ) ?
			$raw_data['trackAndTrace'] : $wcGetOrder->get_meta( '_shipping_ce_track_and_trace' );
		$shipping_method = ! empty( $raw_data['shippingMethod'] ) ?
			$raw_data['shippingMethod'] : $wcGetOrder->get_meta( '_shipping_ce_shipping_method' );
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
