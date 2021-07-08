<?php

use ChannelEngine\Infrastructure\Serializer\Concrete\NativeSerializer;
use ChannelEngine\Infrastructure\Serializer\Serializer;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Migrations\Schema\Queue_Schema_Provider;
use ChannelEngine\Tests\Infrastructure\ORM\AbstractGenericQueueItemRepositoryTest;

class Queue_Repository_Test extends AbstractGenericQueueItemRepositoryTest {

	protected function setUp() {
		parent::setUp();

		ServiceRegister::registerService( Serializer::CLASS_NAME,
		function () {
			return new NativeSerializer();
		});

		$this->create_test_table();
	}

	public function getQueueItemEntityRepositoryClass() {
		return Test_Queue_Repository::getClassName();
	}

	public function cleanUpStorage() {
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Test_Queue_Repository::TABLE_NAME );
	}

	private function create_test_table() {
		global $wpdb;

		$sql = Queue_Schema_Provider::get_schema( $wpdb->prefix . Test_Queue_Repository::TABLE_NAME );
		$wpdb->query( $sql );
	}
}
