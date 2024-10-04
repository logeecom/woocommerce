<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Authorization\Contracts\AuthorizationService;
use ChannelEngine\BusinessLogic\Authorization\DTO\AuthInfo;
use ChannelEngine\BusinessLogic\Authorization\Exceptions\CurrencyMismatchException;
use ChannelEngine\BusinessLogic\Webhooks\Contracts\WebhooksService;
use ChannelEngine\Components\Exceptions\Webhook_Creation_Failed_Exception;
use ChannelEngine\Components\Services\State_Service;
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
		$api_key      = sanitize_text_field( $post['apiKey'] );
		$account_name = sanitize_text_field( $post['accountName'] );

		if ( empty( $api_key ) || empty( $account_name ) ) {
			$this->return_error( __( 'API key and Account name fields are required.', 'channelengine-integration' ) );
		}

		try {
			$this->get_auth_service()->validateAccountInfo( $api_key, $account_name, get_woocommerce_currency() );
			$auth_info = AuthInfo::fromArray(
				array(
					'account_name' => $account_name,
					'api_key'      => $api_key,
				)
			);
			$this->get_auth_service()->setAuthInfo( $auth_info );
			$this->register_webhooks();
			$this->get_state_service()->set_account_configured( true );
			$this->return_json( array( 'success' => true ) );
		} catch ( CurrencyMismatchException|Webhook_Creation_Failed_Exception $e ) {
			$this->return_error( sprintf( '%s', esc_html( $e->getMessage() ) ) );
		} catch ( Exception $e ) {
			$this->return_error( __( 'Invalid API key or Account name.', 'channelengine-integration' ) );
		}
	}

	/**
	 * Registers webhooks.
	 *
	 * @throws Webhook_Creation_Failed_Exception
	 */
	protected function register_webhooks() {
		try {
			$this->get_webhooks_service()->createWebhookToken();
			$this->get_webhooks_service()->createWebhookUniqueId();
			$this->get_webhooks_service()->create();
		} catch ( Exception $e ) {
			throw new Webhook_Creation_Failed_Exception( 'Failed to create webhook.' );
		}
	}

	protected function load_resources() {
		parent::load_resources();

		Script_Loader::load_js(
			array(
				'/js/OnboardingAuth.js',
			)
		);
	}

	/**
	 * Retrieves instance of AuthorizationService.
	 *
	 * @return AuthorizationService
	 */
	protected function get_auth_service() {
		if ( null === $this->auth_service ) {
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
		return ServiceRegister::getService( WebhooksService::class );
	}
}
