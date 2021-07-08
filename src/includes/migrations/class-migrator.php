<?php

namespace ChannelEngine\Migrations;

use ChannelEngine\Migrations\Utility\Migration_Reader;
use wpdb;

/**
 * Class Migrator
 *
 * @package ChannelEngine\Migrations
 */
class Migrator {
    /**
     * @var wpdb
     */
    private $db;
    /**
     * @var string
     */
    private $version;

    /**
     * Migrator constructor.
     *
     * @param wpdb $db
     * @param string $version
     */
    public function __construct( $db, $version ) {
        $this->db      = $db;
        $this->version = $version;
    }

    /**
     * Executes migrations that have higher version then the provided version.
     *
     * @throws Exceptions\Migration_Exception
     */
    public function execute() {
        $migration_reader = new Migration_Reader( $this->get_migration_directory() , $this->version, $this->db);
        while ($migration_reader->has_next()) {
            if ($migration = $migration_reader->read_next()) {
                $migration->execute();
            }
        }
    }

    /**
     * Provides migration directory.
     *
     * @return string
     */
    protected function get_migration_directory() {
        return realpath( __DIR__ ) . '/Scripts/';
    }
}