<?php

namespace ChannelEngine\Migrations\Schema;

/**
 * Class Queue_Schema_Provider
 *
 * @package ChannelEngine\Migrations\Schema
 */
class Queue_Schema_Provider {
	/**
	 * Provides queue schema.
	 *
	 * @param $table_name
	 *
	 * @return string
	 */
	public static function get_schema( $table_name ) {

		return 'CREATE TABLE IF NOT EXISTS `' . $table_name . '` (
                       `id` INT NOT NULL AUTO_INCREMENT,
                       `type` VARCHAR(191) NOT NULL,
                       `index_1` VARCHAR(191),
                       `index_2` VARCHAR(191),
                       `index_3` VARCHAR(191),
                       `index_4` VARCHAR(191),
                       `index_5` VARCHAR(191),
                       `index_6` VARCHAR(191),
                       `index_7` VARCHAR(191),
                       `index_8` VARCHAR(191),
                       `index_9` VARCHAR(191),
                       `parent_id` INT,
                       `status` VARCHAR(32),
                       `context` VARCHAR(191),
                       `serialized_task` LONGTEXT,
                       `queue_name` VARCHAR(191),
                       `last_execution_progress` INT,
                       `progress_base_points` INT,
                       `retries` INT,
                       `failure_description` TEXT,
                       `create_time` INT,
                       `start_time` INT,
                       `earliest_start_time` INT,
                       `queue_time` INT,
                       `last_update_time` INT,
                       `priority` INT, 
                       PRIMARY KEY (`id`),
                       INDEX `type_index_1_idx` (`type`, `index_1`),
                       INDEX `type_index_2_idx` (`type`, `index_2`),
                       INDEX `type_index_3_idx` (`type`, `index_3`),
                       INDEX `type_index_4_idx` (`type`, `index_4`),
                       INDEX `type_index_5_idx` (`type`, `index_5`),
                       INDEX `type_index_6_idx` (`type`, `index_6`),
                       INDEX `type_index_7_idx` (`type`, `index_7`),
                       INDEX `type_index_8_idx` (`type`, `index_8`),
                       INDEX `type_index_9_idx` (`type`, `index_9`))';
	}
}
