<?php

namespace ChannelEngine\Components\Tasks;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsReplaceTask;
use ChannelEngine\Components\Services\Replace_Products_Service;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class Products_Replace_Task
 *
 * @package ChannelEngine\Components\Tasks
 */
class Products_Replace_Task extends ProductsReplaceTask {

	/**
	 * @return ProductsService
	 */
	protected function getProductsService() {
		return ServiceRegister::getService( Replace_Products_Service::class );
	}
}
