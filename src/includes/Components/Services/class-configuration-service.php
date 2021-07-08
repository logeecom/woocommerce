<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\Configuration\ConfigService;
use ChannelEngine\Utility\Shop_Helper;

/**
 * Class Configuration_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Configuration_Service extends ConfigService {

	/**
	 * Retrieves integration name.
	 *
	 * @return string
	 */
	public function getIntegrationName() {
		return 'WooCommerce';
	}

	/**
	 * Returns async process starter url, always in http.
	 *
	 * @param string $guid Process identifier.
	 *
	 * @return string Formatted URL of async process starter endpoint.
	 */
	public function getAsyncProcessUrl( $guid ) {
		$params = array( 'guid' => $guid );
		if ( $this->isAutoTestMode() ) {
			$params['auto-test'] = 1;
		}

		return Shop_Helper::get_controller_url( 'Async_Process', 'run', $params );
	}
}
