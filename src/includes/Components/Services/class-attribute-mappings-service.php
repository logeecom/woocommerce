<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\DTO\AttributeMappings;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Attribute_Mappings_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Attribute_Mappings_Service {

	/**
	 * Sets attribute mappings configuration.
	 *
	 * @param AttributeMappings $mappings
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function setAttributeMappings( $mappings ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'attributeMappings', $mappings->toArray() );
	}

	/**
	 * Retrieves attribute mappings configuration.
	 *
	 * @return AttributeMappings|null
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function getAttributeMappings() {
		$rawData = ConfigurationManager::getInstance()->getConfigValue( 'attributeMappings' );

		return null !== $rawData ? AttributeMappings::fromArray( $rawData ) : $rawData;
	}
}
