<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Authorization\Contracts\AuthorizationService;
use ChannelEngine\BusinessLogic\Authorization\DTO\AuthInfo;
use ChannelEngine\BusinessLogic\Authorization\Exceptions\CurrencyMismatchException;
use ChannelEngine\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrderSyncConfig;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\Products\Entities\SyncConfig;
use ChannelEngine\BusinessLogic\Webhooks\Contracts\WebhooksService;
use ChannelEngine\Components\Exceptions\Order_Statuses_Invalid;
use ChannelEngine\Components\Exceptions\Order_Sync_Config_Invalid;
use ChannelEngine\Components\Exceptions\Stock_Quantity_Invalid;
use ChannelEngine\Components\Exceptions\Stock_Sync_Flag_Invalid;
use ChannelEngine\Components\Services\Attribute_Mappings_Service;
use ChannelEngine\Components\Services\Extra_Data_Attribute_Mappings_Service;
use ChannelEngine\Components\Services\Order_Config_Service;
use ChannelEngine\Components\Services\Products_Service;
use ChannelEngine\Components\Services\Trigger_Sync_Service;
use ChannelEngine\DTO\AttributeMappings;
use ChannelEngine\DTO\ExtraDataAttributeMappings;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use ChannelEngine\Repositories\Plugin_Options_Repository;
use ChannelEngine\Utility\Database;
use ChannelEngine\Utility\Standard_Product_Attributes;
use Exception;
use WC_Product_Attribute;

