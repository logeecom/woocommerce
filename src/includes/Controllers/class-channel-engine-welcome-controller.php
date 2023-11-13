<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Utility\Script_Loader;

/**
 * Class Channel_Engine_Welcome_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Welcome_Controller extends Channel_Engine_Frontend_Controller {

	/**
	 * Sets that onboarding process has started.
	 */
	public function start_onboarding() {
		$state_service = new State_Service();
		try {
			$state_service->set_onboarding_started( true );
			$this->return_json( array( 'status' => true ) );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError( $e->getMessage() );
			$this->return_json( array( 'status' => false ) );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function load_resources() {
		parent::load_resources();

		Script_Loader::load_js(
			array(
				'/js/Onboarding.js',
			)
		);
	}
}
