<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsDeleteTask;
use ChannelEngine\BusinessLogic\SupportConsole\Contracts\SupportService;
use ChannelEngine\Components\Services\Products_Service;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\QueueService;

/**
 * Class Channel_Engine_Support_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Support_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var SupportService
	 */
	protected $support_service;
    /**
     * @var ProductsService
     */
    protected $products_service;
    /**
     * @var QueueService
     */
    protected $queue_service;

	/**
	 * Return system configuration parameters.
	 */
	public function display() {
		$this->return_json( array( $this->get_support_service()->get() ) );
	}

	/**
	 * Updates system configuration parameters.
	 */
	public function modify() {
		$payload = json_decode( $this->get_raw_input(), true );
		$payload = array_map('sanitize_text_field', $payload );

		$this->return_json( array( $this->get_support_service()->update( $payload ) ) );
	}

    /**
     * Remove all three level products from CE portal.
     *
     * @return void
     *
     * @throws \ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function remove_products_from_channel_engine()
    {
        $toDelete = $this->get_products_service()->count();
        $page = 0;
        $allIds = [];
        while($toDelete > 0) {
            $ids = $this->get_products_service()->getProductIds($page, 500);
            foreach ($ids as $id) {
                $allIds[] = $id;
                $allIds[] = 'CEG-' . $id;
                $allIds[] = 'CE-' . $id;
            }
            $this->get_queue_service()->enqueue('products-delete-queue', new ProductsDeleteTask($allIds));
            $toDelete-= count($allIds);
            $page++;
        }

        $this->return_json( ['success' => true ]);
    }

	/**
	 * @return SupportService
	 */
	protected function get_support_service() {
		if ( null === $this->support_service ) {
			$this->support_service = ServiceRegister::getService( SupportService::class );
		}

		return $this->support_service;
	}

    /**
     * @return Products_Service
     */
    protected function get_products_service() {
        if ( null === $this->products_service ) {
            $this->products_service = ServiceRegister::getService( ProductsService::class );
        }

        return $this->products_service;
    }

    /**
     * @return QueueService
     */
    protected function get_queue_service() {
        if ( null === $this->queue_service ) {
            $this->queue_service = ServiceRegister::getService( QueueService::class );
        }

        return $this->queue_service;
    }
}
