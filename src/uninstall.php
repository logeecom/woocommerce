<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

global $wpdb;

\ChannelEngine\Components\Bootstrap_Component::init();

$plugin = \ChannelEngine\ChannelEngine::init( __FILE__ );
$plugin->uninstall();