<?php

namespace ChannelEngine\Utility;

use ChannelEngine\Infrastructure\Logger\Logger;

/**
 * Class Logging_Callable
 *
 * @package ChannelEngine\Utility
 */
class Logging_Callable {

	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * Logging_Callable constructor.
	 *
	 * @param callable $callback
	 */
	public function __construct( $callback ) {
		$this->callback = $callback;
	}

	public function __invoke() {
		$args = func_get_args();
		try {
			return call_user_func_array( $this->callback, $args );
		} catch ( \Exception $exception ) {
			Logger::logError( $exception->getMessage() );
			throw $exception;
		}
	}
}
