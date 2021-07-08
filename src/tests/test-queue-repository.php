<?php

use ChannelEngine\Repositories\Queue_Repository;

class Test_Queue_Repository extends Queue_Repository {
	const TABLE_NAME = 'test_queue';
	const THIS_CLASS_NAME = __CLASS__;
}