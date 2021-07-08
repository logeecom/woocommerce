<?php

namespace ChannelEngine\Migrations\Scripts;

use ChannelEngine\BusinessLogic\Authorization\Contracts\AuthorizationService;
use ChannelEngine\BusinessLogic\Authorization\DTO\AuthInfo;
use ChannelEngine\Components\Bootstrap_Component;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Migrations\Abstract_Migration;
use ChannelEngine\Migrations\Schema\Buffer_Schema_Provider;
use ChannelEngine\Migrations\Schema\Channel_Engine_Entity_Schema_Provider;
use ChannelEngine\Migrations\Schema\Log_Schema_Provider;
use ChannelEngine\Migrations\Schema\Queue_Schema_Provider;
use WC_Product;
use WC_Product_Attribute;

/**
 * Class Migration_3_0_0
 *
 * @package ChannelEngine\Migrations\Scripts
 */
class Migration_3_0_0 extends Abstract_Migration {

	/**
	 * @inheritDoc
	 */
	public function execute() {
		Bootstrap_Component::init();

		$this->create_channel_engine_entity_table();
		$this->create_queue_item_table();
		$this->create_buffer_table();
		$this->create_log_table();
		$this->migrate_channel_engine_user_data();
		$this->migrate_channel_engine_attributes();
	}

	/**
	 * Creates channel engine entity table.
	 */
	private function create_channel_engine_entity_table() {
		$table_name = $this->db->prefix . 'channel_engine_entity';
		$query      = Channel_Engine_Entity_Schema_Provider::get_schema( $table_name );

		$this->db->query( $query );
	}

	/**
	 * Creates queue item table.
	 */
	private function create_queue_item_table() {
		$table_name = $this->db->prefix . 'channel_engine_queue';
		$sql        = Queue_Schema_Provider::get_schema( $table_name );

		$this->db->query( $sql );
	}

	private function create_buffer_table() {
		$table_name = $this->db->prefix . 'channel_engine_events';
		$sql        = Buffer_Schema_Provider::get_schema( $table_name );

		$this->db->query( $sql );
	}

	private function create_log_table() {
		$table_name = $this->db->prefix . 'channel_engine_logs';
		$sql        = Log_Schema_Provider::get_schema( $table_name );

		$this->db->query( $sql );
	}

	/**
	 * Migrates ChannelEngine user data.
	 */
	private function migrate_channel_engine_user_data() {
		$api_key      = get_option( '_channel_engine_api_key' );
		$account_name = get_option( '_channel_engine_account_name' );

		if ( ! $api_key || ! $account_name ) {
			return;
		}

		/** @var AuthorizationService $auth_service */
		$auth_service = ServiceRegister::getService( AuthorizationService::CLASS_NAME );
		$auth_info    = new AuthInfo( $account_name, $api_key );
		$auth_service->setAuthInfo( $auth_info );

		delete_option( '_channel_engine_api_key' );
		delete_option( '_channel_engine_account_name' );

		$state_service = new State_Service();
		$state_service->set_onboarding_started( true );
		$state_service->set_account_configured( true );
	}

	/**
	 * Moves ChannelEngine specific product attributes to custom product attributes.
	 */
	private function migrate_channel_engine_attributes() {
		$channel_engine_attributes = $this->get_channel_engine_attributes();
		$product_ids               = $this->get_product_ids( $channel_engine_attributes );
		$product_ids_batch         = $this->get_batch_of_product_ids( $product_ids );

		while ( ! empty( $product_ids_batch ) ) {
			$products = wc_get_products( [ 'include' => $product_ids_batch ] );

			/** @var WC_Product $product */
			foreach ( $products as $product ) {
				$ce_attributes      = $this->get_ce_attributes_with_product_id( $channel_engine_attributes, $product->get_id() );
				$product_attributes = $product->get_attributes();

				foreach ( $ce_attributes as $attribute ) {
					$product_attributes[] = $this->prepare_meta_values( $attribute );
				}

				$product->set_attributes( $product_attributes );

				$product->save();
			}

			$this->delete_channel_engine_attributes( $product_ids_batch );
			$this->unset_updated_product_ids( $product_ids, $product_ids_batch );
			$product_ids_batch = $this->get_batch_of_product_ids( $product_ids );
		}
	}

	/**
	 * Gets channel engine attributes.
	 *
	 * @return array|object|null
	 *
	 * @noinspection SqlNoDataSourceInspection
	 * @noinspection SqlDialectInspection
	 */
	private function get_channel_engine_attributes() {
		$query = "SELECT post_id, meta_value, meta_key FROM " .
		         $this->db->postmeta . " WHERE meta_key LIKE '_channel_engine%'";

		return $this->db->get_results( $this->db->prepare( $query, [] ), ARRAY_A );
	}

	/**
	 * Gets product ids.
	 *
	 * @param $channel_engine_attributes
	 *
	 * @return array
	 */
	private function get_product_ids( &$channel_engine_attributes ) {
		$product_ids = [];

		foreach ( $channel_engine_attributes as $attribute ) {
			$product_ids[] = $attribute['post_id'];
		}

		return array_unique( $product_ids );
	}

	/**
	 * Prepares meta values.
	 *
	 * @param $attribute
	 *
	 * @return WC_Product_Attribute
	 */
	private function prepare_meta_values( $attribute ) {
		$wc_attribute = new WC_Product_Attribute();

		$wc_attribute->set_name( $this->get_meta_name( $attribute ) );
		$wc_attribute->set_options( [ $attribute['meta_value'] ] );
		$wc_attribute->set_visible( 0 );
		$wc_attribute->set_variation( 0 );

		return $wc_attribute;
	}

	private function get_meta_name( $attribute ) {
		$name = ucfirst( str_replace( '_channel_engine_', '', $attribute['meta_key'] ) );

		if ( $name === 'Gtin' ) {
			$name = strtoupper( $name );
		}

		return $name;
	}

	/**
	 * Deletes channel engine attributes.
	 *
	 * @param $ids
	 */
	private function delete_channel_engine_attributes( $ids ) {
		$post_ids = implode( ', ', array_values( $ids ) );

		$query = "DELETE FROM " . $this->db->postmeta .
		         " WHERE meta_key LIKE '_channel_engine%' AND post_id IN ($post_ids)";

		$this->db->query( $query );
	}

	/**
	 * Retrieves batch of product ids for update.
	 *
	 * @param $product_ids
	 *
	 * @return array
	 */
	private function get_batch_of_product_ids( $product_ids ) {
		return array_slice( $product_ids, 0, 250, true );
	}

	/**
	 * Unsets updated product ids.
	 *
	 * @param $product_ids
	 * @param $batch
	 */
	private function unset_updated_product_ids( &$product_ids, $batch ) {
		foreach ( $batch as $key => $value ) {
			unset( $product_ids[ $key ] );
		}
	}

	/**
	 * Gets all channel engine attributes with given product id.
	 *
	 * @param $channel_engine_attributes
	 * @param $id
	 *
	 * @return array
	 */
	private function get_ce_attributes_with_product_id( &$channel_engine_attributes, $id ) {
		$result = [];

		foreach ( $channel_engine_attributes as $attribute ) {
			if ( $attribute['post_id'] === (string) $id ) {
				$result[] = $attribute;
			}
		}

		return $result;
	}
}
