<?php

namespace ChannelEngine\Repositories;

use ChannelEngine\Infrastructure\ORM\Entity;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ORM\Interfaces\RepositoryInterface;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryCondition;
use ChannelEngine\Infrastructure\ORM\QueryFilter\QueryFilter;
use ChannelEngine\Infrastructure\ORM\Utility\IndexHelper;
use wpdb;

/**
 * Class Base_Repository
 *
 * @package ChannelEngine\Repositories
 */
class Base_Repository implements RepositoryInterface {
	/**
	 * Fully qualified name of this class.
	 */
	const THIS_CLASS_NAME = __CLASS__;
	const TABLE_NAME = 'channel_engine_entity';

	/**
	 * @var wpdb
	 */
	protected $db;
	/**
	 * @var string
	 */
	protected $table_name;
	/**
	 * @var string
	 */
	protected $entity_class;

	/**
	 * Base_Repository constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = $this->db->prefix . static::TABLE_NAME;
	}

	/**
	 * @inheritDoc
	 */
	public static function getClassName() {
		return static::THIS_CLASS_NAME;
	}

	/**
	 * @inheritDoc
	 */
	public function setEntityClass( $entityClass ) {
		$this->entity_class = $entityClass;
	}

	/**
	 * @inheritDoc
	 */
	public function select( QueryFilter $filter = null ) {
		/** @var Entity $entity */
		$entity = new $this->entity_class();
		$type   = $entity->getConfig()->getType();

		/** @noinspection SqlNoDataSourceInspection */
		$query = "SELECT * FROM {$this->table_name} WHERE type = %s";
		if ( $filter ) {
			$query .= $this->apply_query_filter( $filter, IndexHelper::mapFieldsToIndexes( $entity ) );
		}
		$sql         = $this->db->prepare( $query, $type );
		$raw_results = $this->db->get_results( $sql, ARRAY_A );

		return $this->transform_to_entities( $raw_results );
	}

	/**
	 * @inheritDoc
	 */
	public function selectOne( QueryFilter $filter = null ) {
		if ( ! $filter ) {
			$filter = new QueryFilter();
		}

		$filter->setLimit( 1 );
		$results = $this->select( $filter );

		return ! empty( $results ) ? $results[0] : null;
	}

	/**
	 * @inheritDoc
	 */
	public function save( Entity $entity ) {
		if ( $entity->getId() ) {
			$this->update( $entity );

			return $entity->getId();
		}

		return $this->save_entity_to_storage( $entity );
	}

	/**
	 * @inheritDoc
	 */
	public function update( Entity $entity ) {
		$item = $this->prepare_entity_for_storage( $entity );

		// Only one record should be updated.
		return 1 === $this->db->update( $this->table_name, $item, array( 'id' => $entity->getId() ) );
	}

	/**
	 * @inheritDoc
	 */
	public function delete( Entity $entity ) {
		return false !== $this->db->delete( $this->table_name, array( 'id' => $entity->getId() ) );
	}

	/**
	 * @inheritDoc
	 */
	public function deleteWhere( QueryFilter $filter ) {
		/** @var Entity $entity */
		$entity = new $this->entity_class();
		$type   = $entity->getConfig()->getType();

		/** @noinspection SqlDialectInspection */
		/** @noinspection SqlNoDataSourceInspection */
		$query = "DELETE FROM {$this->table_name} WHERE type = %s ";
		if ( $filter ) {
			$query .= $this->get_condition( $filter, IndexHelper::mapFieldsToIndexes( $entity ) );
		}

		$sql = $this->db->prepare( $query, array( $type ) );
		$this->db->query( $sql );
	}


	/**
	 * @inheritDoc
	 */
	public function count( QueryFilter $filter = null ) {
		/** @var Entity $entity */
		$entity = new $this->entity_class();
		$type   = $entity->getConfig()->getType();

		/** @noinspection SqlDialectInspection */
		/** @noinspection SqlNoDataSourceInspection */
		$query = "SELECT COUNT(*) as `total` FROM {$this->table_name} WHERE type = %s ";
		if ( $filter ) {
			$query .= $this->apply_query_filter( $filter, IndexHelper::mapFieldsToIndexes( $entity ) );
		}

		$sql    = $this->db->prepare( $query, array( $type ) );
		$result = $this->db->get_results( $sql, ARRAY_A );

		return empty( $result ) ? 0 : (int) $result[0]['total'];
	}

	/**
	 * Saves entity to system storage.
	 *
	 * @param Entity $entity Entity to be stored.
	 *
	 * @return int Inserted entity identifier.
	 */
	protected function save_entity_to_storage( Entity $entity ) {
		$storage_item = $this->prepare_entity_for_storage( $entity );

		$this->db->insert( $this->table_name, $storage_item );

		$insert_id = (int) $this->db->insert_id;
		$entity->setId( $insert_id );

		return $insert_id;
	}

