<?php

namespace ChannelEngine\Repositories;

/**
 * Class Plugin_Options_Repository
 *
 * @package ChannelEngine\Repositories
 */
class Plugin_Options_Repository {
	/**
	 * Provides current schema version.
	 *
	 * @NOTICE default version is 1.0.0 if version has not been previously set.
	 *
	 * @return string
	 */
	public function get_schema_version() {
		return get_option( 'CE_SCHEMA_VERSION', '0.0.1' );
	}

	/**
	 * Sets schema version.
	 *
	 * @param string $version
	 */
	public function set_schema_version( $version ) {
		update_option( 'CE_SCHEMA_VERSION', $version );
	}
}
