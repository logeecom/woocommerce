<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\InitialSync\OrderSync;
use ChannelEngine\BusinessLogic\InitialSync\ProductSync;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;

class Trigger_Sync_Service {
	/**
	 * Triggers sync.
	 *
	 * @param array $sync_details
	 *
	 * @throws QueueStorageUnavailableException
	 */
	public static function trigger( array $sync_details ) {
		$order_sync   = rest_sanitize_boolean( $sync_details['order_sync'] );
		$product_sync = rest_sanitize_boolean( $sync_details['product_sync'] );

		if ( $product_sync ) {
			static::get_queue_service()->enqueue( 'channel-engine-products', new ProductSync() );
			static::get_state_service()->set_manual_product_sync_in_progress( true );
		}
		if ( $order_sync ) {
			static::get_queue_service()->enqueue( 'channel-engine-orders', new OrderSync() );
			static::get_state_service()->set_manual_order_sync_in_progress( true );
		}
	}

	protected static function get_queue_service() {
		return ServiceRegister::getService( QueueService::class );
	}

	protected static function get_state_service() {
		return ServiceRegister::getService( State_Service::class );
	}
}
