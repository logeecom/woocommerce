<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\Products\Domain\CustomAttribute;
use ChannelEngine\BusinessLogic\Products\Domain\Product;
use ChannelEngine\BusinessLogic\Products\Domain\Variant;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Repositories\Meta_Repository;
use ChannelEngine\Repositories\Product_Repository;
use DateTime;
use WC_Product;
use WC_Product_Attribute;
use WC_Product_Variation;

/**
 * Class Products_Service
 *
 * @package ChannelEngine\Components\Services
 */
class Products_Service implements ProductsService {
	/**
	 * @var array
	 */
	protected $product_attributes = [];
	/**
	 * List of resolved category trails.
	 *
	 * @var array
	 */
	protected $category_trails = [];
	/**
	 * List of resolved images.
	 *
	 * @var array
	 */
	protected $images = [];
	/**
	 * @var Meta_Repository
	 */
	protected $meta_repository;
	/**
	 * @var ProductsSyncConfigService
	 */
	protected $product_config_service;

	/**
	 * @inheritDoc
	 */
	public function getProductIds( $page, $limit = 5000 ) {
		return $this->get_product_repository()->get_ids( $limit, $limit * $page );
	}

	/**
	 * Retrieves total count of products.
	 *
	 * @return int
	 */
	public function count() {
		return $this->get_product_repository()->get_count();
	}

	/**
	 * @inheritDoc
	 */
	public function getProducts( array $ids ) {
		// Invalidate cache to preserve memory.
		$this->category_trails = [];
		$this->images          = [];

		$args = [
			'return'         => 'objects',
			'posts_per_page' => - 1,
			'include'        => $ids,
		];

		$meta_lookup = $this->get_meta_repository()->get_product_meta( $ids );
		$wc_products = wc_get_products( $args );
		$ce_products = [];

		/** @var WC_Product $wc_product */
		foreach ( $wc_products as $wc_product ) {
			if ( $wc_product->is_downloadable() || $wc_product->is_virtual() ) {
				continue;
			}

			$ce_products[] = $this->transform_product(
				$wc_product,
				isset( $meta_lookup[ $wc_product->get_id() ] ) ? $meta_lookup[ $wc_product->get_id() ] : []
			);
		}

		return $ce_products;
	}

	/**
	 * Transforms WC product to ChannelEngine product.
	 *
	 * @param WC_Product $wc_product
	 * @param array $meta_lookup
	 *
	 * @return Product
	 */
	protected function transform_product( WC_Product $wc_product, array $meta_lookup ) {
		$this->product_attributes = [];
		$attributes               = $this->fetch_attributes( $wc_product, $meta_lookup );

		$product = new Product(
			$wc_product->get_id(),
			$attributes['price'],
			$attributes['stock'],
			$wc_product->get_name(),
			$attributes['description'],
			$attributes['purchase_price'],
			$attributes['msrp'],
			$attributes['vat_rate_type'],
			$attributes['shipping_costs'],
			$attributes['shipping_time'],
			$attributes['ean'],
			$attributes['manufacturer_product_number'],
			$attributes['url'],
			$attributes['brand'],
			$attributes['size'],
			$attributes['color'],
			$attributes['main_image_url'],
			$attributes['additional_image_urls'],
			$this->get_custom_attributes( $wc_product->get_attributes() ),
			$attributes['category_trail']
		);

		$variant_ids = $wc_product->get_children();

		if ( $variant_ids ) {
			$variant_meta_lookup = $this->get_meta_repository()->get_product_meta( $variant_ids );
			$variants            = wc_get_products( [
				'status' => 'publish',
				'type'   => 'variation',
				'parent' => $wc_product->get_id(),
				'limit'  => - 1,
				'return' => 'objects',
			] );

			/**
			 * @var WC_Product_Variation $variant
			 */
			foreach ( $variants as $variant ) {
				if ( $variant->is_virtual() || $variant->is_downloadable() ) {
					continue;
				}

				$product->addVariant(
					$this->transform_variant(
						$variant,
						$product,
						isset( $variant_meta_lookup[ $variant->get_id() ] ) ?
							$variant_meta_lookup[ $variant->get_id() ] : []
					)
				);
			}
		}

		return $product;
	}

	/**
	 * Retrieves all product variations.
	 *
	 * @param $parent_id
	 *
	 * @return WC_Product
	 */
	protected function get_variations( $parent_id ) {
		$args = [
			'post_parent' => $parent_id,
			'post_status' => 'publish',
		];

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return wc_get_products( $args );
	}

