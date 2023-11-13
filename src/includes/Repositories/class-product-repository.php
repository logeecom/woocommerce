<?php

namespace ChannelEngine\Repositories;

use wpdb;

/**
 * Class Product_Repository
 *
 * @package ChannelEngine\Repositories
 */
class Product_Repository {

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * Product_Repository constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}


	public function get_ids( $limit = 0, $offset = 5000 ) {
		$sql = "SELECT ID as id FROM {$this->db->posts} 
				   inner join {$this->db->postmeta} as pm on ID=pm.post_id and pm.meta_key='_virtual' and pm.meta_value='no'
				   inner join {$this->db->postmeta} as pm2 on ID=pm2.post_id and pm2.meta_key='_downloadable' and pm2.meta_value='no' 
				   where post_type='product' and post_status='publish' limit %d offset %d";

		$sql    = $this->db->prepare( $sql, array( $limit, $offset ) );
		$result = $this->db->get_results( $sql, ARRAY_A );

		return ! empty( $result ) ? array_column( $result, 'id' ) : array();
	}

	public function get_count() {
		$sql = "SELECT count(*) FROM {$this->db->posts}
				where post_type like 'product%' and post_status='publish' 
				and ID not in (select distinct post_id from {$this->db->postmeta} where meta_value='yes' and (meta_key='_downloadable' or meta_key='_virtual'))
				and post_parent not in (select distinct post_id from {$this->db->postmeta} where meta_value='yes' and (meta_key='_downloadable' or meta_key='_virtual'))";

		return (int) $this->db->get_var( $sql );
	}
}
