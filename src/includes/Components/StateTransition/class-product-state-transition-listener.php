<?php

namespace ChannelEngine\Components\StateTransition;

use ChannelEngine\BusinessLogic\InitialSync\ProductSync;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueStatusChangedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;

/**
 * Class Product_State_Transition_Listener
 *
 * @package ChannelEngine\Components\StateTransition
 */
class Product_State_Transition_Listener {
	/**
	 * @param QueueStatusChangedEvent $event
	 *
	 * @throws QueueItemDeserializationException
	 * @throws QueryFilterInvalidParamException
	 */
	public static function handle( QueueStatusChangedEvent $event ) {
		$queue_item    = $event->getQueueItem();
		$task          = $queue_item->getTask();
		$state_service = new State_Service();

		if ( ! $task || ! ( $task instanceof ProductSync ) ) {
			return;
		}

		if ( $queue_item->getStatus() === QueueItem::IN_PROGRESS
		     && ! $state_service->is_manual_product_sync_in_progress() ) {
			$state_service->set_initial_sync_in_progress( true );
			$state_service->set_product_sync_in_progress( true );
		}

		if ( $queue_item->getStatus() === QueueItem::COMPLETED ) {
			$state_service->set_manual_product_sync_in_progress( false );
			$state_service->set_product_sync_in_progress( false );

			if ( ! $state_service->is_order_sync_in_progress() ) {
				$state_service->set_initial_sync_in_progress( false );
				$state_service->set_onboarding_completed( true );
			}
		}
	}
}