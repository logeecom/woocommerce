<?php

namespace ChannelEngine\Controllers;

use ChannelEngine\BusinessLogic\Notifications\Contracts\NotificationService;
use ChannelEngine\BusinessLogic\Notifications\Entities\Notification;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\DetailsService;
use ChannelEngine\BusinessLogic\TransactionLog\Contracts\TransactionLogService;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class Channel_Engine_Notifications_Controller
 *
 * @package ChannelEngine\Controllers
 */
class Channel_Engine_Notifications_Controller extends Channel_Engine_Frontend_Controller {
	/**
	 * @var NotificationService
	 */
	protected $notification_service;
	/**
	 * @var TransactionLogService
	 */
	protected $log_service;
	/**
	 * @var DetailsService
	 */
	protected $details_service;

	/**
	 * Retrieves notifications.
	 */
	public function get() {
		$offset                        = (int) $this->get_param( 'offset' ) ? (int) $this->get_param( 'offset' ) : 0;
		$limit                         = (int) $this->get_param( 'limit' ) ? (int) $this->get_param( 'limit' ) : 15;
		$notifications                 = $this->get_notification_service()->find( array( 'isRead' => false ), $offset, $limit );
		$formatted_notifications       = $this->format_notifications( $notifications );
		$number_of_notifications       = count( $formatted_notifications );
		$total_number_of_notifications = $this->get_notification_service()->countNotRead();

		$this->return_json(
			array(
				'notifications'         => $formatted_notifications,
				'numberOfNotifications' => $offset + $number_of_notifications,
				'disableButton'         => $total_number_of_notifications - ( $offset + $number_of_notifications ) === 0,
			)
		);
	}

	/**
	 * Retrieves detail for notification.
	 */
	public function show_details() {
		$notification_id = (int) $this->get_param( 'notificationId' );
		$log_id          = (int) $this->get_param( 'logId' );

		$notification = $this->get_notification_service()->get( $notification_id );

		if ( $notification ) {
			$this->get_notification_service()->delete( $notification );
		}

		$details           = $this->get_details_service()->find( array( 'logId' => $log_id ), 0, 10 );
		$formatted_details = array();

		foreach ( $details as $detail ) {
			$formatted_details[] = array(
				'message'    => vsprintf( $detail->getMessage(), $detail->getArguments() ),
				'identifier' => $detail->getArguments()[0],
			);
		}

		$number_of_details = $this->get_details_service()->count( array( 'logId' => $log_id ) );

		$this->return_json(
			array(
				'details'         => $formatted_details,
				'numberOfDetails' => $number_of_details,
				'from'            => ( 0 === $number_of_details ) ? 0 : 1,
				'to'              => ( $number_of_details < 10 ) ? $number_of_details : 10,
				'numberOfPages'   => ceil( $number_of_details / 10 ),
				'currentPage'     => 1,
				'logId'           => $log_id,
				'pageSize'        => 10,
			)
		);
	}

	/**
	 * @param Notification[] $notifications
	 */
	protected function format_notifications( $notifications ) {
		$formatted_notifications = array();

		foreach ( $notifications as $notification ) {
			$log = $this->get_log_service()->find( array( 'id' => $notification->getTransactionLogId() ) )[0];

			$formatted_notification = array(
				'logId'          => $notification->getTransactionLogId(),
				'notificationId' => $notification->getId(),
				'context'        => $notification->getNotificationContext(),
				'message'        => vsprintf( $notification->getMessage(), $notification->getArguments() ),
				'date'           => $log ? $log->getStartTime()->format( 'd/m/Y' ) : '',
			);

			$formatted_notifications[] = $formatted_notification;
		}

		return $formatted_notifications;
	}

	/**
	 * @return TransactionLogService
	 */
	protected function get_log_service() {
		if ( null === $this->log_service ) {
			$this->log_service = ServiceRegister::getService( TransactionLogService::class );
		}

		return $this->log_service;
	}

	/**
	 * @return NotificationService
	 */
	protected function get_notification_service() {
		if ( null === $this->notification_service ) {
			$this->notification_service = ServiceRegister::getService( NotificationService::class );
		}

		return $this->notification_service;
	}

	/**
	 * @return DetailsService
	 */
	protected function get_details_service() {
		if ( null === $this->details_service ) {
			$this->details_service = ServiceRegister::getService( DetailsService::class );
		}

		return $this->details_service;
	}
}
