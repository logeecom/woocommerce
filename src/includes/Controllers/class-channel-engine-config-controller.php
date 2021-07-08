<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\API\Orders\Http\Proxy;
use ChannelEngine\BusinessLogic\Authorization\Contracts\AuthorizationService;
use ChannelEngine\BusinessLogic\Authorization\DTO\AuthInfo;
use ChannelEngine\BusinessLogic\Authorization\Exceptions\CurrencyMismatchException;
use ChannelEngine\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use ChannelEngine\BusinessLogic\InitialSync\OrderSync;
use ChannelEngine\BusinessLogic\InitialSync\ProductSync;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrderSyncConfig;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\Products\Entities\SyncConfig;
use ChannelEngine\BusinessLogic\Webhooks\Contracts\WebhooksService;
use ChannelEngine\Components\Exceptions\Order_Statuses_Invalid;
use ChannelEngine\Components\Exceptions\Stock_Quantity_Invalid;
use ChannelEngine\Components\Services\Order_Config_Service;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\Http\HttpClient;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;
use ChannelEngine\Repositories\Plugin_Options_Repository;
use ChannelEngine\Utility\Database;
use Exception;

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
	 * Retrieves stock quantity.
	 */
	public function get_stock_quantity() {
		$this->return_json( [
			'stockQuantity' => $this->get_product_config_service()->get()->getDefaultStock(),
		] );
	}

	/**
	 * Disconnects user.
	 */
	public function disconnect() {
		$webhook_service = ServiceRegister::getService(WebhooksService::class);
		$webhook_service->delete();
		$database = new Database( new Plugin_Options_Repository() );
		$database->remove_data();

		$this->return_json( [ 'success' => true ] );
	}

	/**
	 * Triggers sync.
	 */
	public function trigger_sync() {
		$post         = json_decode( $this->get_raw_input(), true );
		$order_sync   = $post['order_sync'];
		$product_sync = $post['product_sync'];
		try {
			if ( $order_sync ) {
				$this->get_queue_service()->enqueue( 'channel-engine-orders', new OrderSync() );
				$this->get_state_service()->set_manual_order_sync_in_progress(true);
			}

			if ( $product_sync ) {
				$this->get_queue_service()->enqueue( 'channel-engine-products', new ProductSync() );
				$this->get_state_service()->set_manual_product_sync_in_progress(true);
			}
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
			$this->save_stock_quantity( $post['stockQuantity'] );
			$this->save_order_statuses( $post['orderStatuses'] );
		} catch ( BaseException $e ) {
			$this->return_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}

		$this->return_json( [
		    'success' => true,
            'message' => __('Configuration saved successfully.')
        ] );
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

            // @todo Delete when account endpoint is available
            $orderProxy = new Proxy(ServiceRegister::getService(HttpClient::class), $account_name, $api_key);
            $orderProxy->getNew();

            $auth_info = AuthInfo::fromArray( [ 'account_name' => $account_name, 'api_key' => $api_key ] );
            $this->get_auth_service()->setAuthInfo( $auth_info );
        } catch ( Exception $e ) {
            throw new BaseException(__( 'Invalid API key or Account name.', 'channelengine' ));
        }
	}

	/**
	 * Saves default stock quantity.
	 *
	 * @param $stock_quantity
	 *
	 * @throws Stock_Quantity_Invalid
	 */
	protected function save_stock_quantity( $stock_quantity ) {
		if ( ! filter_var( $stock_quantity, FILTER_VALIDATE_INT ) || (int) $stock_quantity < 0 ) {
			throw new Stock_Quantity_Invalid( __( 'Stock quantity is not valid.', 'channelengine' ) );
		}

		$config = new SyncConfig();
		$config->setDefaultStock( $stock_quantity );

		$this->get_product_config_service()->set( $config );
	}

	/**
	 * Saves order statuses.
	 *
	 * @param $order_statuses
	 *
	 * @throws QueryFilterInvalidParamException
	 * @throws Order_Statuses_Invalid
	 */
	protected function save_order_statuses( $order_statuses ) {
		if ( ! $this->get_order_config_service()->are_statuses_valid( $order_statuses ) ) {
			throw new Order_Statuses_Invalid( __( 'Order statuses are not valid.', 'channelengine' ) );
		}

		$orderSyncConfig = new OrderSyncConfig();
		$orderSyncConfig->setIncomingOrders( $order_statuses['incoming'] );
		$orderSyncConfig->setShippedOrders( $order_statuses['shipped'] );
		$orderSyncConfig->setFulfilledOrders( $order_statuses['fulfilledByMp'] );

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
}