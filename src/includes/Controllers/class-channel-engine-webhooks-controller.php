<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Webhooks\DTO\Webhook;
use ChannelEngine\BusinessLogic\Webhooks\Handlers\WebhooksHandler;
use ChannelEngine\Components\Services\Plugin_Status_Service;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\Exceptions\BaseException;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class Channel_Engine_Webhooks_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Webhooks_Controller extends Channel_Engine_Base_Controller {
	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Handles webhook.
	 */
	public function handle() {
		if (!$this->get_plugin_status_service()->is_enabled()) {
			return;
		}

		$tenant = $this->get_param( 'tenant' );
		$token  = $this->get_param( 'token' );
		$event  = $this->get_param( 'type' );
		$webhook = new Webhook( $tenant, $token, $event );

		$handler = new WebhooksHandler();
		try {
			$handler->handle( $webhook );
			$this->return_plain_text();
		} catch ( BaseException $e ) {
			Logger::logError($e->getMessage());
			$this->return_plain_text(json_encode(['Error' => $e->getMessage(), 400]));
		}
	}

	/**
	 * Sets response header content type to plain/text, echos supplied $data as a json string and terminates request.
	 *
	 * @param string $data
	 * @param int $status_code
	 */
	protected function return_plain_text($data = '', $status_code = 200) {
		status_header( $status_code );
		echo $data;
		die();
	}

	/**
	 * @return Plugin_Status_Service
	 */
	protected function get_plugin_status_service() {
		return ServiceRegister::getService(Plugin_Status_Service::class);
	}
}