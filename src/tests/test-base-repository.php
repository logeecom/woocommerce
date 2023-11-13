<?php

use ChannelEngine\Infrastructure\ORM\Entity;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryFilter;
use ChannelEngine\Repositories\Base_Repository;

/**
 * Class Base_Repository_Test
 */
class Base_Repository_Test extends Base_Repository {
	const THIS_CLASS_NAME = __CLASS__;
	const TABLE_NAME      = 'channel_engine_test_entity';
}
