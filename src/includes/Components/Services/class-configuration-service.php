<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\Configuration\ConfigService;
use ChannelEngine\BusinessLogic\Configuration\DTO\SystemInfo;
use ChannelEngine\ChannelEngine;
use ChannelEngine\Infrastructure\Utility\ServerUtil;
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

    /**
     * @inheritDoc
     */
    public function getSystemInfo()
    {
        return new SystemInfo(
            'woocommerce',
            $this->getWooCommerceVersion(),
            ServerUtil::get('HTTP_HOST', 'N/A'),
            ChannelEngine::VERSION
        );
    }

    /**
     * Gets WooCommerce version if available.
     *
     * @return string
     */
    private function getWooCommerceVersion()
    {
        return defined('WC_VERSION') ? WC_VERSION : 'N/A';
    }
}
