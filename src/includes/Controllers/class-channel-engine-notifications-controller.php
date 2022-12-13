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
		$offset                        = (int) $this->get_param( 'offset' ) ?: 0;
		$limit                         = (int) $this->get_param( 'limit' ) ?: 15;
		$notifications                 = $this->get_notification_service()->find( [ 'isRead' => false ], $offset, $limit );
		$formatted_notifications       = $this->format_notifications( $notifications );
		$number_of_notifications       = count( $formatted_notifications );
		$total_number_of_notifications = $this->get_notification_service()->countNotRead();

		$this->return_json( [
			'notifications'         => $formatted_notifications,
			'numberOfNotifications' => $offset + $number_of_notifications,
			'disableButton'         => $total_number_of_notifications - ( $offset + $number_of_notifications ) === 0,
		] );
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

		$details           = $this->get_details_service()->find( [ 'logId' => $log_id ], 0, 10 );
		$formatted_details = [];

		foreach ( $details as $detail ) {
			$formatted_details[] = [
				'message'    => vsprintf( __( $detail->getMessage(), 'channelengine-wc' ), $detail->getArguments() ),
				'identifier' => $detail->getArguments()[0],
			];
		}

		$number_of_details = $this->get_details_service()->count( [ 'logId' => $log_id ] );

		$this->return_json( [
			'details'         => $formatted_details,
			'numberOfDetails' => $number_of_details,
			'from'            => ( $number_of_details === 0 ) ? 0 : 1,
			'to'              => ( $number_of_details < 10 ) ? $number_of_details : 10,
			'numberOfPages'   => ceil( $number_of_details / 10 ),
			'currentPage'     => 1,
			'logId'           => $log_id,
			'pageSize'        => 10,
		] );
	}

	/**
	 * @param Notification[] $notifications
	 */
	protected function format_notifications( $notifications ) {
		$formatted_notifications = [];

		foreach ( $notifications as $notification ) {
			$log = $this->get_log_service()->find( [ 'id' => $notification->getTransactionLogId() ] )[0];

			$formatted_notification = [
				'logId'          => $notification->getTransactionLogId(),
				'notificationId' => $notification->getId(),
				'context'        => __( $notification->getNotificationContext(), 'channelengine-wc' ),
				'message'        => vsprintf( __( $notification->getMessage(), 'channelengine-wc' ), $notification->getArguments() ),
				'date'           => $log ? $log->getStartTime()->format( 'd/m/Y' ) : '',
			];

			$formatted_notifications[] = $formatted_notification;
		}

		return $formatted_notifications;
	}

	/**
	 * @return TransactionLogService
	 */
	protected function get_log_service() {
		if ( $this->log_service === null ) {
			$this->log_service = ServiceRegister::getService( TransactionLogService::class );
		}

		return $this->log_service;
	}

	/**
	 * @return NotificationService
	 */
	protected function get_notification_service() {
		if ( $this->notification_service === null ) {
			$this->notification_service = ServiceRegister::getService( NotificationService::class );
		}

		return $this->notification_service;
	}

	/**
	 * @return DetailsService
	 */
	protected function get_details_service() {
		if ( $this->details_service === null ) {
			$this->details_service = ServiceRegister::getService( DetailsService::class );
		}

		return $this->details_service;
	}
}
