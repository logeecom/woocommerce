<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\DTO\ExtraDataAttributeMappings;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Extra_Data_Attribute_Mappings_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Extra_Data_Attribute_Mappings_Service
{
    /**
     * Sets extra data attribute mappings configuration.
     *
     * @param ExtraDataAttributeMappings $mappings
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setExtraDataAttributeMappings($mappings)
    {
        ConfigurationManager::getInstance()->saveConfigValue('extraDataAttributeMappings', $mappings->toArray());
    }

	/**
	 * Retrieves extra data attribute mappings configuration.
	 *
	 * @return ExtraDataAttributeMappings|null
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function getExtraDataAttributeMappings()
	{
		$rawData = ConfigurationManager::getInstance()->getConfigValue('extraDataAttributeMappings');

		return $rawData !== null ? new ExtraDataAttributeMappings($rawData) : $rawData;
	}
}
