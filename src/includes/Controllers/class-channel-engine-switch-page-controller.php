<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class Channel_Engine_Switch_Page_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Switch_Page_Controller extends Channel_Engine_Base_Controller {

	/**
	 * @var State_Service
	 */
	protected $state_service;

	/**
	 * Renders appropriate view.
	 */
	public function switch_page() {
		$input = json_decode( $this->get_raw_input(), true );
		$page  = $input['page'];
		if ( State_Service::PRODUCT_CONFIGURATION === $page ) {
			$this->get_state_service()->set_product_configured( false );
			$this->get_state_service()->set_order_configured( false );
		}

		if ( State_Service::ORDER_STATUS_MAPPING === $page ) {
			$this->get_state_service()->set_order_configured( false );
		}

		$this->return_json( array( 'success' => true ) );
	}

	/**
	 * @return State_Service
	 */
	protected function get_state_service() {
		if ( null === $this->state_service ) {
			$this->state_service = ServiceRegister::getService( State_Service::class );
		}

		return $this->state_service;
	}
}
