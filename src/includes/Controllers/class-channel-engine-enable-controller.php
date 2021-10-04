<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\Components\Services\Plugin_Status_Service;
use ChannelEngine\Components\Services\Trigger_Sync_Service;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Utility\Currency_Check;
use ChannelEngine\Utility\Script_Loader;
use Exception;

/**
 * Class Channel_Engine_Enable_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Enable_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * Enables plugin.
	 */
	public function enable() {
		if ( ! Currency_Check::match( get_woocommerce_currency() ) ) {
			$this->return_error( 'Currency mismatch detected. Please make sure that store currency matches ChannelEngine.' );
		}

		$this->get_status_service()->enable();

		try {
			$post = json_decode( $this->get_raw_input(), true );
			Trigger_Sync_Service::trigger( $post );
		} catch ( Exception $e ) {
			$this->disable();
			$this->return_error( 'Failed to start sync.' );
		}

		$this->return_json( [ 'success' => true ] );
	}

	/**
	 * Disables plugin.
	 */
	public function disable() {
		$this->get_status_service()->disable();

		$this->return_json( [ 'success' => true ] );
	}

	/**
	 * @inheritDoc
	 */
	protected function load_resources() {
		parent::load_resources();

		Script_Loader::load_js( [
			'/js/InitialSync.js',
		] );
	}

	/**
	 * Provides plugin status service.
	 *
	 * @return Plugin_Status_Service
	 */
	private function get_status_service() {
		return ServiceRegister::getService( Plugin_Status_Service::class );
	}
}