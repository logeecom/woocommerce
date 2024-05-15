<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\Utility\Shop_Helper;

/**
 * Class Channel_Engine_Base_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Base_Controller {
	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = true;

	public function process( $action = '' ) {
		if ( $this->is_internal ) {
			$this->validate_internal_call();
		}

		if ( empty( $action ) ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$action = $this->get_param( 'action' );
		}

		if ( $action ) {
			if ( method_exists( $this, $action ) ) {
				$this->$action();
			} else {
				$this->return_json( array( 'error' => "Method $action does not exist!" ), 404 );
			}
		}
	}

	/**
	 * Validates if call made from plugin code is secure by checking session token.
	 * If call is not secure, returns 401 status and terminates request.
	 */
	protected function validate_internal_call() {
		$logged_user_id = get_current_user_id();
		if ( empty( $logged_user_id ) ) {
			status_header( 401 );
			nocache_headers();

			exit();
		}
	}

	/**
	 * Gets request parameter if exists. Otherwise, returns null.
	 *
	 * @param string $key Request parameter key.
	 *
	 * @return mixed
	 */
	protected function get_param( $key ) {
		if ( isset( $_REQUEST[ $key ] ) ) { //phpcs:ignore
			return sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); //phpcs:ignore
		}

		return null;
	}

	/**
	 * Sets response header content type to json, echos supplied $data as a json string and terminates request.
	 *
	 * @param array $data Array to be returned as a json response.
	 * @param int   $status_code Response status code.
	 */
	protected function return_json( array $data, $status_code = 200 ) {
		wp_send_json( $data, $status_code );
	}

	/**
	 * Gets raw request.
	 *
	 * @return string
	 */
	protected function get_raw_input() {
		return file_get_contents( 'php://input' );
	}

	/**
	 * Returns 404 response and terminates request.
	 */
	protected function redirect404() {
		status_header( 404 );
		nocache_headers();

		require get_404_template();

		exit();
	}

	protected function redirect401() {
		status_header( 401 );
		nocache_headers();

		exit();
	}

	/**
	 * Validates if plugin is enabled and if it is post request.
	 *
	 * @param bool $only_admin Only admin should have access.
	 */
	protected function validate( $only_admin = false ) {
		if ( ! Shop_Helper::is_plugin_enabled() ) {
			exit();
		}

		if ( $only_admin && ! current_user_can( 'administrator' ) ) {
			$this->redirect401();
		}
	}

	/**
	 * Checks whether current request is POST.
	 *
	 * @return bool
	 */
	protected function is_post() {
		return isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'];
	}
}
