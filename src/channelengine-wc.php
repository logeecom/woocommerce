<?php
/*
Plugin Name: ChannelEngine Integration
Plugin URI: https://wordpress.org/plugins/channelengine-wc/
Description: ChannelEngine plugin for WooCommerce
Version: 3.8.6
Text Domain: channelengine-wc
Domain Path: /i18n/languages
Author: ChannelEngine
Author URI: http://channelengine.net
*/

use ChannelEngine\ChannelEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

ChannelEngine::init( __FILE__ );
