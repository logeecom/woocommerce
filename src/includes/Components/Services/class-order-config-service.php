<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;

/**
 * Class Order_Config_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Order_Config_Service extends OrdersConfigurationService {
	/**
	 * Validates order statuses.
	 *
	 * @param array $statuses
	 *
	 * @return bool
	 */
	public function are_statuses_valid($statuses) {
		$order_statuses = array_keys( wc_get_order_statuses() );

		return isset( $statuses['incoming'], $statuses['shipped'], $statuses['fulfilledByMp'] ) &&
		       in_array( $statuses['incoming'], $order_statuses, true ) &&
		       in_array( $statuses['shipped'], $order_statuses, true ) &&
		       in_array( $statuses['fulfilledByMp'], $order_statuses, true );
	}
}