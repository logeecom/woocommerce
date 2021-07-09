<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\Orders\Contracts\OrdersService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\Components\Services\Orders_Service;
use ChannelEngine\Components\Services\Products_Service;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;

/**
 * Class Channel_Engine_Check_Status_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Check_Status_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var QueueService
	 */
	protected $queue_service;
	/**
	 * @var Products_Service
	 */
	protected $products_service;
	/**
	 * @var Orders_Service
	 */
	protected $orders_service;
	/**
	 * @var TransactionLogService
	 */
	protected $transaction_log_service;

	/**
	 * Gets synchronization data.
	 */
	public function get_sync_data() {
		$data = [
			'product_sync' => $this->get_task_data( 'ProductSync' ),
			'order_sync'   => $this->get_task_data( 'OrderSync' ),
		];

		$this->return_json( $data );
	}

	/**
	 * Retrieves task data.
	 *
	 * @param string $task_type
	 *
	 * @return array
	 *
	 * @throws HttpCommunicationException
	 * @throws HttpRequestException
	 * @throws QueryFilterInvalidParamException
	 * @throws QueueItemDeserializationException
	 * @throws RepositoryNotRegisteredException
	 * @throws RequestNotSuccessfulException
	 */
	protected function get_task_data( $task_type ) {
		$queueItem = $this->get_queue_service()->findLatestByType( $task_type );

		if ( ! $queueItem ) {
			return [
				'status' => 'not created',
			];
		}

		$log = $this->get_transaction_log_service()->find( [
			'executionId' => $queueItem->getTask()->getExecutionId()
		] )[0];

		$status = ( $log && $log->getSynchronizedEntities() ) ? $log->getSynchronizedEntities() : 0;
		$count  = 0;

		if ( $task_type === 'ProductSync' ) {
			$count = $this->get_products_service()->count();
		}

		if ( $task_type === 'OrderSync' ) {
			$count = $this->get_orders_service()->getOrdersCount();
		}

		if ( $count ) {
			$status *= 100 / $count;
		} else {
			$status = 100;
		}

		return [
			'status'   => $queueItem->getStatus(),
			'progress' => (int) $status,
			'synced'   => ( $log && $log->getSynchronizedEntities() ) ? $log->getSynchronizedEntities() : 0,
			'total'    => $count,
		];
	}

	/**
	 * Retrieves an instance of QueueService.
	 *
	 * @return QueueService
	 */
	protected function get_queue_service() {
		if ( $this->queue_service === null ) {
			$this->queue_service = ServiceRegister::getService( QueueService::class );
		}

		return $this->queue_service;
	}

	/**
	 * Retrieves an instance of Products_Service
	 *
	 * @return Products_Service
	 */
	protected function get_products_service() {
		if ( $this->products_service === null ) {
			$this->products_service = ServiceRegister::getService( ProductsService::class );
		}

		return $this->products_service;
	}

	/**
	 * Retrieves an instance of Orders_Service.
	 *
	 * @return Orders_Service
	 */
	protected function get_orders_service() {
		if ( $this->orders_service === null ) {
			$this->orders_service = ServiceRegister::getService( OrdersService::class );
		}

		return $this->orders_service;
	}

	/**
	 * Retrieves an instance of TransactionLogService.
	 *
	 * @return TransactionLogService
	 */
	protected function get_transaction_log_service() {
		if ( $this->transaction_log_service === null ) {
			$this->transaction_log_service = ServiceRegister::getService( TransactionLogService::class );
		}

		return $this->transaction_log_service;
	}
}
