<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\Infrastructure\AutoTest\AutoTestService;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use ChannelEngine\Utility\Shop_Helper;

/**
 * Class Channel_Engine_Async_Process_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Async_Process_Controller extends Channel_Engine_Base_Controller {

	/**
	 * Channel_Engine_Async_Process_Controller constructor.
	 */
	public function __construct() {
		$this->is_internal = false;
	}

	/**
	 * Runs process defined by guid request parameter.
	 */
	public function run() {
		if ( ! Shop_Helper::is_plugin_enabled() ) {
			$this->return_json(
				array(
					'success' => false,
					'error'   => 'Plugin not enabled',
				)
			);
		}

		$guid      = $this->get_param( 'guid' );
		$auto_test = $this->get_param( 'auto-test' );

		if ( $auto_test ) {
			$auto_test_service = new AutoTestService();
			$auto_test_service->setAutoTestMode();
			Logger::logInfo( 'Received auto-test async process request.', 'Integration', array( 'guid' => $guid ) );
		} else {
			Logger::logDebug( 'Received async process request.', 'Integration', array( 'guid' => $guid ) );
		}

		if ( 'auto-configure' !== $guid ) {
			/** @var AsyncProcessService $service */
			$service = ServiceRegister::getService( AsyncProcessService::CLASS_NAME );
			$service->runProcess( $this->get_param( 'guid' ) );
		}

		$this->return_json( array( 'success' => true ) );
	}
}
