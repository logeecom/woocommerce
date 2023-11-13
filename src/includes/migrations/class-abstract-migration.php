<?php


namespace ChannelEngine\Migrations;

use ChannelEngine\Migrations\Exceptions\Migration_Exception;
use wpdb;

/**
 * Class Abstract_Migration
 *
 * @package ChannelEngine\Migrations
 */
abstract class Abstract_Migration {

	/**
	 * @var wpdb
	 */
	protected $db;

	/**
	 * Abstract_Migration constructor.
	 *
	 * @param wpdb $db
	 */
	public function __construct( $db ) {
		$this->db = $db;
	}

	/**
	 * Executes migration.
	 *
	 * @throws Migration_Exception
	 */
	abstract public function execute();
}
