<?php
/*
Plugin Name: WooCommerce ChannelEngine
Plugin URI: http://channelengine.net
Description: ChannelEngine plugin for WooCommerce
Version: 1.5.7
Author: ChannelEngine
Author URI: http://channelengine.net
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

//Autoload all channel engine classes
require plugin_dir_path( __FILE__ ) .  'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-channel-engine.php' ;

//Add channel engine settings button to the WooCommerce menu
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );
function add_action_links ( $links ) {
    $mylinks = array(
        '<a href="' . admin_url( 'admin.php?page=channel-engine-plugin' ) . '">Settings</a>',
    );
    return array_merge( $links, $mylinks );
}

/**
 * Begins execution of the plugin.
 */
function run_channel_engine() {

    $plugin = new Channel_Engine(__FILE__);
}
run_channel_engine();