<?php
/*
Plugin Name: ChannelEngine WooCommerce Integration
Plugin URI: http://channelengine.net
Description: ChannelEngine plugin for WooCommerce
Version: 3.4.0
Author: ChannelEngine
Author URI: http://channelengine.net
*/

use ChannelEngine\ChannelEngine;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

ChannelEngine::init(__FILE__);