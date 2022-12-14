<?php

namespace ChannelEngine\Migrations\Scripts;

use ChannelEngine\Components\Bootstrap_Component;
use ChannelEngine\Migrations\Abstract_Migration;
use ChannelEngine\Migrations\Schema\Buffer_Schema_Provider;
use ChannelEngine\Migrations\Schema\Channel_Engine_Entity_Schema_Provider;
use ChannelEngine\Migrations\Schema\Log_Schema_Provider;
use ChannelEngine\Migrations\Schema\Queue_Schema_Provider;
use WC_Product;
use WC_Product_Attribute;

/**
 * Class Migration_1_0_0
 *
 * @package ChannelEngine\Migrations\Scripts
 */
class Migration_1_0_0 extends Abstract_Migration {

	/**
	 * @inheritDoc
	 */
	public function execute() {
		Bootstrap_Component::init();

		$this->create_channel_engine_entity_table();
		$this->create_queue_item_table();
		$this->create_buffer_table();
		$this->create_log_table();
	}

	/**
	 * Creates channel engine entity table.
	 */
	private function create_channel_engine_entity_table() {
		$table_name = $this->db->prefix . 'channel_engine_entity';
		$query      = Channel_Engine_Entity_Schema_Provider::get_schema( $table_name );

		$this->db->query( $query );
	}

	/**
	 * Creates queue item table.
	 */
	private function create_queue_item_table() {
		$table_name = $this->db->prefix . 'channel_engine_queue';
		$sql        = Queue_Schema_Provider::get_schema( $table_name );

		$this->db->query( $sql );
	}

    /**
     * Creates events table.
     */
	private function create_buffer_table() {
		$table_name = $this->db->prefix . 'channel_engine_events';
		$sql        = Buffer_Schema_Provider::get_schema( $table_name );

		$this->db->query( $sql );
	}

    /**
     * Creates logs table.
     */
	private function create_log_table() {
		$table_name = $this->db->prefix . 'channel_engine_logs';
		$sql        = Log_Schema_Provider::get_schema( $table_name );

		$this->db->query( $sql );
	}
}
