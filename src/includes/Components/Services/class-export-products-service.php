<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Export_Products_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Export_Products_Service {

	/**
	 * Checks if export products is enabled.
	 *
	 * @return bool
	 */
	public function isExportProductsEnabled(): bool {
		try {
			return ConfigurationManager::getInstance()->getConfigValue( 'syncProducts', 1 ) === 1 ?? false;
		} catch ( QueryFilterInvalidParamException $exception ) {
			Logger::logError( $exception->getMessage() );

			return false;
		}
	}

	/**
	 * Enables products export.
	 *
	 * @return void
	 * @throws QueryFilterInvalidParamException
	 */
	public function enableProductsExport(): void {
		ConfigurationManager::getInstance()->saveConfigValue( 'syncProducts', 1 );
	}

	/**
	 * Disables products export.
	 *
	 * @return void
	 * @throws QueryFilterInvalidParamException
	 */
	public function disableProductsExport(): void {
		ConfigurationManager::getInstance()->saveConfigValue( 'syncProducts', 0 );
	}
}
