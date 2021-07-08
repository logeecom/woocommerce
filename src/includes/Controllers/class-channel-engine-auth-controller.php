<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\API\Orders\Http\Proxy;
use ChannelEngine\BusinessLogic\Authorization\Contracts\AuthorizationService;
use ChannelEngine\BusinessLogic\Authorization\DTO\AuthInfo;
use ChannelEngine\BusinessLogic\Webhooks\Contracts\WebhooksService;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\Http\HttpClient;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Utility\Script_Loader;
use Exception;

/**
 * Class Channel_Engine_Auth_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Auth_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var AuthorizationService
	 */
	protected $auth_service;

	/**
	 * Performs authorization process.
	 */
	public function auth() {
		$post         = json_decode( $this->get_raw_input(), true );
		$api_key      = $post['apiKey'];
		$account_name = $post['accountName'];

		if ( empty( $api_key ) || empty( $account_name ) ) {
			$this->return_error( __( 'API key and Account name fields are required.', 'channelengine' ) );
		}

		try {
			$this->get_auth_service()->validateAccountInfo($api_key, $account_name, get_woocommerce_currency());

			// @todo Delete when account endpoint is available
            $orderProxy = new Proxy(ServiceRegister::getService(HttpClient::class), $account_name, $api_key);
            $orderProxy->getNew();

			$auth_info = AuthInfo::fromArray( [ 'account_name' => $account_name, 'api_key' => $api_key ] );
			$this->get_auth_service()->setAuthInfo( $auth_info );
			$this->get_state_service()->set_account_configured( true );
			$this->register_webhooks();
			$this->return_json( [ 'success' => true ] );
		} catch ( Exception $e ) {
			$this->return_error( __( 'Invalid API key or Account name.', 'channelengine' ) );
		}
	}

	/**
	 * Registers webhooks.
	 */
	protected function register_webhooks() {
		$this->get_webhooks_service()->createWebhookToken();
		$this->get_webhooks_service()->createWebhookUniqueId();
		$this->get_webhooks_service()->create();
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

	protected function load_resources() {
		parent::load_resources();

		Script_Loader::load_js( [
			'/js/OnboardingAuth.js',
		] );
	}

	/**
	 * Retrieves instance of AuthorizationService.
	 *
	 * @return AuthorizationService
	 */
	protected function get_auth_service() {
		if ( $this->auth_service === null ) {
			$this->auth_service = ServiceRegister::getService( AuthorizationService::class );
		}

		return $this->auth_service;
	}

	/**
	 * @return State_Service
	 */
	protected function get_state_service() {
		return new State_Service();
	}

	/**
	 * @return WebhooksService
	 */
	protected function get_webhooks_service() {
		return ServiceRegister::getService(WebhooksService::class);
	}
}