	protected function transform_variant( WC_Product $variant, Product $parent, array $meta_lookup = [] ) {
		$attributes = $this->fetch_attributes( $variant, $meta_lookup );

		$variant_custom_attributes = $this->get_custom_variant_attributes( $variant->get_attributes() );
		$custom_attributes         = array_merge( $variant_custom_attributes, $parent->getCustomAttributes() );

		return new Variant(
			$variant->get_id(),
			$parent,
			$attributes['price'] ?: $parent->getPrice(),
			$attributes['stock'] ?: $parent->getStock(),
			$parent->getName(),
			$attributes['description'] ?: $parent->getDescription(),
			$attributes['purchase_price'] ?: $parent->getPurchasePrice(),
			$attributes['msrp'] ?: $parent->getMsrp(),
			$attributes['vat_rate_type'] ?: $parent->getVatRateType(),
			$attributes['shipping_costs'] ?: $parent->getShippingCost(),
			$attributes['shipping_time'] ?: $parent->getShippingTime(),
			$attributes['ean'] ?: $parent->getEan(),
			$attributes['manufacturer_product_number'] ?: $parent->getManufacturerProductNumber(),
			$attributes['url'] ?: $parent->getUrl(),
			$attributes['brand'] ?: $parent->getBrand(),
			$attributes['size'] ?: $parent->getSize(),
			$attributes['color'] ?: $parent->getColor(),
			$attributes['main_image_url'] ?: $parent->getMainImageUrl(),
			$attributes['additional_image_urls'] ?: $parent->getAdditionalImageUrls(),
			$custom_attributes,
			$attributes['category_trail'] ?: $parent->getCategoryTrail()
		);
	}

	/**
	 * @param array $meta
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	protected function get_meta_value( array $meta, $key ) {
		return isset( $meta[ $key ] ) ? $meta[ $key ] : null;
	}

	/**
	 * Fetches attributes for ChannelEngine Product/Variant.
	 *
	 * @param WC_Product $wc_product
	 * @param array $meta_lookup
	 *
	 * @return array
	 */
	protected function fetch_attributes( WC_Product $wc_product, array $meta_lookup ) {
		$attributes = [];
		$now        = new DateTime();

		if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
			$attributes['price'] = $wc_product->get_price();
			if ( $wc_product->get_date_on_sale_from() >= $now
			     && $wc_product->get_date_on_sale_to() <= $now ) {
				$attributes['price'] = $wc_product->get_sale_price();
			}
		} else {
			$attributes['price'] = wc_get_price_including_tax( $wc_product );
			if ( $wc_product->get_date_on_sale_from() >= $now
			     && $wc_product->get_date_on_sale_to() <= $now ) {
				$attributes['price'] = wc_get_price_including_tax(
					$wc_product,
					[ 'price' => $wc_product->get_sale_price() ]
				);
			}
		}

