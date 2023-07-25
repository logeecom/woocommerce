<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\Cancellation\Contracts\CancellationService;
use ChannelEngine\BusinessLogic\Cancellation\Domain\CancellationItem;
use ChannelEngine\BusinessLogic\Cancellation\Domain\CancellationRequest;
use ChannelEngine\BusinessLogic\Cancellation\Domain\RejectResponse;
use ChannelEngine\Components\Exceptions\Cancellation_Rejected_Exception;
use Exception;
use WC_Order;

/**
 * Class Cancellation_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Cancellation_Service implements CancellationService {

	/**
	 * Provides list of cancelled items for order.
	 *
	 * @param $orderId
	 *
	 * @return CancellationItem[]
	 */
	public function getAllItems( $orderId ) {
        $order = wc_get_order( $orderId );
        $ce_order_id = $order->get_meta( '_channel_engine_order_id' );

		if ( ! $ce_order_id ) {
			return [];
		}

		$items = [];

		foreach ( $order->get_items() as $item ) {
			$items[] = new CancellationItem( $item['product_id'], $item['qty'], $order->get_status() );
		}

		return $items;
	}

	/**
	 * Rejects cancellation request.
	 *
	 * @param CancellationRequest $request
	 * @param Exception $reason
	 *
	 * @return RejectResponse
	 *
	 * @throws Cancellation_Rejected_Exception
	 */
	public function reject( CancellationRequest $request, Exception $reason ) {
		$error = json_decode( $reason->getMessage(), true );
		throw new Cancellation_Rejected_Exception(
			__( 'Order cancellation failed. Reason: ', 'channelengine-wc' ) .
			$error['message']
		);
	}
}
