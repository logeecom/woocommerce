<?php

namespace ChannelEngine\Controllers;

/**
 * Class Channel_Engine_Index
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Index extends Channel_Engine_Base_Controller {

	public function index() {
		$controller_name = $this->get_param( 'channel_engine_controller' );

		$class_name = '\ChannelEngine\Controllers\Channel_Engine_' . $controller_name . '_Controller';
		if ( ! $this->validate_controller_name( $controller_name ) || ! class_exists( $class_name ) ) {
			status_header( 404 );
			nocache_headers();

			require get_404_template();

			exit();
		}

		/** @var Channel_Engine_Base_Controller $controller */
		$controller = new $class_name();
		$controller->process();
	}

	public function index_admin() {
		if ( ! in_array( $this->get_param( 'channel_engine_controller' ), array( 'Async_Process', 'Webhooks' ) ) ) {
			$this->validate( true );
		}

		$this->index();
	}

	/**
	 * Validates controller name by checking whether it exists in the list of known controller names.
	 *
	 * @param string $controller_name Controller name from request input.
	 *
	 * @return bool
	 */
	private function validate_controller_name( $controller_name ) {
		$allowed_controllers = array(
			'Async_Process',
			'Frontend',
			'Order_Overview',
			'Welcome',
			'Auth',
			'Product_Sync',
			'Order_Status',
			'Initial_Sync',
			'Check_Status',
			'Enable',
			'Dashboard',
			'Config',
			'Transactions',
			'Notifications',
			'Support',
			'Webhooks',
			'Switch_Page',
		);

		return in_array( $controller_name, $allowed_controllers, true );
	}
}
