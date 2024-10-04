<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\Shipments\Contracts\ShipmentsService;
use ChannelEngine\BusinessLogic\Shipments\Domain\CreateShipmentRequest;
use ChannelEngine\BusinessLogic\Shipments\Domain\OrderItem;
use ChannelEngine\BusinessLogic\Shipments\Domain\RejectResponse;
use ChannelEngine\BusinessLogic\Shipments\Domain\UpdateShipmentRequest;
use ChannelEngine\Components\Exceptions\Shipment_Rejected_Exception;
use Exception;
use WC_Order;

/**
 * Class Shipments_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Shipments_Service implements ShipmentsService {

	/**
	 * Retrieves all order items by order id.
	 *
	 * @param string $shopOrderId
	 *
	 * @return OrderItem[]
	 */
	public function getAllItems( $shopOrderId ) {
		$order       = wc_get_order( $shopOrderId );
		$ce_order_id = $order->get_meta( '_channel_engine_order_id' );

		if ( ! $ce_order_id ) {
			return array();
		}

		$items = array();

		foreach ( $order->get_items() as $item ) {
			$orderItem = new OrderItem();
			$orderItem->setShipped( true );
			$orderItem->setQuantity( $item['qty'] );
			$orderItem->setMerchantProductNo( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );

			$items[] = $orderItem;
		}

		return $items;
	}

	/**
	 * Rejects creation request.
	 *
	 * @param CreateShipmentRequest $request
	 * @param Exception             $reason
	 *
	 * @return RejectResponse
	 *
	 * @throws Shipment_Rejected_Exception
	 */
	public function rejectCreate( $request, Exception $reason ) {
		$error = json_decode( $reason->getMessage(), true );
		throw new Shipment_Rejected_Exception(
            esc_html__( 'Shipment create failed. Reason: ', 'channelengine-integration' ) .
            esc_html( $error['message'] )
		);
	}

	/**
	 * Rejects update request.
	 *
	 * @param UpdateShipmentRequest $request
	 * @param Exception             $reason
	 *
	 * @return RejectResponse
	 *
	 * @throws Shipment_Rejected_Exception
	 */
	public function rejectUpdate( $request, Exception $reason ) {
		$error = json_decode( $reason->getMessage(), true );
		throw new Shipment_Rejected_Exception(
            esc_html__( 'Shipment update failed. Reason: ', 'channelengine-integration' ) .
            esc_html( $error['message'] )
		);
	}
}
