<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\SupportConsole\Contracts\SupportService;
use ChannelEngine\Infrastructure\ServiceRegister;

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
	 * @return SupportService
	 */
	protected function get_support_service() {
		if ( null === $this->support_service ) {
			$this->support_service = ServiceRegister::getService( SupportService::class );
		}

		return $this->support_service;
	}
}
