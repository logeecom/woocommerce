<?php

namespace ChannelEngine\Repositories;

use ChannelEngine\BusinessLogic\Products\Entities\ProductEvent;
use ChannelEngine\BusinessLogic\Products\Repositories\ProductEventRepository;

/**
 * Class Product_Event_Repository
 *
 * @package ChannelEngine\Repositories
 */
class Product_Event_Repository extends Base_Repository implements ProductEventRepository {
	/**
	 * Fully qualified name of this class.
	 */
	const THIS_CLASS_NAME = __CLASS__;
	const TABLE_NAME      = 'channel_engine_events';

	/**
	 * Deletes multiple entities and returns success flag.
	 *
	 * @param ProductEvent[] $entities
	 *
	 * @return bool
	 */
	public function batchDelete( array $entities ) {
		$ids = array();

		foreach ( $entities as $entity ) {
			$ids[] = $entity->getId();
		}

		$format = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
		$query  = $this->db->prepare( "DELETE FROM $this->table_name WHERE ID IN ($format)", $ids );

		return $this->db->query( $query ) === count( $ids );
	}
}
