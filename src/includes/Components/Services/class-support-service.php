<?php

namespace ChannelEngine\Components\Services;


use ChannelEngine\BusinessLogic\SupportConsole\SupportService;
use ChannelEngine\BusinessLogic\Webhooks\Contracts\WebhooksService;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Repositories\Plugin_Options_Repository;
use ChannelEngine\Utility\Database;

/**
 * Class Support_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Support_Service extends SupportService {
	/**
	 * @inheritDoc
	 */
	protected function hardReset() {
		$this->get_webhook_service()->delete();
		$database = new Database( new Plugin_Options_Repository() );
		$database->remove_data();
	}

	/**
	 * @return WebhooksService
	 */
	protected function get_webhook_service() {
		return ServiceRegister::getService(WebhooksService::class);
	}
}