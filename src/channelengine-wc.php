<?php
/*
Plugin Name: ChannelEngine Integration
Plugin URI: https://wordpress.org/plugins/channelengine-integration/
Description: ChannelEngine plugin for WooCommerce
Version: 3.8.23
Text Domain: channelengine-integration
Domain Path: /i18n/languages
Author: ChannelEngine
Author URI: http://channelengine.net
License: GPLv2
WC requires at least: 3.0.0
WC tested up to: 9.5.1
*/

use ChannelEngine\ChannelEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

ChannelEngine::init( __FILE__ );
