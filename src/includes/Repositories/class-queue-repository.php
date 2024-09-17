<?php

namespace ChannelEngine\Repositories;

use ChannelEngine\Infrastructure\ORM\Entity;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\Interfaces\QueueItemRepository;
use ChannelEngine\Infrastructure\ORM\QueryFilter\Operators;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryFilter;
use ChannelEngine\Infrastructure\ORM\Utility\IndexHelper;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use ChannelEngine\Infrastructure\TaskExecution\Interfaces\Priority;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\Utility\TimeProvider;

class Queue_Repository extends Base_Repository implements QueueItemRepository {
	const TABLE_NAME = 'channel_engine_queue';

	/**
	 * Queue_Repository constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->table_name = $this->db->prefix . static::TABLE_NAME;
	}

	/**
	 * @inheritDoc
	 */
	public function findOldestQueuedItems( $priority, $limit = 10 ) {
		$names = $this->get_running_queue_names();
		$ids   = $this->get_ids_for_execution( $names, $limit, $priority );
		if ( empty( $ids ) ) {
			return array();
		}

		$ids_query = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
		$sql       = "SELECT *
	            FROM {$this->table_name}
	            WHERE id in ($ids_query)";

		$sql    = $this->db->prepare( $sql, $ids );
		$result = $this->db->get_results( $sql, ARRAY_A );

		return $this->transform_to_entities( $result );
	}

	/**
	 * @inheritDoc
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	public function saveWithCondition( QueueItem $queueItem, array $additionalWhere = array() ) {
		if ( $queueItem->getId() === null ) {
			return $this->save( $queueItem );
		}

		$filter = new QueryFilter();
		$filter->where( 'id', Operators::EQUALS, $queueItem->getId() );

		foreach ( $additionalWhere as $name => $value ) {
			if ( null === $value ) {
				$filter->where( $name, Operators::NULL );
			} else {
				$filter->where( $name, Operators::EQUALS, $value );
			}
		}

		if ( ( $this->count( $filter ) ) <= 0 ) {
			throw new QueueItemSaveException( esc_html("Can not update queue item with id {$queueItem->getId()}. Item not found." ) );
		}

		$this->update( $queueItem );

		return $queueItem->getId();
	}

	/**
	 * @inheritDoc
	 */
	public function batchStatusUpdate( array $ids, $status ) {
		if ( empty( $ids ) ) {
			return;
		}

		$index_columns     = $this->get_indexes_columns();
		$status_index      = $index_columns['status_index'];
		$last_update_index = $index_columns['last_update_index'];

		$current_time            = $this->get_time_provider()->getCurrentLocalTime();
		$current_timestamp       = $current_time->getTimestamp();
		$current_time_serialized = IndexHelper::castFieldValue( $current_time, 'dateTime' );

		$sql = "UPDATE $this->table_name SET `$status_index`=%s, `status`=%s, `$last_update_index`=%s, `last_update_time`=%d" .
			   ' WHERE `id` IN (' . implode( ', ', array_fill( 0, count( $ids ), '%d' ) ) . ')';

		$sql = $this->db->prepare(
			$sql,
			array_merge(
				array(
					$status,
					$status,
					$current_time_serialized,
					$current_timestamp,
				),
				$ids
			)
		);

		$this->db->query( $sql );
	}

	/**
	 * @inheritDoc
	 */
	protected function transform_to_entities( array $result ) {
		$entities = array();

		foreach ( $result as $entity ) {
			$item = new QueueItem();
			$item->setId( (int) $entity['id'] );
			$item->setParentId( ! empty( $entity['parent_id'] ) ? (int) $entity['parent_id'] : null );
			$item->setStatus( $entity['status'] );
			$item->setContext( $entity['context'] );
			$item->setSerializedTask( $entity['serialized_task'] );
			$item->setQueueName( $entity['queue_name'] );
			$item->setLastExecutionProgressBasePoints( ! empty( $entity['last_execution_progress'] ) ? (int) $entity['last_execution_progress'] : 0 );
			$item->setProgressBasePoints( ! empty( $entity['progress_base_points'] ) ? (int) $entity['progress_base_points'] : 0 );
			$item->setRetries( ! empty( $entity['retries'] ) ? (int) $entity['retries'] : 0 );
			$item->setFailureDescription( $entity['failure_description'] );
			$item->setCreateTimestamp( $entity['create_time'] );
			$item->setStartTimestamp( ( $entity['start_time'] ) );
			$item->setEarliestStartTimestamp( $entity['earliest_start_time'] );
			$item->setQueueTimestamp( $entity['queue_time'] );
			$item->setLastUpdateTimestamp( $entity['last_update_time'] );
			$item->setPriority( (int) $entity['priority'] );

			$entities[] = $item;
		}

		return $entities;
	}

