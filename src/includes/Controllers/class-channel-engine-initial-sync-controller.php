<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\InitialSync\OrderSync;
use ChannelEngine\BusinessLogic\InitialSync\ProductSync;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use ChannelEngine\Utility\Script_Loader;

/**
 * Class Channel_Engine_Initial_Sync_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Initial_Sync_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var QueueService
	 */
	protected $queue_service;

	/**
	 * Starts initial synchronization.
	 */
	public function start() {
		try {
			$this->get_queue_service()->enqueue( 'channel-engine-products', new ProductSync() );
			$config = ServiceRegister::getService( OrdersConfigurationService::class )->getOrderSyncConfig();
			if ( $config->isEnableOrdersByMerchantSync() || $config->isEnableOrdersByMarketplaceSync() ) {
				$this->get_queue_service()->enqueue( 'channel-engine-orders', new OrderSync() );
			}
			$this->get_state_service()->set_initial_sync_in_progress( true );

			$this->return_json( array( 'success' => true ) );
		} catch ( QueueStorageUnavailableException $e ) {
			$this->return_json(
				array(
					'success' => false,
					/* translators: %s: search term */
					'message' => sprintf( __( 'Failed to start initial sync because %s', 'channelengine-integration' ), $e->getMessage() ),
				)
			);
		}
	}

	protected function load_resources() {
		parent::load_resources();

		Script_Loader::load_js(
			array(
				'/js/InitialSync.js',
				'/js/DisconnectService.js',
				'/js/Disconnect.js',
			)
		);
	}

	/**
	 * Retrieves instance of QueueService.
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
	 * @return State_Service
	 */
	protected function get_state_service() {
		return ServiceRegister::getService( State_Service::class );
	}
}
