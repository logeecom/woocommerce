<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\TransactionLog\Contracts\DetailsService;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\BusinessLogic\TransactionLog\Entities\TransactionLog;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class Channel_Engine_Transactions_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Transactions_Controller extends Channel_Engine_Frontend_Controller {

	/**
	 * @var TransactionLogService
	 */
	protected $log_service;
	/**
	 * @var DetailsService
	 */
	protected $details_service;

	/**
	 * Retrieves data for transactions page.
	 */
	public function get() {
		$status    = (bool) $this->get_param( 'status' );
		$page      = (int) $this->get_param( 'page' ) ? (int) $this->get_param( 'page' ) : 1;
		$page_size = (int) $this->get_param( 'page_size' ) ? (int) $this->get_param( 'page_size' ) : 10;
		$task_type = $status ? '' : $this->get_param( 'task_type' );

		$logs           = $this->get_logs( $page, $page_size, $task_type, $status );
		$number_of_logs = $this->get_log_service()->count( $this->get_filters( $task_type, $status ) );

		$this->return_json(
			array(
				'logs'          => $this->format_logs( $logs ),
				'numberOfLogs'  => $number_of_logs,
				'from'          => ( 0 === $number_of_logs ) ? 0 : ( $page - 1 ) * $page_size + 1,
				'to'            => ( $number_of_logs < $page * $page_size ) ? $number_of_logs : $page * $page_size,
				'numberOfPages' => ceil( $number_of_logs / $page_size ),
				'currentPage'   => (int) $page,
				'taskType'      => $status ? 'Errors' : $task_type,
			)
		);
	}

	/**
	 * Retrieves details for log.
	 */
	public function get_details() {
		$log_id            = (int) $this->get_param( 'log_id' );
		$page              = (int) $this->get_param( 'page' ) ? (int) $this->get_param( 'page' ) : 1;
		$page_size         = $this->get_param( 'page_size' ) ? $this->get_param( 'page_size' ) : 10;
		$details           = $this->get_details_service()->find(
			array( 'logId' => $log_id ),
			( $page - 1 ) * $page_size,
			$page_size
		);
		$formatted_details = array();

		foreach ( $details as $detail ) {
			$formatted_details[] = array(
				'message'    => vsprintf( __( $detail->getMessage(), 'channelengine-wc' ), $detail->getArguments() ),
				'identifier' => $detail->getArguments()[0],
			);
		}

		$number_of_details = $this->get_details_service()->count( array( 'logId' => $log_id ) );

		$this->return_json(
			array(
				'details'         => $formatted_details,
				'numberOfDetails' => $number_of_details,
				'from'            => ( 0 === $number_of_details ) ? 0 : ( $page - 1 ) * $page_size + 1,
				'to'              => ( $number_of_details < $page * $page_size ) ? $number_of_details : $page * $page_size,
				'numberOfPages'   => ceil( $number_of_details / $page_size ),
				'currentPage'     => (int) $page,
				'logId'           => $log_id,
				'pageSize'        => $page_size,
			)
		);
	}

	/**
	 * @param $page
	 * @param $page_size
	 * @param string    $task_type
	 * @param string    $status
	 *
	 * @return TransactionLog[]
	 */
	protected function get_logs( $page, $page_size, $task_type, $status ) {
		return $this->get_log_service()->find(
			$this->get_filters( $task_type, $status ),
			( $page - 1 ) * $page_size,
			$page_size
		);
	}

	/**
	 * @param $task_type
	 * @param $status
	 *
	 * @return array
	 */
	protected function get_filters( $task_type, $status ) {
		$filters = array();

		if ( $task_type ) {
			$filters['taskType'] = $task_type;
		}

		if ( 'ProductSync' === $task_type ) {
			$filters['taskType'] = array( 'ProductSync', 'ProductsDeleteTask', 'ProductsUpsertTask' );
		}

		if ( $status ) {
			$filters['status'] = 'failed';
		}

		return $filters;
	}

	/**
	 * @param TransactionLog[] $logs
	 *
	 * @return array
	 */
	protected function format_logs( $logs ) {
		$formatted_logs = array();

		foreach ( $logs as $log ) {
			$detail = $this->get_details_service()->getForLog( $log->getId() );

			$formatted_log = array(
				'taskType'      => __( $log->getTaskType(), 'channelengine-wc' ),
				'status'        => __( $log->getStatus(), 'channelengine-wc' ),
				'startTime'     => '',
				'completedTime' => '',
				'id'            => $log->getId(),
				'hasDetails'    => array() !== $detail,
			);

			if ( $log->getStartTime() ) {
				$formatted_log['startTime'] = get_date_from_gmt(
					gmdate( 'Y-m-d H:i:s', $log->getStartTime()->getTimestamp() ),
					'd/m/Y H.i'
				);
			}

			if ( $log->getCompletedTime() ) {
				$formatted_log['completedTime'] = get_date_from_gmt(
					gmdate( 'Y-m-d H:i:s', $log->getCompletedTime()->getTimestamp() ),
					'd/m/Y H.i'
				);
			}

			$formatted_logs[] = $formatted_log;
		}

		return $formatted_logs;
	}

	/**
	 * Retrieves an instance of TransactionLogService.
	 *
	 * @return TransactionLogService
	 */
	protected function get_log_service() {
		if ( null === $this->log_service ) {
			$this->log_service = ServiceRegister::getService( TransactionLogService::class );
		}

		return $this->log_service;
	}

	/**
	 * Retrieves an instance of DetailsService.
	 *
	 * @return DetailsService
	 */
	protected function get_details_service() {
		if ( null === $this->details_service ) {
			$this->details_service = ServiceRegister::getService( DetailsService::class );
		}

		return $this->details_service;
	}
}
