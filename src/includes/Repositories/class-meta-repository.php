<?php

namespace ChannelEngine\Repositories;

/**
 * Class Meta_Repository
 *
 * @package ChannelEngine\Repositories
 */
class Meta_Repository {
	/**
	 * Retrieves product meta attributes.
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function get_product_meta( array $ids ) {
		global $wpdb;

		if ( empty( $ids ) ) {
			return [];
		}

		$posts     = $wpdb->posts;
		$post_meta = $wpdb->postmeta;

		$query = $wpdb->prepare(
			"SELECT $post_meta.*, $posts.post_parent 
			FROM $post_meta
			INNER JOIN $posts ON $posts.id = $post_meta.post_id
			WHERE $posts.id IN (%s)
			AND (($posts.post_status = 'publish' 
			AND $posts.post_type IN ('product', 'product_variation')) OR 
             ($posts.post_status = 'inherit' 
			AND $posts.post_type IN ('attachment')))
			AND $post_meta.meta_key IN('_product_attributes', '_weight', '_length', '_height',
			                      '_width', '_sku', '_ean', '_thumbnail_id')",
			implode( ', ', $ids )
		);

		$lookup = [];

		$meta = $wpdb->get_results( $query, OBJECT );

		foreach ( $meta as $item ) {
			$post_id = $item->post_id;
			if ( ! isset( $lookup[ $post_id ] ) ) {
				$lookup[ $post_id ] = [];
			}
			$lookup[ $post_id ][ $item->meta_key ] = $item->meta_value;
		}

		return $lookup;
	}
}
