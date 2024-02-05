<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrderSyncConfig;
use ChannelEngine\Components\Services\Order_Config_Service;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Utility\Script_Loader;
use DateTime;
use Exception;

/**
 * Class Channel_Engine_Order_Status_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Order_Status_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var Order_Config_Service
	 */
	protected $order_config_service;

	/**
	 * Retrieves order statuses.
	 *
	 * @throws RepositoryNotRegisteredException
	 */
	public function get() {
		$order_statuses                 = wc_get_order_statuses();
		$statuses                       = $this->format_order_statuses( $order_statuses );
		$mappings                       = $this->get_order_config_service()->getOrderSyncConfig();
		$order_by_marketplace_time_from = $this->get_order_config_service()->getClosedOrdersSyncTime();

		$this->return_json( array(
			'order_statuses'                              => $statuses,
			'incoming'                                    => ( $mappings && $mappings->getIncomingOrders() !== null ) ?
				array(
					'value' => $mappings->getIncomingOrders(),
					'label' => __( $mappings->getIncomingOrders(), 'channelengine-wc' ),
				) : array(
					'value' => 'wc-processing',
					'label' => __( 'wc-processing', 'channelengine-wc' ),
				),
			'shipped'                                     => ( $mappings && $mappings->getShippedOrders() !== null ) ?
				array(
					'value' => $mappings->getShippedOrders(),
					'label' => __( $mappings->getShippedOrders(), 'channelengine-wc' ),
				) : array(
					'value' => 'wc-completed',
					'label' => __( 'wc-completed', 'channelengine-wc' ),
				),
			'fulfilledByMp'                               => ( $mappings && $mappings->getFulfilledOrders() !== null ) ?
				array(
					'value' => $mappings->getFulfilledOrders(),
					'label' => __( $mappings->getFulfilledOrders(), 'channelengine-wc' ),
				) : array(
					'value' => 'wc-completed',
					'label' => __( 'wc-completed', 'channelengine-wc' ),
				),
			'enableShipmentInfoSync'                      =>
				! ( $mappings && $mappings->isEnableShipmentInfoSync() !== null ) || $mappings->isEnableShipmentInfoSync(),
			'enableOrderCancellationSync'                 =>
				! ( $mappings && $mappings->isEnableOrderCancellationSync() !== null ) || $mappings->isEnableOrderCancellationSync(),
			'enableOrdersByMerchantSync'                  =>
				! ( $mappings && $mappings->isEnableOrdersByMerchantSync() !== null ) || $mappings->isEnableOrdersByMerchantSync(),
			'enableOrdersByMarketplaceSync'               =>
				! ( $mappings && $mappings->isEnableOrdersByMarketplaceSync() !== null ) || $mappings->isEnableOrdersByMarketplaceSync(),
			'ordersByMarketplaceFromDate'                 => null != $order_by_marketplace_time_from && 0 !== $order_by_marketplace_time_from->getTimestamp() ?
				$order_by_marketplace_time_from->format( 'd.m.Y.' ) : gmdate( 'd.m.Y' ),
			'enableReduceStock'                           =>
				! ( $mappings && $mappings->isEnableReduceStock() !== null ) || $mappings->isEnableReduceStock(),
			'displayTheDateFromWhichOrdersFBMAreImported' => null != $order_by_marketplace_time_from && 0 !== $order_by_marketplace_time_from->getTimestamp()
		) );
	}

	/**
	 * Saves order statuses.
	 *
	 * @throws RepositoryNotRegisteredException
	 * @throws Exception
	 */
	public function save() {
		$this->get_state_service()->set_order_configured( true );
		$this->save_values();
	}

	/**
	 * @throws RepositoryNotRegisteredException
	 * @throws Exception
	 */
	public function save_values() {
		$payload = json_decode( $this->get_raw_input(), true );

		if ( ! $this->get_order_config_service()->are_statuses_valid( $payload ) ) {
			$this->return_json( array(
				'success' => false,
				'message' => __( 'Invalid values.', 'channelengine-wc' ),
			) );
		}

		$orderSyncConfig = new OrderSyncConfig();
		$orderSyncConfig->setIncomingOrders( $payload['incoming'] );
		$orderSyncConfig->setShippedOrders( $payload['shipped'] );
		$orderSyncConfig->setFulfilledOrders( $payload['fulfilledByMp'] );
		$orderSyncConfig->setEnableShipmentInfoSync( $payload['enableShipmentInfoSync'] );
		$orderSyncConfig->setEnableOrderCancellationSync( $payload['enableOrderCancellationSync'] );
		$orderSyncConfig->setEnableOrdersByMerchantSync( $payload['enableOrdersByMerchantSync'] );
		$orderSyncConfig->setEnableOrdersByMarketplaceSync( $payload['enableOrdersByMarketplaceSync'] );
		$orderSyncConfig->setEnableReduceStock( $payload['enableReduceStock'] );

		$this->get_order_config_service()->saveOrderSyncConfig( $orderSyncConfig );
		$this->get_order_config_service()->setClosedOrdersSyncTime( new DateTime( $payload['startSyncDate'] ) );

		$this->return_json( array( 'success' => true ) );
	}

	/**
	 * Retrieves information for shipment synchronization.
	 */
	public function get_sync_shipment_status() {
		$sync_config = $this->get_order_config_service()->getOrderSyncConfig();
		if ( $sync_config ) {
			$this->return_json( array( 'enableShipmentInfoSync' => $sync_config->isEnableShipmentInfoSync() ) );
		} else {
			$this->return_json( array( 'enableShipmentInfoSync' => array() ) );

		}
	}

	/**
	 * @inheritDoc
	 */
	protected function load_resources() {
		parent::load_resources();

		Script_Loader::load_js( array(
			'/js/OrderStatuses.js',
			'/js/ModalService.js',
			'/js/DisconnectService.js',
			'/js/Disconnect.js',
		) );
	}

	/**
	 * Formats order statuses.
	 *
	 * @param array $order_statuses
	 *
	 * @return array
	 */
	protected function format_order_statuses( array $order_statuses ) {
		$formatted_statuses = array();

		foreach ( $order_statuses as $key => $value ) {
			$formatted_statuses[] = array(
				'value' => $key,
				'label' => __( $key, 'channelengine-wc' ),
			);
		}

		return $formatted_statuses;
	}

	/**
	 * Retrieves instance of Order_Config_Service.
	 *
	 * @return Order_Config_Service
	 */
	protected function get_order_config_service() {
		if ( null === $this->order_config_service ) {
			$this->order_config_service = ServiceRegister::getService( OrdersConfigurationService::class );
		}

		return $this->order_config_service;
	}

	/**
	 * Retrieves instance of State_Service.
	 *
	 * @return State_Service
	 */
	protected function get_state_service() {
		return ServiceRegister::getService( State_Service::class );
	}
}
