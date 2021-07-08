<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\Products\Entities\SyncConfig;
use ChannelEngine\Components\Services\State_Service;
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
	 * Saves default stock quantity.
	 */
	public function save() {
		$quantityJson = json_decode( $this->get_raw_input(), true );
		$quantity     = $quantityJson['quantity'];

		if ( ! filter_var( $quantity, FILTER_VALIDATE_INT ) || (int) $quantity < 0 ) {
			$this->return_json( [
				'success' => false,
				'message' => __( 'Default stock quantity is required field.', 'channelengine' ),
			] );
		}

		$config = new SyncConfig();
		$config->setDefaultStock( $quantity );

		$this->get_product_config_service()->set( $config );
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
	 * @return State_Service
	 */
	protected function get_state_service() {
		return ServiceRegister::getService( State_Service::class );
	}
}
