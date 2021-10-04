<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\Components\Services\Plugin_Status_Service;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use ChannelEngine\Utility\Script_Loader;
use ChannelEngine\Utility\View;

/**
 * Class Channel_Engine_Frontend_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Frontend_Controller extends Channel_Engine_Base_Controller {
	/**
	 * @var State_Service
	 */
	protected $state_service;
	/**
	 * @var Plugin_Status_Service
	 */
	protected $plugin_status_service;
	/**
	 * @var string
	 */
	protected $page;
	/**
	 * @var TaskRunnerWakeup
	 */
	protected $task_runner_wakeup;
	/**
	 * Page data.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Renders appropriate view.
	 */
	public function render() {
		$this->get_task_runner_wakeup()->wakeup();
		$this->set_page();
		$controller_name = $this->get_controller_class();
		$controller      = new $controller_name();
		$this->data      = $controller->get_view_data();
		$controller->load_resources();

		echo View::file( '/' . $this->page . '.php' )->render( $this->data );
	}

	/**
	 * Sets current page code.
	 */
	protected function set_page() {
		$subpage    = $this->get_param( 'subpage' );
		$this->page = $this->get_state_service()->get_current_state();

		if ( $subpage && $this->page === State_Service::DASHBOARD
		     && $this->get_plugin_status_service()->is_enabled() ) {
			$this->page = $subpage;
		}
	}

	/**
	 * Loads CSS and JS for specific ChannelEngine pages.
	 */
	protected function load_resources() {
		Script_Loader::load_css( [ '/css/main.css' ] );
		Script_Loader::load_js( [
			'/channelengine/js/AjaxService.js',
			'/js/Main.js',
			'/js/Notifications.js',
			'/js/ProductService.js',
			'/js/OrderService.js',
			'/js/Loader.js',
		] );
	}

	/**
	 * Retrieves view data.
	 *
	 * @return array
	 */
	protected function get_view_data() {
		return [];
	}

	/**
	 * @return string
	 */
	protected function get_controller_class() {
		switch ( $this->page ) {
			case State_Service::WELCOME_STATE:
				return Channel_Engine_Welcome_Controller::class;
			case State_Service::ACCOUNT_CONFIGURATION:
				return Channel_Engine_Auth_Controller::class;
			case State_Service::ORDER_STATUS_MAPPING:
				return Channel_Engine_Order_Status_Controller::class;
			case State_Service::PRODUCT_CONFIGURATION:
				return Channel_Engine_Product_Sync_Controller::class;
			case State_Service::ENABLE_AND_SYNC:
				return Channel_Engine_Enable_Controller::class;
			case State_Service::DASHBOARD:
				return Channel_Engine_Dashboard_Controller::class;
			case State_Service::CONFIG:
				return Channel_Engine_Config_Controller::class;
			case State_Service::TRANSACTIONS:
				return Channel_Engine_Transactions_Controller::class;
		}
	}

	/**
	 * @return State_Service
	 */
	protected function get_state_service() {
		if ( $this->state_service === null ) {
			$this->state_service = ServiceRegister::getService( State_Service::class );
		}

		return $this->state_service;
	}

	/**
	 * @return Plugin_Status_Service
	 */
	protected function get_plugin_status_service() {
		if ( $this->plugin_status_service === null ) {
			$this->plugin_status_service = ServiceRegister::getService( Plugin_Status_Service::class );
		}

		return $this->plugin_status_service;
	}

	/**
	 * @return TaskRunnerWakeup
	 */
	protected function get_task_runner_wakeup() {
		if ( $this->task_runner_wakeup === null ) {
			$this->task_runner_wakeup = ServiceRegister::getService( TaskRunnerWakeup::class );
		}

		return $this->task_runner_wakeup;
	}

	/**
	 * Returns json response with error message.
	 *
	 * @param string $message
	 */
	protected function return_error( $message ) {
		$this->return_json(
			[
				'success' => false,
				'message' => $message,
			]
		);
	}
}
