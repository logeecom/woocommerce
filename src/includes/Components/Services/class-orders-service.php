<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\API\Orders\DTO\Address;
use ChannelEngine\BusinessLogic\API\Orders\DTO\Order;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Orders\Domain\CreateResponse;
use ChannelEngine\BusinessLogic\Orders\OrdersService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\Components\Exceptions\ProductNotAvailableException;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\ServiceRegister;
use WC_Data_Exception;
use WC_Order;
use WC_Order_Item_Shipping;
use WC_Order_Item_Tax;
use WC_Product;
use WP_Error;

/**
 * Class Orders_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Orders_Service extends OrdersService {

	/**
	 * @const string
	 */
	const MARKETPLACE_TYPE_OF_FULFILLMENT = 'Marketplace';

	/**
	 * Creation of order with taxes requires integer value for the tax rate id. We do not want to introduce new custom tax rate,
	 * but we do want to create order with taxes, therefore we use max integer value as a custom fake rate id.
	 */
	const CUSTOM_TAX_RATE_ID = PHP_INT_MAX;

	/**
	 * @var Order_Config_Service
	 */
	protected $order_config_service;
	/**
	 * @var ProductsSyncConfigService
	 */
	protected $product_sync_config_service;

	/**
	 * Creates new orders in the shop system and
	 * returns CreateResponse.
	 *
	 * @param Order $order
	 *
	 * @return CreateResponse
	 */
	public function create( Order $order ) {
		try {
			if ( $this->orderFromChannelEngineAlreadyExists( $order ) ) {
				return $this->create_response( false, '', 'Order already created' );
			}

			$wc_products = $this->fetch_products( $order );
			$order_data  = $this->format_order_data( $order );
			$wc_order    = wc_create_order( $order_data );

			if ( $wc_order instanceof WP_Error ) {
				return $this->create_response( false, '', 'Failed to create a new order.' );
			}

			$wc_order->set_date_created( $order->getOrderDate()->format( DATE_ATOM ) );
			$wc_order->set_total( $order->getTotalInclVat() );
			$wc_order->set_shipping_total( $order->getShippingCostsInclVat() );
			$wc_order->set_shipping_address( $this->format_address_data( $order->getShippingAddress(), $order->getEmail(), $order->getPhone() ) );
			$wc_order->set_billing_address( $this->format_address_data( $order->getBillingAddress(), $order->getEmail(), $order->getPhone() ) );

			$this->add_items( $wc_products, $wc_order );
			$wc_order->add_item( $this->get_shipping_item( $order ) );
			if ( wc_tax_enabled() ) {
				$wc_order->set_shipping_total( $order->getShippingCostsInclVat() - $order->getShippingCostsVat() );
				$wc_order->set_shipping_tax( $order->getShippingCostsVat() );
				$wc_order->set_cart_tax( $order->getTotalVat() - $order->getShippingCostsVat() );
				$wc_order->add_item( $this->get_tax_subtotal_item( $order ) );
			}

			$wc_order->add_meta_data( '_channel_engine_order_id', $order->getId() );
			$wc_order->add_meta_data( '_channel_engine_channel_name', $order->getChannelName() );
			$wc_order->add_meta_data( '_channel_engine_channel_order_no', $order->getChannelOrderNo() );
			$wc_order->add_meta_data( '_channel_engine_type_of_fulfillment', $order->getTypeOfFulfillment() );
			$wc_order->add_meta_data( '_channel_engine_payment_method', $order->getPaymentMethod() );

			if ( $order->getTypeOfFulfillment() === self::MARKETPLACE_TYPE_OF_FULFILLMENT ) {
				$wc_order->add_meta_data( '_ce_order_shipped', true );
			}

			$wc_order->save();
			if ( $this->get_product_sync_config_service()->get()->isEnabledStockSync()
				 && $this->get_order_config_service()->getOrderSyncConfig()->isEnableReduceStock() ) {
				wc_reduce_stock_levels( $wc_order->get_id() );
			}
		} catch ( BaseException $e ) {
			return $this->create_response( false, '', $e->getMessage() );
		} catch ( WC_Data_Exception $e ) {
			return $this->create_response( false, '', 'Failed to sync order because: ' . $e->getMessage() );
		}

		return $this->create_response( true, $wc_order->get_id(), 'Successfully created order.' );
	}

	/**
	 * Formats order data for WooCommerce order creation.
	 *
	 * @param Order $order
	 *
	 * @return array
	 */
	protected function format_order_data( Order $order ) {
		$config = $this->get_order_config_service()->getOrderSyncConfig();

		switch ( $order->getStatus() ) {
			case 'NEW':
				$status = $config ? $config->getIncomingOrders() : $order->getStatus();
				break;
			case 'CLOSED':
			case 'SHIPPED':
				$status = $config ? $config->getFulfilledOrders() : $order->getStatus();
				break;
			default:
				$status = $order->getStatus();
		}

		return array(
			'status'      => $status,
			'customer_id' => null,
		);
	}

	/**
	 * Formats order address data.
	 *
	 * @param Address $address
	 * @param string $email
	 * @param string $phone
	 *
	 * @return array
	 */
	protected function format_address_data( Address $address, $email, $phone ) {
		return array(
			'first_name' => $address->getFirstName(),
			'last_name'  => $address->getLastName(),
			'company'    => $address->getCompanyName(),
			'address_1'  => $address->getStreetName() . ' ' .
							$address->getHouseNumber() . ' ' . $address->getHouseNumberAddition(),
			'address_2'  => '',
			'city'       => $address->getCity(),
			'postcode'   => $address->getZipCode(),
			'country'    => strtoupper( $address->getCountryIso() ),
			'state'      => $address->getRegion(),
			'email'      => $email,
			'phone'      => $phone,
		);
	}

	/**
	 * Fetches products for given order.
	 *
	 * @param Order $order
	 *
	 * @return array
	 *
	 * @throws ProductNotAvailableException
	 */
	protected function fetch_products( Order $order ) {
		$result = array();

		$product_ids_batch = $this->get_product_ids( $order );
		$products          = wc_get_products( array( 'include' => $product_ids_batch ) );
		$variations        = wc_get_products(
			array(
				'type'    => 'variation',
				'include' => $product_ids_batch,
			)
		);
		$products          = array_merge( $products, $variations );

		foreach ( $order->getLines() as $order_line ) {
			$product = $this->get_product( $products, (int) $order_line->getMerchantProductNo() );

			$product_data = array(
				'name'         => $product->get_name(),
				'sku'          => $product->get_sku(),
				'variation_id' => $product->is_type( 'variation' ) ? $order_line->getMerchantProductNo() : 0,
				'subtotal'     => $order_line->getUnitPriceInclVat(),
				'quantity'     => $order_line->getQuantity(),
				'total'        => $order_line->getLineTotalInclVat(),
				'total_tax'    => 0,
				'subtotal_tax' => 0,
				'taxes'        => array(
					'total'    => array(),
					'subtotal' => array(),
				),
			);

			if ( wc_tax_enabled() ) {
				$product_data['subtotal']     -= $order_line->getUnitVat();
				$product_data['total']        -= $order_line->getLineVat();
				$product_data['total_tax']    = $order_line->getLineVat();
				$product_data['subtotal_tax'] = $order_line->getLineVat();
				$product_data['taxes']        = array(
					'total'    => array( self::CUSTOM_TAX_RATE_ID => $order_line->getLineVat() ),
					'subtotal' => array( self::CUSTOM_TAX_RATE_ID => $order_line->getLineVat() ),
				);
			}

			$result[] = array(
				'product'      => $product,
				'product_data' => $product_data,
			);
		}

		return $result;
	}

	/**
	 * Retrieves product.
	 *
	 * @param WC_Product[] $products
	 * @param $id
	 *
	 * @return WC_Product
	 *
	 * @throws ProductNotAvailableException
	 */
	protected function get_product( $products, $id ) {
		foreach ( $products as $product ) {
			if ( $product->get_id() === $id ) {
				return $product;
			}
		}

		throw new ProductNotAvailableException( "Product with id $id does not exist." );
	}

	/**
	 * Retrieves product ids.
	 *
	 * @param Order $order
	 *
	 * @return array
	 */
	protected function get_product_ids( Order $order ) {
		$result = array();

		foreach ( $order->getLines() as $order_line ) {
			$result[] = $order_line->getMerchantProductNo();
		}

		return $result;
	}

	/**
	 * Adds product items to order.
	 *
	 * @param array $wc_products
	 * @param WC_Order $wc_order
	 *
	 * @throws WC_Data_Exception
	 */
	protected function add_items( array $wc_products, WC_Order $wc_order ) {
		foreach ( $wc_products as $wc_product ) {
			$wc_order->add_product(
				$wc_product['product'],
				$wc_product['product_data']['quantity'],
				$wc_product['product_data']
			);
		}
	}

	/**
	 * Gets order shipping item.
	 *
	 * @throws WC_Data_Exception
	 */
	protected function get_shipping_item( Order $order ) {
		$shipping_item = new WC_Order_Item_Shipping();
		$shipping_item->set_name( 'Shipping' );
		$shipping_item->set_total( $order->getShippingCostsInclVat() );

		if ( wc_tax_enabled() ) {
			$shipping_item->set_total( $order->getShippingCostsInclVat() - $order->getShippingCostsVat() );
			$shipping_item->set_taxes( array( 'total' => array( self::CUSTOM_TAX_RATE_ID => $order->getShippingCostsVat() ) ) );
		}

		return $shipping_item;
	}

	/**
	 * Gets order tax subtotal item.
	 *
	 * @param Order $order
	 *
	 * @return WC_Order_Item_Tax
	 */
	protected function get_tax_subtotal_item( Order $order ) {
		$tax_subtotal = new WC_Order_Item_Tax();
		$tax_subtotal->set_rate_id( self::CUSTOM_TAX_RATE_ID );
		$tax_subtotal->set_label( 'Tax' );
		$tax_subtotal->set_rate_percent(
			( 100 * $order->getTotalVat() ) / ( $order->getTotalInclVat() - $order->getTotalVat() )
		);
		$tax_subtotal->set_tax_total( $order->getTotalVat() - $order->getShippingCostsVat() );
		$tax_subtotal->set_shipping_tax_total( $order->getShippingCostsVat() );

		return $tax_subtotal;
	}

	/**
	 * Creates CreateResponse.
	 *
	 * @param bool $status
	 * @param string $shopOrderId
	 * @param string $message
	 *
	 * @return CreateResponse
	 */
	protected function create_response( $status, $shopOrderId = '', $message = '' ) {
		$response = new CreateResponse();
		$response->setSuccess( $status );
		$response->setShopOrderId( $shopOrderId );
		$response->setMessage( $message );

		return $response;
	}

	/**
	 * Retrieves an instance of Order_Config_Service.
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
	 * Retrieves an instance of ProductsSyncConfigService.
	 *
	 * @return ProductsSyncConfigService
	 */
	protected function get_product_sync_config_service() {
		if ( null === $this->product_sync_config_service ) {
			$this->product_sync_config_service = ServiceRegister::getService( ProductsSyncConfigService::class );
		}

		return $this->product_sync_config_service;
	}

	/**
	 * Checks if order from ChannelEngine already exist in WooCommerce.
	 *
	 * @param Order $order
	 *
	 * @return bool
	 */
	private function orderFromChannelEngineAlreadyExists( Order $order ): bool {
		$wc_orders = wc_get_orders(
			array(
				'meta_key'   => '_channel_engine_order_id',
				'meta_value' => $order->getId(),
			)
		);

		return ! empty( $wc_orders );
	}
}
