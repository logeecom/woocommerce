<?php

namespace ChannelEngine\Repositories;

use WC_Product_Attribute;

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

	/**
	 * Returns all available product attributes for mapping.
	 *
	 * @return array
	 */
	public function get_product_attributes() {
		global $wpdb;
		$globalAttributes = wc_get_attribute_taxonomies();
		$attributes = [];

		foreach ($globalAttributes as $globalAttribute) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_name($globalAttribute->attribute_name);
			$attribute->set_position( 0 );
			$attribute->set_visible( 1 );
			$attribute->set_variation( 0 );
			$attribute->set_id( 0 );
			$attributes[$globalAttribute->attribute_id] = $attribute;
		}
		$meta_data = $wpdb->get_results( "SELECT * FROM wp_postmeta WHERE meta_key = '_product_attributes'", ARRAY_A );
		foreach ($meta_data as $metaItem) {
			$meta_attributes = maybe_unserialize( $metaItem['meta_value'] );
			foreach ( $meta_attributes as $meta_attribute_value ) {
				$meta_value = array_merge(
					array(
						'name'         => '',
						'value'        => '',
						'position'     => 0,
						'is_visible'   => 0,
						'is_variation' => 0,
						'is_taxonomy'  => 0,
					),
					(array) $meta_attribute_value
				);

				// Check if is a taxonomy attribute.
				if ( ! empty( $meta_value['is_taxonomy'] ) ) {
					if ( ! taxonomy_exists( $meta_value['name'] ) ) {
						continue;
					}
					$id = wc_attribute_taxonomy_id_by_name( $meta_value['name'] );
				} else {
					$id = 0;
				}

				if ( array_key_exists( $id, $attributes ) ) {
					$attributes[$id]->set_position( $meta_value['position'] );
					$attributes[$id]->set_visible( $meta_value['is_visible'] );
					$attributes[$id]->set_variation( $meta_value['is_variation'] );
					$attributes[$id]->set_id( $id );
				} else {
					$attribute = new WC_Product_Attribute();
					$attribute->set_position( $meta_value['position'] );
					$attribute->set_visible( $meta_value['is_visible'] );
					$attribute->set_variation( $meta_value['is_variation'] );
					$attribute->set_id( $id );
					$attribute->set_name( $meta_value['name'] );
					$attributes[] = $attribute;
				}
			}
		}

		return $attributes;
	}
}
