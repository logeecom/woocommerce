<?php

namespace ChannelEngine\Components\Handlers;

use ChannelEngine\BusinessLogic\Products\Entities\ProductEvent;
use ChannelEngine\BusinessLogic\Products\Handlers\TickEventHandler as BaseTickEventHandler;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsDeleteTask;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsPurgeTask;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsUpsertTask;
use ChannelEngine\Components\Tasks\Products_Replace_Task;

/**
 * Class TickEventHandler
 *
 * @package ChannelEngine\Components\Handlers
 */
class Tick_Event_Handler extends BaseTickEventHandler {

	/**
	 * Retrieves sync task.
	 *
	 * @param $type
	 * @param $ids
	 *
	 * @return ProductsDeleteTask|ProductsUpsertTask|ProductsPurgeTask|Products_Replace_Task
	 */
	protected function getTask( $type, $ids ) {
		switch ( $type ) {
			case ProductEvent::DELETED:
				return new ProductsDeleteTask( $ids );
			case ProductEvent::PURGED:
				return new ProductsPurgeTask( $ids );
			case ProductEvent::REPLACED:
				return new Products_Replace_Task( $ids );
			default:
				return new ProductsUpsertTask( $ids );
		}
	}
}
