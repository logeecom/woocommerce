<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Notifications\Contracts\NotificationService;
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
		$scripts         = [ '/js/Dashboard.js' ];
		$dashboard_state = $this->get_state();

		if ( $dashboard_state === 'disabled-integration' ) {
			$scripts[] = '/js/Enable.js';
		}

		if ( in_array( $dashboard_state, [
			'sync-in-progress',
			'order-sync-in-progress',
			'product-sync-in-progress'
		] ) ) {
			$scripts[] = '/js/CheckStatus.js';
		}

		Script_Loader::load_js( $scripts );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_view_data() {
		try {
			return [ 'status' => $this->get_state() ];
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError( $e->getMessage() );

			return [ 'status' => 'notifications' ];
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
			return 'sync-in-progress';
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
		if ( $this->notification_service === null ) {
			$this->notification_service = ServiceRegister::getService( NotificationService::class );
		}

		return $this->notification_service;
	}
}