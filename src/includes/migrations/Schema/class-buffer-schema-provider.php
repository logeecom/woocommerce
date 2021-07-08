<?php

namespace ChannelEngine\Migrations\Schema;

/**
 * Class Buffer_Schema_Provider\
 *
 * @package ChannelEngine\Migrations\Schema
 */
class Buffer_Schema_Provider {
	/**
	 * Provides ChannelEngine buffer schema.
	 *
	 * @param $table_name
	 *
	 * @return string
	 */
	public static function get_schema( $table_name ) {
		return "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                       `id` INT NOT NULL AUTO_INCREMENT,
                       `type` VARCHAR(191) NOT NULL,
                       `data` TEXT NOT NULL,
                       `index_1` VARCHAR(191),
                       `index_2` VARCHAR(191),
                       PRIMARY KEY (`id`),
                       INDEX `type_index_1` (`type`, `index_1`),
                       INDEX `type_index_2` (`type`, `index_2`)
                       )";
	}
}