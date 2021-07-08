<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\API\Webhooks\Enums\EventTypes;
use ChannelEngine\BusinessLogic\Webhooks\WebhooksService;
use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Utility\Shop_Helper;

/**
 * Class Webhooks_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Webhooks_Service extends WebhooksService {

	/**
	 * Creates webhook unique id.
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function createWebhookUniqueId() {
		$id = substr( $this->getGuidProvider()->generateGuid(), 0, 8 );

		ConfigurationManager::getInstance()->saveConfigValue( 'CHANNELENGINE_WEBHOOK_ID', $id );
	}

	/**
	 * Retrieves webhook unique id.
	 *
	 * @return string
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function getWebhookUniqueId() {
		return ConfigurationManager::getInstance()->getConfigValue( 'CHANNELENGINE_WEBHOOK_ID', '' );
	}

	/**
	 * @inheritDoc
	 */
	protected function getEvents() {
		return [
			EventTypes::ORDERS_CHANGE,
		];
	}

	/**
	 * @inheritDoc
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	protected function getName() {
		return 'woocommerce_orders_' . $this->getWebhookUniqueId();
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrl() {
		return Shop_Helper::get_controller_url('Webhooks', 'handle');
	}
}