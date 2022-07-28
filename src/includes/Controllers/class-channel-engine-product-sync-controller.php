<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\Products\Entities\SyncConfig;
use ChannelEngine\Components\Services\Attribute_Mappings_Service;
use ChannelEngine\Components\Services\Extra_Data_Attribute_Mappings_Service;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\DTO\AttributeMappings;
use ChannelEngine\DTO\ExtraDataAttributeMappings;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Utility\Script_Loader;

/**
 * Class Channel_Engine_Product_Sync_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Product_Sync_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var ProductsSyncConfigService
	 */
	protected $product_config_service;
	/**
	 * @var Attribute_Mappings_Service
	 */
	protected $attribute_mappings_service;
	/**
	 * @var Extra_Data_Attribute_Mappings_Service
	 */
	protected $extra_data_attribute_mappings_service;

	/**
	 * Saves product synchronization configuration.
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function save() {
		$quantityJson     = json_decode( $this->get_raw_input(), true );
		$quantity         = $quantityJson['quantity'];
		$enabledStockSync = $quantityJson['enabledStockSync'];
		$mappings         = $quantityJson['attributeMappings'];
		$extraDataMapping = $quantityJson['extraDataMappings'];

		if ( $enabledStockSync && ( !is_numeric( $quantity) || (int) $quantity < 0 )) {
			$this->return_json( [
				'success' => false,
				'message' => __( 'Default stock quantity is required field.', 'channelengine' ),
			] );
		}

		$config = new SyncConfig();
		$config->setDefaultStock( $quantity );
		$config->setEnabledStockSync( $enabledStockSync );

		$this->get_product_config_service()->set( $config );

		$mappings_dto = new AttributeMappings(
			$mappings['brand']  !== '' ? $mappings['brand'] : null,
			$mappings['color']  !== '' ? $mappings['color'] : null,
			$mappings['size']  !== '' ? $mappings['size'] : null,
			$mappings['gtin']  !== '' ? $mappings['gtin'] : null,
			$mappings['cataloguePrice']  !== '' ? $mappings['cataloguePrice'] : null,
			$mappings['price']  !== '' ? $mappings['price'] : null,
			$mappings['purchasePrice']  !== '' ? $mappings['purchasePrice'] : null,
			$mappings['details']  !== '' ? $mappings['details'] : null,
			$mappings['category']  !== '' ? $mappings['category'] : null,
			$mappings['vendorProductNumber']  !== '' ? $mappings['vendorProductNumber'] : null
		);

		$extra_data_dto = new ExtraDataAttributeMappings($extraDataMapping);

		$this->get_attribute_mappings_service()->setAttributeMappings($mappings_dto);
		$this->get_extra_data_attribute_mappings_service()->setExtraDataAttributeMappings($extra_data_dto);
		$this->get_state_service()->set_product_configured( true );
		$this->return_json( [ 'success' => true ] );
	}

	/**
	 * @inheritDoc
	 */
	protected function load_resources() {
		parent::load_resources();

		Script_Loader::load_js( [
			'/js/ProductSettings.js',
		] );
	}

	/**
	 * Retrieves an instance of ProductSyncConfigService.
	 *
	 * @return ProductsSyncConfigService
	 */
	protected function get_product_config_service() {
		if ( $this->product_config_service === null ) {
			$this->product_config_service = ServiceRegister::getService( ProductsSyncConfigService::class );
		}

		return $this->product_config_service;
	}

	/**
	 * Retrieves an instance of Attribute_Mappings_Service.
	 *
	 * @return Attribute_Mappings_Service
	 */
	protected function get_attribute_mappings_service() {
		if ( $this->attribute_mappings_service === null ) {
			$this->attribute_mappings_service = ServiceRegister::getService( Attribute_Mappings_Service::class );
		}

		return $this->attribute_mappings_service;
	}

	/**
	 * Retrieves an instance of Extra_Data_Attribute_Mappings_Service.
	 *
	 * @return Extra_Data_Attribute_Mappings_Service
	 */
	protected function get_extra_data_attribute_mappings_service() {
		if ( $this->extra_data_attribute_mappings_service === null ) {
			$this->extra_data_attribute_mappings_service = ServiceRegister::getService( Extra_Data_Attribute_Mappings_Service::class );
		}

		return $this->extra_data_attribute_mappings_service;
	}

	/**
	 * @return State_Service
	 */
	protected function get_state_service() {
		return ServiceRegister::getService( State_Service::class );
	}
}