	/**
	 * @inheritDoc
	 */
	protected function prepare_entity_for_storage( Entity $entity ) {
		/** @var QueueItem $item */
		$item = $entity;

		$storage_item = array(
			'type'                    => $item->getConfig()->getType(),
			'parent_id'               => $item->getParentId(),
			'status'                  => $item->getStatus(),
			'context'                 => $item->getContext(),
			'serialized_task'         => $item->getSerializedTask(),
			'queue_name'              => $item->getQueueName(),
			'last_execution_progress' => $item->getLastExecutionProgressBasePoints(),
			'progress_base_points'    => $item->getProgressBasePoints(),
			'retries'                 => $item->getRetries(),
			'failure_description'     => $item->getFailureDescription(),
			'create_time'             => $item->getCreateTimestamp(),
			'start_time'              => $item->getStartTimestamp(),
			'earliest_start_time'     => $item->getEarliestStartTimestamp(),
			'queue_time'              => $item->getQueueTimestamp(),
			'last_update_time'        => $item->getLastUpdateTimestamp(),
			'priority'                => $item->getPriority(),
		);

		$indexes = IndexHelper::transformFieldsToIndexes( $item );
		foreach ( $indexes as $index => $value ) {
			$storage_item[ 'index_' . $index ] = $value;
		}

		return $storage_item;
	}

	/**
	 * Provides index column names.
	 *
	 * @return string[]
	 */
	private function get_indexes_columns() {
		/**
		 * Entity object.
		 *
		 * @var Entity $entity
		 */
		$entity    = new $this->entity_class();
		$index_map = IndexHelper::mapFieldsToIndexes( $entity );

		return array(
			'status_index'      => 'index_' . $index_map['status'],
			'queue_index'       => 'index_' . $index_map['queueName'],
			'priority_index'    => 'index_' . $index_map['priority'],
			'last_update_index' => 'index_' . $index_map['lastUpdateTimestamp'],
		);
	}

	/**
	 * Provides time provider.
	 *
	 * @return TimeProvider
	 */
	private function get_time_provider() {
		return ServiceRegister::getService( TimeProvider::CLASS_NAME );
	}

	/**
	 * Provides list of running queues.
	 *
	 * @return array
	 */
	private function get_running_queue_names() {
		$index_columns = $this->get_indexes_columns();

		$queue_name_index = $index_columns['queue_index'];
		$status_index     = $index_columns['status_index'];

		$running_queues_query = "SELECT $queue_name_index as name FROM `$this->table_name` q2 WHERE q2.`$status_index` = '%s'";
		$sql                  = $this->db->prepare( $running_queues_query, array( QueueItem::IN_PROGRESS ) );

		$result = $this->db->get_results( $sql, ARRAY_A );

		return array_column( $result, 'name' );
	}

	/**
	 * Provides list of ids for execution.
	 *
	 * @param array $names
	 * @param int $limit
	 * @param int $priority
	 *
	 * @return array
	 */
	private function get_ids_for_execution( array $names, $limit = 10, $priority = Priority::NORMAL ) {
		$index_columns = $this->get_indexes_columns();

		$queue_name_index = $index_columns['queue_index'];
		$status_index     = $index_columns['status_index'];
		$priority_index   = $index_columns['priority_index'];

		$sql = "SELECT $queue_name_index, MIN(id) AS id
	                 FROM `$this->table_name` AS q
	                 WHERE q.`$priority_index` = %d AND q.`$status_index` = %s ";

		if ( ! empty( $names ) ) {
			$running_queues_query = implode( ', ', array_fill( 0, count( $names ), '%s' ) );

			$sql .= " AND q.`$queue_name_index` NOT IN ($running_queues_query) ";
		}

		$sql    .= " GROUP BY `$queue_name_index`";
		$sql    = $this->db->prepare( $sql, array_merge( array( $priority, QueueItem::QUEUED ), $names ) );
		$result = $this->db->get_results( $sql, ARRAY_A );
		$result = array_column( $result, 'id' );
		sort( $result );

		return array_slice( $result, 0, $limit );
	}
}
