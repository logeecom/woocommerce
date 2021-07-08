<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\Infrastructure\Logger\Interfaces\LoggerAdapter;
use ChannelEngine\Infrastructure\Logger\LogData;
use ChannelEngine\Infrastructure\Logger\Logger;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\Singleton;
use ChannelEngine\Utility\Shop_Helper;
use WC_Logger;

/**
 * Class Logger_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Logger_Service extends Singleton implements LoggerAdapter {
	const LOG_DEF = "[%s][%d][%s] %s\n";
	const CONTEXT_DEF = "\tContext[%s]: %s\n";

	/**
	 * @var Logger_Service
	 */
	protected static $instance;

	/**
	 * @var WC_Logger
	 */
	private $wc_logger;

	/**
	 * Logger_Service constructor.
	 */
	protected function __construct() {
		parent::__construct();
		$this->wc_logger = new WC_Logger();
	}

	/**
	 * Log message in system
	 *
	 * @param LogData $data
	 */
	public function logMessage( LogData $data ) {
		/** @var Configuration_Service $configuration */
		$configuration = ServiceRegister::getService( Configuration_Service::CLASS_NAME );
		$min_log_level = $configuration->getMinLogLevel();
		$log_level     = $data->getLogLevel();
		if ( ! Shop_Helper::is_woocommerce_active() ) {
			return;
		}

		if ( $log_level > $min_log_level && ! $configuration->isDebugModeEnabled() ) {
			return;
		}

		switch ( $log_level ) {
			case Logger::ERROR:
				$this->wc_logger->error( $this->format_message( 'error', $data ) );

				break;
			case Logger::INFO:
				$this->wc_logger->info( $this->format_message( 'info', $data ) );

				break;
			case Logger::DEBUG:
				$this->wc_logger->debug( $this->format_message( 'debug', $data ) );

				break;
			default :
				$this->wc_logger->warning( $this->format_message( 'warning', $data ) );
				break;
		}
	}

	protected function format_message( $level, LogData $data ) {
		$message = sprintf( static::LOG_DEF, $level, $data->getTimestamp(), $data->getComponent(), $data->getMessage() );
		foreach ( $data->getContext() as $item ) {
			$message .= sprintf( static::CONTEXT_DEF, $item->getName(), $item->getValue() );
		}

		return $message;
	}
}