/**
 * Class Channel_Engine_Config_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Config_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var \ChannelEngine\BusinessLogic\Authorization\AuthorizationService
	 */
	protected $auth_service;
	/**
	 * @var QueueService
	 */
	protected $queue_service;
	/**
	 * @var ProductsSyncConfigService
	 */
	protected $product_config_service;
	/**
	 * @var Order_Config_Service
	 */
	protected $order_config_service;
	/**
	 * @var Products_Service
	 */
	protected $product_service;
	/**
	 * @var Attribute_Mappings_Service
	 */
	protected $attribute_mappings_service;
	/**
	 * @var Extra_Data_Attribute_Mappings_Service
	 */
	protected $extra_data_attribute_mappings_service;

    /**
     * Retrieves account data.
     *
     * @throws FailedToRetrieveAuthInfoException
     * @throws QueryFilterInvalidParamException
     */
    public function get_account_data() {
        $auth_info = $this->get_auth_service()->getAuthInfo();

        $this->return_json( [
            'apiKey'      => $auth_info->getApiKey(),
            'accountName' => $auth_info->getAccountName(),
        ] );
    }

    /**
     * Retrieves account data.
     *
     * @throws FailedToRetrieveAuthInfoException
     * @throws QueryFilterInvalidParamException
     */
    public function get_account_name() {
        $this->return_json( [
            'accountName' => $this->get_auth_service()->getAuthInfo()->getAccountName(),
        ] );
    }

	/**
	 * Retrieves stock synchronization configuration.
	 */
	public function get_stock_sync_config() {
        $syncConfig = $this->get_product_config_service()->get();
        if ($syncConfig === null) {
            $this->return_json([
                'stockQuantity'    => 0,
                'enabledStockSync' => true
            ]);
        }

		$this->return_json( [
			'stockQuantity'    => $syncConfig->getDefaultStock(),
			'enabledStockSync' => $syncConfig->isEnabledStockSync()
		] );
	}

	/**
	 * Retrieves information about stock synchronization flag.
	 */
	public function is_enabled_stock_sync() {
		$this->return_json( [
			'enabledStockSync' => $this->get_product_config_service()->get()->isEnabledStockSync()
		] );
	}

	/**
	 * Disconnects user.
	 */
	public function disconnect() {
		try {
			/** @var WebhooksService $webhook_service */
			$webhook_service = ServiceRegister::getService( WebhooksService::class );
			$webhook_service->delete();
		} catch ( Exception $e ) {
			Logger::logError( 'Failed to delete webhook because: ' . $e->getMessage() );
		}

		$database = new Database( new Plugin_Options_Repository() );
		$database->remove_data();

		$this->return_json( [ 'success' => true ] );
	}

	/**
	 * Triggers sync.
	 */
	public function trigger_sync() {
		$post = json_decode( $this->get_raw_input(), true );

		try {
			Trigger_Sync_Service::trigger( $post );
		} catch ( QueueStorageUnavailableException $e ) {
			$this->return_json( [
				'success' => false,
				'message' => sprintf( __( 'Failed to start initial sync because %s', 'channelengine' ), $e->getMessage() ),
			] );
		}
		$this->return_json( [ 'success' => true ] );
	}

	/**
	 * Checks sync status.
	 */
	public function check_status() {
		$this->return_json( [ 'in_progress' => $this->sync_in_progress() ] );
	}

	/**
	 * Saves configuration data.
	 */
	public function save() {
		$post = json_decode( $this->get_raw_input(), true );
		try {
			$this->save_account_data( $post['apiKey'], $post['accountName'] );
			$this->save_stock_sync_config( $post['stockQuantity'], $post['enabledStockSync'] );
			$this->save_order_statuses( $post['orderStatuses'], $post['orderSyncConfig'], $post['enableReduceStock'] );
			$this->save_product_attribute_mapping( $post['attributeMappings'] );
			$this->get_extra_data_attribute_mapping_service()
			     ->setExtraDataAttributeMappings( new ExtraDataAttributeMappings( $post['extraDataMappings'] ) );
		} catch ( BaseException $e ) {
			$this->return_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}

		$this->return_json( [
			'success' => true,
			'message' => __( 'Configuration saved successfully.' )
		] );
	}

	/**
	 * Retrieves extra data attribute mapping.
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function get_extra_data_mappings() {
		$mappings = $this->get_extra_data_attribute_mapping_service()->getExtraDataAttributeMappings();

		$this->return_json( [
			'extra_data_mapping' => $mappings ? $mappings->get_mappings() : []
		] );
	}

	/**
	 * Retrieves product attributes.
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	protected function get_product_attributes() {
		$selectedMapping      = $this->get_attribute_mapping_service()->getAttributeMappings();
		$standard_attributes  = $this->get_product_service()->get_standard_product_attributes();
		$custom_attributes    = $this->get_product_service()->get_custom_product_attributes();
		$formatted_attributes = $this->get_formatted_product_attributes( $standard_attributes, $custom_attributes );

		if ( $selectedMapping ) {
			$this->return_json( [
				'product_attributes'    => $formatted_attributes,
				'brand'                 => $selectedMapping->get_brand(),
				'color'                 => $selectedMapping->get_color(),
				'size'                  => $selectedMapping->get_size(),
				'gtin'                  => $selectedMapping->get_gtin(),
				'catalogue_price'       => $selectedMapping->get_catalogue_price(),
				'price'                 => $selectedMapping->get_price(),
				'purchase_price'        => $selectedMapping->get_purchase_price(),
				'details'               => $selectedMapping->get_details(),
				'category'              => $selectedMapping->get_category(),
				'vendor_product_number' => $selectedMapping->get_vendor_product_number(),
				'shipping_time' => $selectedMapping->get_shipping_time()
			] );
		}

		$default_attributes = $this->get_default_attribute_mapping_values( array_merge( $standard_attributes, $custom_attributes ) );

		$this->return_json( [
			'product_attributes'    => $formatted_attributes,
			'brand'                 => $default_attributes['brand'] ?: '',
			'color'                 => $default_attributes['color'] ?: '',
			'size'                  => $default_attributes['size'] ?: '',
			'gtin'                  => $default_attributes['gtin'] ?: $default_attributes['ean'] ?: '',
			'catalogue_price'       => $default_attributes['msrp'] ?: $default_attributes['manufacturer_price'] ?: $default_attributes['vendor_price'] ?: '',
			'price'                 => $default_attributes['price'] ?: $default_attributes[ Standard_Product_Attributes::PREFIX . '_price_incl_tax' ] ?: '',
			'purchase_price'        => $default_attributes['purchase_price'] ?: '',
			'details'               => $default_attributes['details'] ?: $default_attributes[ Standard_Product_Attributes::PREFIX . '_description' ] ?: '',
			'category'              => $default_attributes['category'] ?: $default_attributes[ Standard_Product_Attributes::PREFIX . '_category' ] ?: '',
			'vendor_product_number' => $default_attributes['vendor_product_number'] ?: '',
			'shipping_time' => $default_attributes['shipping_time'] ?: ''
		] );
	}

	/**
	 * Get formatted product attributes
	 *
	 * @param WC_Product_Attribute[] $standard_attributes
	 * @param WC_Product_Attribute[] $custom_attributes
	 *
	 * @return array
	 */
	protected function get_formatted_product_attributes( array $standard_attributes, array $custom_attributes ) {
		$formatted_attributes = [
			'custom'   => [],
			'standard' => [],
		];

		foreach ( $standard_attributes as $attribute ) {
			$attribute_name = $attribute->get_data()['name'];

			$formatted_attributes['standard'][] = [
				'value' => $attribute_name,
				'label' => __( $attribute_name, 'channelengine' ),
			];
		}

		foreach ( $custom_attributes as $attribute ) {
			$attribute_name = $attribute->get_data()['name'];

			foreach ( $formatted_attributes['custom'] as $formatted_attribute ) {
				if ( $formatted_attribute['value'] === $attribute_name ) {
					continue 2;
				}
			}

			$formatted_attributes['custom'][] = [
				'value' => $attribute_name,
				'label' => __( $attribute_name, 'channelengine' ),
			];
		}

		return $formatted_attributes;
	}

	/**
	 * Saves product attribute mapping.
	 *
	 * @param $mappings
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	protected function save_product_attribute_mapping( $mappings ) {
		$mappings_dto = new AttributeMappings(
			$mappings['brand'] !== '' ? $mappings['brand'] : null,
			$mappings['color'] !== '' ? $mappings['color'] : null,
			$mappings['size'] !== '' ? $mappings['size'] : null,
			$mappings['gtin'] !== '' ? $mappings['gtin'] : null,
			$mappings['cataloguePrice'] !== '' ? $mappings['cataloguePrice'] : null,
			$mappings['price'] !== '' ? $mappings['price'] : null,
			$mappings['purchasePrice'] !== '' ? $mappings['purchasePrice'] : null,
			$mappings['details'] !== '' ? $mappings['details'] : null,
			$mappings['category'] !== '' ? $mappings['category'] : null,
            $mappings['vendorProductNumber'] !== '' ? $mappings['vendorProductNumber'] : null,
			$mappings['shippingTime'] !== '' ? $mappings['shippingTime'] : null
		);

		$this->get_attribute_mapping_service()->setAttributeMappings( $mappings_dto );
	}

	/**
	 * Returns array of default attribute mapping values.
	 *
	 * @param $attributes
	 *
	 * @return array
	 */
	protected function get_default_attribute_mapping_values( $attributes ) {
		$default_attribute_mapping = [];
		foreach ( $attributes as $attribute ) {
			$attribute_name                               = str_replace( ' ', '_', $attribute->get_data()['name'] );
			$default_attribute_mapping[ $attribute_name ] = $attribute->get_data()['name'];
		}

		return $default_attribute_mapping;
	}

	/**
	 * Saves account data.
	 *
	 * @param $api_key
	 * @param $account_name
	 *
	 * @throws QueryFilterInvalidParamException
	 * @throws CurrencyMismatchException
	 * @throws BaseException
	 */
	protected function save_account_data( $api_key, $account_name ) {

		try {
			$this->get_auth_service()->validateAccountInfo( $api_key, $account_name, get_woocommerce_currency() );

			$auth_info = AuthInfo::fromArray( [ 'account_name' => $account_name, 'api_key' => $api_key ] );
			$this->get_auth_service()->setAuthInfo( $auth_info );
		} catch ( Exception $e ) {
			throw new BaseException( __( 'Invalid API key or Account name.', 'channelengine' ) );
		}
	}

	/**
	 * Saves product stock synchronization configuration.
	 *
	 * @param $stock_quantity
	 * @param $enable_stock_sync
	 *
	 * @throws Stock_Quantity_Invalid|Stock_Sync_Flag_Invalid
	 */
	protected function save_stock_sync_config( $stock_quantity, $enable_stock_sync ) {
		if ( $stock_quantity !== '0' && ( ! filter_var( $stock_quantity, FILTER_VALIDATE_INT ) || (int) $stock_quantity < 0 ) ) {
			throw new Stock_Quantity_Invalid( __( 'Stock quantity is not valid.', 'channelengine' ) );
		}

		if ( ! is_bool( $enable_stock_sync ) ) {
			throw new Stock_Sync_Flag_Invalid( __( 'Stock synchronization flag is not valid.', 'channelengine' ) );
		}

		$config = new SyncConfig();
		$config->setDefaultStock( $stock_quantity );
		$config->setEnabledStockSync( $enable_stock_sync );

		$this->get_product_config_service()->set( $config );
	}

	/**
	 * Saves order statuses.
	 *
	 * @param $order_statuses
	 * @param $order_sync_config
	 * @param $enable_reduce_stock
	 *
	 * @throws Order_Statuses_Invalid
	 * @throws Order_Sync_Config_Invalid
	 */
	protected function save_order_statuses( $order_statuses, $order_sync_config, $enable_reduce_stock ) {
		if ( ! $this->get_order_config_service()->are_statuses_valid( $order_statuses ) ) {
			throw new Order_Statuses_Invalid( __( 'Order statuses are not valid.', 'channelengine' ) );
		}
		if ( ! $this->get_order_config_service()->is_sync_config_valid( $order_sync_config ) ) {
			throw new Order_Sync_Config_Invalid( __( 'Order synchronization config values are not valid.', 'channelengine' ) );
		}

		$orderSyncConfig = new OrderSyncConfig();
		$orderSyncConfig->setIncomingOrders( $order_statuses['incoming'] );
		$orderSyncConfig->setShippedOrders( $order_statuses['shipped'] );
		$orderSyncConfig->setFulfilledOrders( $order_statuses['fulfilledByMp'] );
		$orderSyncConfig->setEnableShipmentInfoSync( $order_sync_config['enableShipmentInfoSync'] );
		$orderSyncConfig->setEnableOrderCancellationSync( $order_sync_config['enableOrderCancellationSync'] );
		$orderSyncConfig->setEnableOrdersByMerchantSync( $order_sync_config['enableOrdersByMerchantSync'] );
		$orderSyncConfig->setEnableOrdersByMarketplaceSync( $order_sync_config['enableOrdersByMarketplaceSync'] );
		$orderSyncConfig->setEnableReduceStock( $enable_reduce_stock );

		$this->get_order_config_service()->saveOrderSyncConfig( $orderSyncConfig );
	}

	/**
	 * @return bool
	 */
	protected function sync_in_progress() {
		$product_sync = $this->get_queue_service()->findLatestByType( 'ProductSync' );
		$order_sync   = $this->get_queue_service()->findLatestByType( 'OrderSync' );

		return $product_sync && $order_sync && ( $product_sync->getStatus() !== QueueItem::COMPLETED
		                                         || $order_sync->getStatus() !== QueueItem::COMPLETED );
	}

	/**
	 * @return AuthorizationService
	 */
	protected function get_auth_service() {
		if ( $this->auth_service === null ) {
			$this->auth_service = ServiceRegister::getService( AuthorizationService::class );
		}

		return $this->auth_service;
	}

	/**
	 * @return QueueService
	 */
	protected function get_queue_service() {
		if ( $this->queue_service === null ) {
			$this->queue_service = ServiceRegister::getService( QueueService::class );
		}

		return $this->queue_service;
	}

	/**
	 * @return ProductsSyncConfigService
	 */
	protected function get_product_config_service() {
		if ( $this->product_config_service === null ) {
			$this->product_config_service = ServiceRegister::getService( ProductsSyncConfigService::class );
		}

		return $this->product_config_service;
	}

	/**
	 * Retrieves instance of Order_Config_Service.
	 *
	 * @return Order_Config_Service
	 */
	protected function get_order_config_service() {
		if ( $this->order_config_service === null ) {
			$this->order_config_service = ServiceRegister::getService( OrdersConfigurationService::class );
		}

		return $this->order_config_service;
	}

	/**
	 * Retrieves instance of ProductsService.
	 *
	 * @return Products_Service
	 */
	protected function get_product_service() {
		if ( $this->product_service === null ) {
			$this->product_service = ServiceRegister::getService( ProductsService::class );
		}

		return $this->product_service;
	}

	/**
	 * Retrieves instance of Attribute_Mappings_Service.
	 *
	 * @return Attribute_Mappings_Service
	 */
	protected function get_attribute_mapping_service() {
		if ( $this->attribute_mappings_service === null ) {
			$this->attribute_mappings_service = ServiceRegister::getService( Attribute_Mappings_Service::class );
		}

		return $this->attribute_mappings_service;
	}

	/**
	 * Retrieves instance of Extra_Data_Attribute_Mappings_Service.
	 *
	 * @return Attribute_Mappings_Service
	 */
	protected function get_extra_data_attribute_mapping_service() {
		if ( $this->extra_data_attribute_mappings_service === null ) {
			$this->extra_data_attribute_mappings_service = ServiceRegister::getService( Extra_Data_Attribute_Mappings_Service::class );
		}

		return $this->extra_data_attribute_mappings_service;
	}
}