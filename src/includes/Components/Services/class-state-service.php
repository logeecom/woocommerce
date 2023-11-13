<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\Infrastructure\Configuration\ConfigurationManager;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class State_Service
 *
 * @package ChannelEngine\Components\Services
 */
class State_Service {
	const WELCOME_STATE         = 'onboarding';
	const ACCOUNT_CONFIGURATION = 'account_configuration';
	const PRODUCT_CONFIGURATION = 'product_configuration';
	const ORDER_STATUS_MAPPING  = 'order_status_mapping';
	const ENABLE_AND_SYNC       = 'enable_and_sync';
	const DASHBOARD             = 'dashboard';
	const CONFIG                = 'config';
	const TRANSACTIONS          = 'transactions';

	/**
	 * Retrieves current plugin state.
	 *
	 * @return string
	 */
	public function get_current_state() {
		$page = self::DASHBOARD;

		if ( ! $this->is_account_configured() ) {
			$page = self::WELCOME_STATE;
		}

		if ( ! $this->is_account_configured() &&
			 $this->get_onboarding_started() ) {
			$page = self::ACCOUNT_CONFIGURATION;
		}

		if ( $this->is_account_configured() && ! $this->is_product_configured() ) {
			$page = self::PRODUCT_CONFIGURATION;
		}

		if ( $this->is_product_configured() && ! $this->is_order_configured() ) {
			$page = self::ORDER_STATUS_MAPPING;
		}

		if ( $this->is_order_configured() && ! $this->is_initial_sync_in_progress() && ! $this->is_onboarding_completed() ) {
			$page = self::ENABLE_AND_SYNC;
		}

		return $page;
	}

	/**
	 * Sets manualProgressSyncInProgress value.
	 *
	 * @param bool $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_manual_product_sync_in_progress( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'manualProductSyncInProgress', $value );
	}

	/**
	 * Retrieves manualProductSyncInProgress value.
	 *
	 * @return mixed
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_manual_product_sync_in_progress() {
		return ConfigurationManager::getInstance()->getConfigValue( 'manualProductSyncInProgress', false );
	}

	/**
	 * Sets manualOrderSyncInProgress value.
	 *
	 * @param bool $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_manual_order_sync_in_progress( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'manualOrderSyncInProgress', $value );
	}

	/**
	 * Retrieves manualOrderSyncInProgress value.
	 *
	 * @return mixed
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_manual_order_sync_in_progress() {
		return ConfigurationManager::getInstance()->getConfigValue( 'manualOrderSyncInProgress', false );
	}

	/**
	 * Sets onboardingStarted value.
	 *
	 * @param $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_onboarding_started( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'onboardingStarted', $value );
	}

	/**
	 * Retrieves onboardingStarted value.
	 *
	 * @return bool
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function get_onboarding_started() {
		return ConfigurationManager::getInstance()->getConfigValue( 'onboardingStarted', false );
	}

	/**
	 * Sets initialSyncInProgress flag.
	 *
	 * @param $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_initial_sync_in_progress( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'initialSyncInProgress', $value );
	}

	/**
	 * Checks if initial sync is In progress.
	 *
	 * @return bool
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_initial_sync_in_progress() {
		return ConfigurationManager::getInstance()->getConfigValue( 'initialSyncInProgress', false );
	}

	/**
	 * Sets accountConfigured flag.
	 *
	 * @param bool $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_account_configured( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'accountConfigured', $value );
	}

	/**
	 * Checks if account is configured.
	 *
	 * @return bool
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_account_configured() {
		return ConfigurationManager::getInstance()->getConfigValue( 'accountConfigured', false );
	}

	/**
	 * Sets orderConfigured flag.
	 *
	 * @param bool $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_order_configured( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'orderConfigured', $value );
	}

	/**
	 * Checks if order is configured.
	 *
	 * @return bool
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_order_configured() {
		return ConfigurationManager::getInstance()->getConfigValue( 'orderConfigured', false );
	}

	/**
	 * Sets productConfigured flag.
	 *
	 * @param bool $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_product_configured( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'productConfigured', $value );
	}

	/**
	 * Checks if product is configured.
	 *
	 * @return bool
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_product_configured() {
		return ConfigurationManager::getInstance()->getConfigValue( 'productConfigured', false );
	}

	/**
	 * Sets productSyncInProgress flag.
	 *
	 * @param bool $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_product_sync_in_progress( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'productSyncInProgress', $value );
	}

	/**
	 * Checks if product sync is In progress.
	 *
	 * @return bool
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_product_sync_in_progress() {
		return ConfigurationManager::getInstance()->getConfigValue( 'productSyncInProgress', false );
	}

	/**
	 * Sets orderSyncInProgress flag.
	 *
	 * @param bool $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_order_sync_in_progress( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'orderSyncInProgress', $value );
	}

	/**
	 * Checks if order sync is In progress.
	 *
	 * @return bool
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_order_sync_in_progress() {
		return ConfigurationManager::getInstance()->getConfigValue( 'orderSyncInProgress', false );
	}

	/**
	 * Checks if onboarding is completed.
	 *
	 * @return bool
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function is_onboarding_completed() {
		return ConfigurationManager::getInstance()->getConfigValue( 'onboardingCompleted', false );
	}

	/**
	 * Sets onboardingCompleted flag.
	 *
	 * @param bool $value
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function set_onboarding_completed( $value ) {
		ConfigurationManager::getInstance()->saveConfigValue( 'onboardingCompleted', $value );
	}
}
