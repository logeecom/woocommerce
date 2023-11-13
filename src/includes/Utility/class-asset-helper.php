<?php

namespace ChannelEngine\Utility;

/**
 * Class Asset_Helper
 *
 * @package ChannelEngine\Utility
 */
class Asset_Helper {

	/**
	 * Retrieves image url.
	 *
	 * @param $file
	 *
	 * @return string
	 */
	public static function get_image_url( $file ) {
		return plugin_dir_url( __DIR__ ) . '../resources/images/' . $file;
	}

	/**
	 * Retrieves css file url.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function get_css_url( $file ) {
		return plugin_dir_url( __DIR__ ) . '../resources/css/' . $file;
	}

	/**
	 * Retrieves js file url.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function get_js_url( $file ) {
		return plugin_dir_url( __DIR__ ) . '../resources/js/' . $file;
	}
}
