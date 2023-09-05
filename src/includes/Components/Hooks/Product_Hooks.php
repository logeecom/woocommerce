<?php

namespace ChannelEngine\Components\Hooks;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\Products\Domain\ProductPurged;
use ChannelEngine\BusinessLogic\Products\Domain\ProductReplaced;
use ChannelEngine\BusinessLogic\Products\Domain\ProductUpsert;
use ChannelEngine\BusinessLogic\Products\Handlers\ProductPurgedEventHandler;
use ChannelEngine\BusinessLogic\Products\Handlers\ProductReplacedEventHandler;
use ChannelEngine\BusinessLogic\Products\Handlers\ProductUpsertEventHandler;
use ChannelEngine\Components\Services\Plugin_Status_Service;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use WC_Product_Variation;
use WP_Query;


/**
 * Class Product_Hooks
 *
 * @package ChannelEngine\Components\Hooks
 */
class Product_Hooks {
	public static function on_product_create( $id ) {
		static::get_task_runner_wakeup()->wakeup();
		$post = get_post( $id );

		if ( $post->post_status === 'publish' ) {
            $handler = new ProductUpsertEventHandler();
            $handler->handle( new ProductUpsert( $id ) );
		} else {
            static::handle_delete_event( $id );
        }
	}

    /**
     * @param $id
     * @return void
     */
    public static function on_product_update( $id ) {
        static::get_task_runner_wakeup()->wakeup();
        $post = get_post( $id );

        if ( $post->post_status === 'publish' ) {
            $handler = new ProductReplacedEventHandler();
            $handler->handle( new ProductReplaced( $id ) );
        } else {
            static::handle_delete_event( $id );
        }
    }

	/**
	 * @param int $id
	 * @param WC_Product_Variation $variation
	 */
	public static function on_variant_create( $id, $variation ) {
		static::get_task_runner_wakeup()->wakeup();
		$handler = new ProductReplacedEventHandler();
        $handler->handle( new ProductReplaced( $id ) );
	}

	public static function on_product_deleted( $id ) {
		$product = wc_get_product( $id );

		if ( ! $product ) {
			return;
		}

		static::get_task_runner_wakeup()->wakeup();
		static::handle_delete_event( $id );
	}

    /**
     * @param $id
     * @return void
     */
    public static function on_variant_deleted( $id ) {
        $product = wc_get_product( $id );

        if ( ! $product ) {
            return;
        }

        static::get_task_runner_wakeup()->wakeup();

        $handler = new ProductReplacedEventHandler();
        $handler->handle( new ProductReplaced( $id ) );
    }

    /**
     * Handles attribute deleted event.
     *
     * @param int $id
     * @param string $attribute
     *
     * @return void
     */
    public static function on_attribute_deleted(int $id, string $attribute) {
        $syncConfig = static::get_product_config_service()->get();

        if ($syncConfig->getThreeLevelSyncAttribute() === $attribute) {
            static::getStatusService()->disable();
        }
    }

    /**
     * Handles attribute value name changed event.
     *
     * @param int $id
     * @return void
     */
    public static function on_attribute_value_updated(int $id) {
        static::get_task_runner_wakeup()->wakeup();

        $term = get_term($id);
        $args = array(
            'posts_per_page' => 20,
            'no_found_rows' => true,
            'post_type' => array('product'),
            'tax_query' =>
                array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => $term->taxonomy,
                        'field' => 'slug',
                        'terms' => $term->slug,
                        'operator' => 'IN'
                    ),
                ),
        );
        $query = new WP_Query($args);
        $products = $query->posts;

        foreach ($products as $product) {
            if ( $product->post_status === 'publish' ) {
                $handler = new ProductReplacedEventHandler();
                $handler->handle( new ProductReplaced( $product->ID ) );
            } else {
                static::handle_delete_event( $product->ID );
            }
        }
    }

	protected static function handle_delete_event( $id ) {
		$handler     = new ProductPurgedEventHandler();
		$product     = wc_get_product( $id );
		$variant_ids = wc_get_products( [
			'type'   => 'variation',
			'parent' => $product->get_id(),
			'limit'  => - 1,
			'return' => 'ids',
		] );

		foreach ( $variant_ids as $variant_id ) {
            $handler->handle( new ProductPurged( $variant_id ) );
		}

        $handler->handle( new ProductPurged( $id ) );
	}

	/**
	 * @return TaskRunnerWakeup
	 */
	protected static function get_task_runner_wakeup() {
		return ServiceRegister::getService( TaskRunnerWakeup::class );
	}
    /**
     * @return ProductsSyncConfigService
     */
    protected static function get_product_config_service() {
        return ServiceRegister::getService( ProductsSyncConfigService::class );
    }

    /**
     * @return Plugin_Status_Service
     */
    protected static function getStatusService(): Plugin_Status_Service {
        return ServiceRegister::getService(Plugin_Status_Service::class);
    }
}