		$attributes['stock']          = $wc_product->get_manage_stock() ?
			$wc_product->get_stock_quantity() : $this->get_product_config_service()->get()->getDefaultStock();
		$attributes['description']    = strip_tags( $wc_product->get_description() );
		$attributes['purchase_price'] = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'purchase_price' ]
		);
		$attributes['msrp']           = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'msrp', 'manufacturer_price', 'vendor_price' ]
		);

		$attributes['vat_rate_type']               = 'STANDARD';
		$attributes['shipping_costs']              = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'shipping_cost' ]
		);
		$attributes['shipping_time']               = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'shipping_time' ]
		);
		$attributes['ean']                         = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'ean', 'gtin' ]
		);
		$attributes['manufacturer_product_number'] = $wc_product->get_sku() ?: $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'sku' ]
		);
		$attributes['url']                         = $wc_product->get_permalink();
		$attributes['brand']                       = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'brand' ]
		);
		$attributes['size']                        = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'size' ]
		);
		$attributes['color']                       = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'color' ]
		);

		$image = '';
		if ( ! empty( $wc_product->get_image_id() ) ) {
			$image                                       = isset( $this->images[ $wc_product->get_image_id() ] ) ?
				$this->images[ $wc_product->get_image_id() ] : get_post( $wc_product->get_image_id() );
			$this->images[ $wc_product->get_image_id() ] = $image;
		}

		$attributes['main_image_url']        = $image ? $image->guid : null;
		$attributes['additional_image_urls'] = $this->get_additional_image_urls( $wc_product->get_gallery_image_ids() );
		$attributes['category_trail']        = $this->get_product_category_trail( $wc_product->get_id() );

		return $attributes;
	}

	/**
	 * @param WC_Product $wc_product
	 * @param $meta_lookup
	 * @param $keys
	 *
	 * @return string
	 */
	protected function get_attribute( WC_Product $wc_product, $meta_lookup, $keys ) {
		$meta_keys = array_merge(
			$keys,
			array_map( static function ( $item ) {
				return '_' . $item;
			}, $keys )
		);

		foreach ( $meta_keys as $key ) {
			$attribute = $this->get_meta_value( $meta_lookup, $key );

			if ( $attribute ) {
				$this->product_attributes[] = $key;

				return $attribute;
			}
		}

		$attribute_keys = array_merge(
			$keys,
			array_map( static function ( $item ) {
				return str_replace( '_', '-', $item );
			}, $keys )
		);

		foreach ( $attribute_keys as $key ) {
			$attribute = $wc_product->get_attribute( $key );

			if ( $attribute ) {
				$this->product_attributes[] = $key;

				return $attribute;
			}
		}

		return '';
	}

	/**
	 * Retrieves additional image urls.
	 *
	 * @param array $image_ids
	 *
	 * @return array
	 */
	protected function get_additional_image_urls( array $image_ids ) {
		$additional_image_urls = [];
		$images                = [];

		if ( $image_ids ) {
			$images = get_posts( [
				'post_type' => 'attachment',
				'include'   => $image_ids
			] );
		}

		foreach ( $images as $image ) {
			$additional_image_urls[] = $image->guid;
		}

		return $additional_image_urls;
	}

	/**
	 * Fetches custom product attributes.
	 *
	 * @param WC_Product_Attribute[] $product_attributes
	 *
	 * @return array
	 */
	protected function get_custom_attributes( array $product_attributes ) {
		$custom_attributes = [];
		foreach ( $product_attributes as $key => $attribute ) {
			if ( in_array($key, $this->product_attributes, true) || $attribute->get_variation() ) {
				continue;
			}

			foreach ( $attribute->get_options() as $option ) {
				$custom_attributes[] = new CustomAttribute(
					$attribute->get_name(),
					$option,
					CustomAttribute::TYPE_TEXT,
					true
				);
			}
		}

		return $custom_attributes;
	}

	/**
	 * Fetches custom variant attributes.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	protected function get_custom_variant_attributes( array $attributes ) {
		$custom_attributes = [];
		foreach ( $attributes as $key => $value ) {
			if ( in_array( $key, $this->product_attributes ) ) {
				continue;
			}

			$custom_attributes[] = new CustomAttribute(
				$key,
				$value,
				CustomAttribute::TYPE_TEXT,
				true
			);
		}

		return $custom_attributes;
	}

	/**
	 * Fetch product's category trail.
	 *
	 * @param $product_id
	 *
	 * @return string
	 */
	protected function get_product_category_trail( $product_id ) {
		$product_category = '';
		$terms            = get_the_terms( $product_id, 'product_cat' );

		if ( is_array( $terms ) ) {
			$final_term      = null;
			$total_ancestors = - 1;

			foreach ( $terms as $term ) {
				$parent_categories = get_ancestors( $term->term_id, 'product_cat' );
				$ancestors_count   = count( $parent_categories );

				if ( $ancestors_count > $total_ancestors ) {
					$total_ancestors = $ancestors_count;
					$final_term      = $term;
				}
			}

			if ( ! empty( $this->category_trails[ $final_term->term_id ] ) ) {
				return $this->category_trails[ $final_term->term_id ];
			}

			$product_category  = $final_term->name;
			$product_cat_id    = $final_term->term_id;
			$parent_categories = get_ancestors( $product_cat_id, 'product_cat' );

			if ( count( $parent_categories ) > 0 ) {
				foreach ( $parent_categories as $parent_category ) {
					$category         = get_term( $parent_category, 'product_cat' );
					$product_category = $category->name . ' > ' . $product_category;
				}
			}

			$this->category_trails[ $final_term->term_id ] = $product_category;
		}

		return $product_category;
	}

	/**
	 * Retrieves an instance of Meta_Repository.
	 *
	 * @return Meta_Repository
	 */
	protected function get_meta_repository() {
		if ( $this->meta_repository === null ) {
			$this->meta_repository = new Meta_Repository();
		}

		return $this->meta_repository;
	}

	/**
	 * Retrieves an instance of ProductsSyncConfigService.
	 *
	 * @return ProductsSyncConfigService
	 */
	protected function get_product_config_service() {
		if ( $this->product_config_service === null ) {
			$this->product_config_service = ServiceRegister::getService( ProductsSyncConfigService::class );
		}

		return $this->product_config_service;
	}

	protected function get_product_repository() {
		return new Product_Repository();
	}
}
