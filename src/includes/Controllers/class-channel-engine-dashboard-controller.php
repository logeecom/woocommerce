<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Notifications\Contracts\NotificationService;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Utility\Script_Loader;

/**
 * Class Channel_Engine_Dashboard_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Dashboard_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var NotificationService
	 */
	protected $notification_service;

	/**
	 * @inheritDoc
	 */
	protected function load_resources() {
		parent::load_resources();
		$scripts         = array(
			'/js/Dashboard.js',
			'/js/TriggerSyncService.js',
			'/js/DashboardNotifications.js',
			'/js/ModalService.js',
			'/js/Details.js',
			'/js/DisconnectService.js',
			'/js/Disconnect.js',
			'/js/TriggerSyncModal.js',
			'/js/Details.js',
		);

		$dashboard_state = $this->get_state();

		if ( 'disabled-integration' === $dashboard_state ) {
			$scripts[] = '/js/Enable.js';
		}

		if ( in_array(
			$dashboard_state,
			array(
				'sync-in-progress',
				'order-sync-in-progress',
				'product-sync-in-progress',
			)
		) ) {
			$scripts[] = '/js/CheckStatus.js';
		}

		Script_Loader::load_js( $scripts );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_view_data() {
		try {
			return array( 'status' => $this->get_state() );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError( $e->getMessage() );

			return array( 'status' => 'notifications' );
		}
	}

	/**
	 * Retrieves dashboard state.
	 *
	 * @return string
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	protected function get_state() {
		if ( ! $this->get_plugin_status_service()->is_enabled() ) {
			return 'disabled-integration';
		}

		if ( $this->get_state_service()->is_initial_sync_in_progress() ) {
			$config = ServiceRegister::getService( OrdersConfigurationService::class )->getOrderSyncConfig();
			if ( $config->isEnableOrdersByMerchantSync() || $config->isEnableOrdersByMarketplaceSync() ) {
				return 'sync-in-progress';
			}
			return 'product-sync-in-progress';

		}

		$manualProductSync = $this->get_state_service()->is_manual_product_sync_in_progress();
		$manualOrderSync   = $this->get_state_service()->is_manual_order_sync_in_progress();

		if ( $manualOrderSync && $manualProductSync ) {
			return 'sync-in-progress';
		}

		if ( $manualOrderSync ) {
			return 'order-sync-in-progress';
		}

		if ( $manualProductSync ) {
			return 'product-sync-in-progress';
		}

		if ( $this->get_notification_service()->countNotRead() > 0 ) {
			return 'notifications';
		}

		return 'sync-completed';
	}

	/**
	 * @return NotificationService
	 */
	protected function get_notification_service() {
		if ( null === $this->notification_service ) {
			$this->notification_service = ServiceRegister::getService( NotificationService::class );
		}

		return $this->notification_service;
	}
}
