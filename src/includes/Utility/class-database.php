<?php

namespace ChannelEngine\Utility;

use ChannelEngine\ChannelEngine;
use ChannelEngine\Migrations\Exceptions\Migration_Exception;
use ChannelEngine\Migrations\Migrator;
use ChannelEngine\Repositories\Base_Repository;
use ChannelEngine\Repositories\Log_Repository;
use ChannelEngine\Repositories\Plugin_Options_Repository;
use ChannelEngine\Repositories\Product_Event_Repository;
use ChannelEngine\Repositories\Queue_Repository;
use WP_Site;
use wpdb;

/**
 * Class Database
 *
 * @package ChannelEngine\Database
 */
class Database {
	/**
	 * @var Plugin_Options_Repository
	 */
	private $repository;
	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * Database constructor.
	 *
	 * @param Plugin_Options_Repository $repository
	 */
	public function __construct( Plugin_Options_Repository $repository ) {
		$this->repository = $repository;
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Performs database update.
	 *
	 * @param $is_multisite
	 *
	 * @throws Migration_Exception
	 */
	public function update( $is_multisite ) {
		if ( $is_multisite ) {
			$sites = get_sites();
			/** @var WP_Site $site */
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->do_update();
				restore_current_blog();
			}
		} else {
			$this->do_update();
		}
	}

	/**
	 * Executes uninstall script.
	 */
	public function uninstall() {
		$this->drop_table( Base_Repository::TABLE_NAME );
		$this->drop_table( Product_Event_Repository::TABLE_NAME );
		$this->drop_table( Queue_Repository::TABLE_NAME );
		$this->drop_table( Log_Repository::TABLE_NAME );
	}

	/**
	 * Removes all data from tables.
	 */
	public function remove_data() {
		$this->truncate( Base_Repository::TABLE_NAME );
		$this->truncate( Product_Event_Repository::TABLE_NAME );
		$this->truncate( Queue_Repository::TABLE_NAME );
		$this->truncate( Log_Repository::TABLE_NAME );
	}

	/**
	 * @param string $table_name
	 */
	private function truncate( $table_name ) {
		$query = 'TRUNCATE ' . $this->db->prefix . $table_name . ';';
		$this->db->query( $query );
	}

	/**
	 * @param string $table_name
	 */
	private function drop_table( $table_name ) {
		$query = 'DROP TABLE IF EXISTS ' . $this->db->prefix . $table_name;
		$this->db->query( $query );
	}

	/**
	 * Updates schema for current site.
	 *
	 * @throws Migration_Exception
	 */
	private function do_update() {
		$current_schema_version = $this->repository->get_schema_version();
		$current_plugin_version = ChannelEngine::VERSION;

		if ( $current_plugin_version === $current_schema_version ) {
			return;
		}

		$migrator = new Migrator( $this->db, $current_schema_version );
		$migrator->execute();
		$this->repository->set_schema_version( $current_plugin_version );
	}
}
