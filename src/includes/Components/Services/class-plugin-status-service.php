<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;

/**
 * Class Plugin_Status_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Plugin_Status_Service {
	/**
	 * Enables integration.
	 */
	public function enable() {
		$this->set_status( true );
	}

	/**
	 * Disables integration.
	 */
	public function disable() {
		$this->set_status( false );
	}

	/**
	 * Checks if integration is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return $this->get_status() === true;
	}

	private function set_status( $status ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'pluginStatus', $status );
	}

	private function get_status() {
		return ConfigurationManager::getInstance()->getConfigValue( 'pluginStatus', true );
	}
}