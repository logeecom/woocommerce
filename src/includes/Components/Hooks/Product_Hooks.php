<?php

namespace ChannelEngine\Components\Hooks;

use ChannelEngine\BusinessLogic\Products\Domain\ProductDeleted;
use ChannelEngine\BusinessLogic\Products\Domain\ProductUpsert;
use ChannelEngine\BusinessLogic\Products\Handlers\ProductDeletedEventHandler;
use ChannelEngine\BusinessLogic\Products\Handlers\ProductUpsertEventHandler;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use WC_Product_Variation;

/**
 * Class Product_Hooks
 *
 * @package ChannelEngine\Components\Hooks
 */
class Product_Hooks {
	public static function on_product_create( $id ) {
		static::get_task_runner_wakeup()->wakeup();
		$handler = new ProductUpsertEventHandler();
		$handler->handle( new ProductUpsert( $id ) );
	}

	/**
	 * @param int $id
	 * @param WC_Product_Variation $variation
	 */
	public static function on_variant_create( $id, $variation ) {
		static::get_task_runner_wakeup()->wakeup();
		$handler = new ProductUpsertEventHandler();
		$handler->handle( new ProductUpsert( $id, true, $variation->get_parent_id( 'edit' ) ) );
	}

	public static function on_product_deleted( $id ) {
		$product = wc_get_product($id);

		if (!$product) {
			return;
		}

		static::get_task_runner_wakeup()->wakeup();
		$handler = new ProductDeletedEventHandler();
		$handler->handle( new ProductDeleted( $id ) );
	}

	/**
	 * @return TaskRunnerWakeup
	 */
	protected static function get_task_runner_wakeup() {
		return ServiceRegister::getService(TaskRunnerWakeup::class);
	}
}