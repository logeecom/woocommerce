<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\Components\Services\Plugin_Status_Service;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Utility\Script_Loader;

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
		$this->get_status_service()->enable();

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