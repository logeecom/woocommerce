<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use WC_Product;

/**
 * Class Replace_Products_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Replace_Products_Service extends Products_Service
{
    /**
     * @inheritDoc
     */
    public function getProducts( array $ids ) {
        $this->category_trails = [];
        $this->images          = [];
        $args = [
            'return'         => 'objects',
            'posts_per_page' => - 1,
            'include'        => $ids,
        ];

        $wc_products = wc_get_products( $args );

        return $this->transform_products($wc_products, $ids);
    }

    /**
     * @param WC_Product[] $wc_products
     * @param array $ids
     *
     * @return array
     *
     * @throws QueryFilterInvalidParamException
     */
    private function transform_products(array $wc_products, array $ids): array {
        $meta_lookup           = $this->get_meta_repository()->get_product_meta( $ids );
        $ce_products           = [];
        $is_enabled_stock_sync = $this->get_product_config_service()->get()->isEnabledStockSync();
        $extra_data_attributes = $this->get_extra_data_attribute_mapping_service()->getExtraDataAttributeMappings()->get_mappings();

        /** @var WC_Product $wc_product */
        foreach ( $wc_products as $wc_product ) {
            if ( $wc_product->is_downloadable() || $wc_product->is_virtual() || $wc_product->is_type( 'grouped' ) ) {
                continue;
            }

            $product = $wc_product->get_parent_id() ? wc_get_product($wc_product->get_parent_id()) : $wc_product;

            $ce_products[] = $this->transform_product(
                $product,
                isset( $meta_lookup[ $wc_product->get_id() ] ) ? $meta_lookup[ $wc_product->get_id() ] : [],
                $is_enabled_stock_sync,
                $extra_data_attributes
            );
        }

        return $ce_products;
    }
}