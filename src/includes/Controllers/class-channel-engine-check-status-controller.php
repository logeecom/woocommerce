<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Orders\Contracts\OrdersService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\Components\Services\Export_Products_Service;
use ChannelEngine\Components\Services\Orders_Service;
use ChannelEngine\Components\Services\Products_Service;
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
	 * @var OrdersConfigurationService
	 */
	protected $orders_config_service;

	/**
	 * Gets synchronization data.
	 */
	public function get_sync_data() {
		$data = array(
			'product_sync'    => $this->get_task_data( 'ProductSync' ),
			'order_sync'      => $this->get_task_data( 'OrderSync' ),
			'export_products' => $this->get_export_products_service()->isExportProductsEnabled(),
		);

		$this->return_json( $data );
	}

	/**
	 * Retrieves task data.
	 *
	 * @param string $task_type
	 *
	 * @return array
	 *
	 * @throws QueueItemDeserializationException
	 */
	protected function get_task_data( $task_type ) {
		$queueItem = $this->get_queue_service()->findLatestByType( $task_type );

		if ( ! $queueItem ) {
			return array(
				'status' => 'not created',
			);
		}

		$log = $this->get_transaction_log_service()->find(
			array(
				'executionId' => $queueItem->getTask()->getExecutionId(),
			)
		)[0];

		$status = ( $log && $log->getSynchronizedEntities() ) ? $log->getSynchronizedEntities() : 0;
		$count  = 0;

		if ( 'ProductSync' === $task_type ) {
			$count = $this->get_products_service()->count();
		}

		if ( 'OrderSync' === $task_type ) {
			$count = $log->getTotalCount();
		}

		if ( $count ) {
			$status *= 100 / $count;
		} else {
			$status = null !== $count ? 100 : 0;
		}

		return array(
			'status'   => $queueItem->getStatus(),
			'progress' => (int) $status,
			'synced'   => ( $log && $log->getSynchronizedEntities() ) ? $log->getSynchronizedEntities() : 0,
			'total'    => null !== $count ? $count : '?',
		);
	}

	/**
	 * Retrieves information on whether order synchronization is enabled.
	 *
	 * @return void
	 */
	protected function get_order_sync_config() {
		$isEnabled = $this->get_orders_config_service()->getOrderSyncConfig()->isEnableOrdersByMarketplaceSync() ||
					 $this->get_orders_config_service()->getOrderSyncConfig()->isEnableOrdersByMerchantSync();

		$this->return_json( array( 'enabled' => $isEnabled ) );
	}

	/**
	 * Retrieves an instance of QueueService.
	 *
	 * @return QueueService
	 */
	protected function get_queue_service() {
		if ( null === $this->queue_service ) {
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
		if ( null === $this->products_service ) {
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
		if ( null === $this->orders_service) {
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
		if ( null === $this->transaction_log_service ) {
			$this->transaction_log_service = ServiceRegister::getService( TransactionLogService::class );
		}

		return $this->transaction_log_service;
	}

	/**
	 * Retrieves an instance of OrdersConfigurationService.
	 *
	 * @return Orders_Service
	 */
	protected function get_orders_config_service() {
		if ( null === $this->orders_config_service ) {
			$this->orders_config_service = ServiceRegister::getService( OrdersConfigurationService::class );
		}

		return $this->orders_config_service;
	}

	/**
	 * Retrieves an instance of Export_Products_Service.
	 *
	 * @return Export_Products_Service
	 */
	protected function get_export_products_service(): Export_Products_Service {
		return ServiceRegister::getService( Export_Products_Service::class );
	}
}
