<?php

namespace ChannelEngine\Repositories;

/**
 * Class Product_Repository
 *
 * @package ChannelEngine\Repositories
 */
class Product_Repository {
	public function get_ids( $limit = 0, $offset = 5000 ) {
		global $wpdb;

		$sql = "SELECT ID as id FROM {$wpdb->posts} 
				   inner join {$wpdb->postmeta} as pm on ID=pm.post_id and pm.meta_key='_virtual' and pm.meta_value='no'
				   inner join {$wpdb->postmeta} as pm2 on ID=pm2.post_id and pm2.meta_key='_downloadable' and pm2.meta_value='no' 
				   where post_type='product' and post_status='publish' limit %d offset %d";

		$sql    = $wpdb->prepare( $sql, [ $limit, $offset ] );
		$result = $wpdb->get_results( $sql, ARRAY_A );

		return ! empty( $result ) ? array_column( $result, 'id' ) : [];
	}

	public function get_count() {
		global $wpdb;

		$sql = "SELECT count(*) FROM {$wpdb->posts}
				where post_type like 'product%' and post_status='publish' 
				and ID not in (select distinct post_id from {$wpdb->postmeta} where meta_value='yes' and (meta_key='_downloadable' or meta_key='_virtual'))
				and post_parent not in (select distinct post_id from {$wpdb->postmeta} where meta_value='yes' and (meta_key='_downloadable' or meta_key='_virtual'))";

		return (int) $wpdb->get_var($sql);
	}
}