	/**
	 * Prepares entity in format for storage.
	 *
	 * @param Entity $entity Entity to be stored.
	 *
	 * @return array Item prepared for storage.
	 */
	protected function prepare_entity_for_storage( Entity $entity ) {
		$indexes      = IndexHelper::transformFieldsToIndexes( $entity );
		$storage_item = array(
			'type' => $entity->getConfig()->getType(),
			'data' => wp_json_encode( $entity->toArray() ),
		);

		foreach ( $indexes as $index => $value ) {
			$storage_item[ 'index_' . $index ] = $value;
		}

		return $storage_item;
	}

	/**
	 * Builds query filter part of the query.
	 *
	 * @param QueryFilter $filter Query filter object.
	 * @param array $field_index_map Property to index number map.
	 *
	 * @return string Query filter addendum.
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 */
	protected function apply_query_filter( QueryFilter $filter, array $field_index_map = array() ) {
		$query = $this->get_condition( $filter, $field_index_map );

		if ( $filter->getOrderByColumn() ) {
			$this->validate_index_column( $filter->getOrderByColumn(), $field_index_map );
			$order_index = 'id' === $filter->getOrderByColumn() ? 'id' : 'index_' . $field_index_map[ $filter->getOrderByColumn() ];
			$query       .= " ORDER BY {$order_index} {$filter->getOrderDirection()}";
		}

		if ( $filter->getLimit() ) {
			$offset = (int) $filter->getOffset();
			$query  .= " LIMIT {$offset}, {$filter->getLimit()}";
		}

		return $query;
	}

	protected function convert_value( QueryCondition $condition ) {
		$value = IndexHelper::castFieldValue( $condition->getValue(), $condition->getValueType() );
		switch ( $condition->getValueType() ) {
			case 'integer':
			case 'double':
				$value = $condition->getValue();
				break;
			case 'dateTime':
			case 'boolean':
				$value = $this->escape_value( $value );
				break;
			case 'string':
				$value = $this->escape_value( $condition->getValue() );
				break;
			case 'array':
				$values         = $condition->getValue();
				$escaped_values = array();
				foreach ( $values as $value ) {
					$escaped_values[] = is_string( $value ) ? $this->escape_value( $value ) : $value;
				}

				$value = '(' . implode( ', ', $escaped_values ) . ')';
				break;
		}

		return $value;
	}

	/**
	 * Checks if value exists and escapes it if it's not.
	 *
	 * @param mixed $value Value to be escaped.
	 *
	 * @return string Escaped value.
	 */
	protected function escape_value( $value ) {
		return null === $value ? 'NULL' : "'" . $this->escape( $value ) . "'";
	}

	/**
	 * Escapes provided value.
	 *
	 * @param mixed $value Value to be escaped.
	 *
	 * @return string Escaped value.
	 */
	protected function escape( $value ) {
		return addslashes( $value );
	}

	/**
	 * Validates if column can be filtered or sorted by.
	 *
	 * @param string $column Column name.
	 * @param array $index_map Index map.
	 *
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 */
	protected function validate_index_column( $column, array $index_map ) {
		if ( 'id' !== $column && ! array_key_exists( $column, $index_map ) ) {
			throw new QueryFilterInvalidParamException( esc_html__( 'Column is not id or index.', 'channelengine-integration' ) );
		}
	}

	/**
	 * Transforms raw database query rows to entities.
	 *
	 * @param array $result Raw database query result.
	 *
	 * @return Entity[] Array of transformed entities.
	 */
	protected function transform_to_entities( array $result ) {
		/** @var Entity[] $entities */
		$entities = array();
		foreach ( $result as $item ) {
			/** @var Entity $data */
			$data   = json_decode( $item['data'], true );
			$entity = isset( $data['class_name'] ) ? new $data['class_name']() : new $this->entity_class();
			$entity->inflate( $data );
			$entity->setId( (int) $item['id'] );

			$entities[] = $entity;
		}

		return $entities;
	}

	/**
	 * @param QueryFilter $filter
	 * @param array $field_index_map
	 *
	 * @return string
	 * @throws QueryFilterInvalidParamException
	 */
	protected function get_condition( QueryFilter $filter, array $field_index_map ) {
		$query      = '';
		$conditions = $filter->getConditions();
		if ( ! empty( $conditions ) ) {
			$query .= 'AND (';
			$first = true;
			foreach ( $conditions as $condition ) {
				$this->validate_index_column( $condition->getColumn(), $field_index_map );
				$chain_op = $first ? '' : $condition->getChainOperator();
				$first    = false;
				$column   = 'id' === $condition->getColumn() ? 'id' : 'index_' . $field_index_map[ $condition->getColumn() ];
				$operator = $condition->getOperator();
				$query    .= " $chain_op $column $operator " . $this->convert_value( $condition );
			}

			$query .= ')';
		}

		return $query;
	}
}
