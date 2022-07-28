<?php

namespace ChannelEngine\Components\Services;

use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsSyncConfigService;
use ChannelEngine\BusinessLogic\Products\Domain\CustomAttribute;
use ChannelEngine\BusinessLogic\Products\Domain\Product;
use ChannelEngine\BusinessLogic\Products\Domain\Variant;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Repositories\Meta_Repository;
use ChannelEngine\Repositories\Product_Repository;
use ChannelEngine\Utility\Standard_Product_Attributes;
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
	protected static $tax_class_map = [
		''             => 'STANDARD',
		'reduced-rate' => 'REDUCED',
		'zero-rate'    => 'EXEMPT',
	];

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
	 * @var Attribute_Mappings_Service
	 */
	protected $attribute_mapping_service;
	/**
	 * @var Extra_Data_Attribute_Mappings_Service
	 */
	protected $extra_data_attribute_mapping_service;

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
	 *
	 * @throws QueryFilterInvalidParamException
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

		$meta_lookup           = $this->get_meta_repository()->get_product_meta( $ids );
		$wc_products           = wc_get_products( $args );
		$ce_products           = [];
		$is_enabled_stock_sync = $this->get_product_config_service()->get()->isEnabledStockSync();
		$extra_data_attributes = $this->get_extra_data_attribute_mapping_service()->getExtraDataAttributeMappings()->get_mappings();

		/** @var WC_Product $wc_product */
		foreach ( $wc_products as $wc_product ) {
			if ( $wc_product->is_downloadable() || $wc_product->is_virtual() ) {
				continue;
			}

			$ce_products[] = $this->transform_product(
				$wc_product,
				isset( $meta_lookup[ $wc_product->get_id() ] ) ? $meta_lookup[ $wc_product->get_id() ] : [],
				$is_enabled_stock_sync,
				$extra_data_attributes
			);
		}

		return $ce_products;
	}

	/**
	 * Get standard WC attributes
	 *
	 * @return WC_Product_Attribute[]
	 */
	public function get_standard_product_attributes() {
		$standard_attributes = [];
		foreach ( Standard_Product_Attributes::ATTRIBUTES as $ATTRIBUTE ) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_name( Standard_Product_Attributes::PREFIX . '_' . $ATTRIBUTE );
			$attribute->set_position( 0 );
			$attribute->set_visible( 1 );
			$attribute->set_variation( 0 );
			$attribute->set_id( 0 );

			$standard_attributes[] = $attribute;
		}

		return $standard_attributes;
	}

	/**
	 * Retrieves product mapped attributes.
	 *
	 * @return WC_Product_Attribute[]
	 */
	public function get_custom_product_attributes() {
		return $this->get_meta_repository()->get_product_attributes();
	}

	/**
	 * Transforms WC product to ChannelEngine product.
	 *
	 * @param WC_Product $wc_product
	 * @param array $meta_lookup
	 * @param bool $is_enabled_stock_sync
	 * @param array $extra_data_attributes
	 *
	 * @return Product
	 * @throws QueryFilterInvalidParamException
	 */
	protected function transform_product( WC_Product $wc_product, array $meta_lookup, $is_enabled_stock_sync, array $extra_data_attributes = [] ) {
		$this->product_attributes = [];
		$attributes               = $this->fetch_attributes( $wc_product, $meta_lookup );

		//Channel Engine does not support negative values stock, but wc does
		if ( $attributes['stock'] < 0 ) {
			$attributes['stock'] = 0;
		}

		$product = new Product(
			$wc_product->get_id(),
			$attributes['price'],
			$is_enabled_stock_sync ? $attributes['stock'] : 0,
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
			$this->get_custom_attributes( $wc_product, $meta_lookup, $extra_data_attributes ),
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
							$variant_meta_lookup[ $variant->get_id() ] : [],
                        $extra_data_attributes
					)
				);
			}
		}

		return $product;
	}

	protected function transform_variant( WC_Product $variant, Product $parent, array $meta_lookup = [], array $extra_data_attributes = [] ) {
		$attributes = $this->fetch_attributes( $variant, $meta_lookup );

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
            $this->get_custom_attributes( $variant, $meta_lookup, $extra_data_attributes ),
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
	 *
	 * @throws QueryFilterInvalidParamException
	 */
	protected function fetch_attributes( WC_Product $wc_product, array $meta_lookup ) {
		$attributes = $this->set_mapped_attributes( $wc_product, $meta_lookup );

		$attributes['stock'] = $wc_product->get_manage_stock() ?
			$wc_product->get_stock_quantity() : $this->get_product_config_service()->get()->getDefaultStock();

		$attributes['vat_rate_type']  = 'STANDARD';
		$attributes['shipping_costs'] = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'shipping_cost' ]
		);
		$attributes['shipping_time']  = $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ 'shipping_time' ]
		);

		$attributes['url']                         = $wc_product->get_permalink();

		$image = '';
		if ( ! empty( $wc_product->get_image_id() ) ) {
			$image                                       = isset( $this->images[ $wc_product->get_image_id() ] ) ?
				$this->images[ $wc_product->get_image_id() ] : get_post( $wc_product->get_image_id() );
			$this->images[ $wc_product->get_image_id() ] = $image;
		}

		$attributes['main_image_url']        = $image ? $image->guid : null;
		$attributes['additional_image_urls'] = $this->get_additional_image_urls( $wc_product->get_gallery_image_ids() );

		return $attributes;
	}

	/**
	 * Set values of mapped attributes
	 *
	 * @param $wc_product
	 * @param $meta_lookup
	 *
	 * @return array
	 * @throws QueryFilterInvalidParamException
	 */
	protected function set_mapped_attributes( $wc_product, $meta_lookup ) {
		$attributes        = [];
		$attributesMapping = $this->get_attribute_mapping_service()->getAttributeMappings();

		if ( ! $attributesMapping ) {
			return [];
		}

		$attributes['price'] = $attributesMapping->get_price() !== null ? $this->get_attribute(
			$wc_product,
			$meta_lookup,
			[ $attributesMapping->get_price() ]
		) : '';


		$attributes['description'] = $attributesMapping->get_details() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_details() ]
			) : '';

		$attributes['purchase_price'] = $attributesMapping->get_purchase_price() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_purchase_price() ]
			) : '';

		$attributes['msrp'] = $attributesMapping->get_catalogue_price() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_catalogue_price() ]
			) : '';

		$attributes['ean'] = $attributesMapping->get_gtin() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_gtin() ]
			) : '';

		$attributes['brand'] = $attributesMapping->get_brand() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_brand() ]
			) : '';

		$attributes['size'] = $attributesMapping->get_size() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_size() ]
			) : '';

		$attributes['color'] = $attributesMapping->get_color() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_color() ]
			) : '';

		$attributes['category_trail'] = $attributesMapping->get_category() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_category() ] ) :
			'';

		$attributes['manufacturer_product_number'] = $attributesMapping->get_vendor_product_number() !== null ?
			$this->get_attribute(
				$wc_product,
				$meta_lookup,
				[ $attributesMapping->get_vendor_product_number() ] ) :
			'';

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
		if ( strpos( $keys[0], Standard_Product_Attributes::PREFIX ) === 0 ) {
			return $this->get_standard_attribute( $wc_product, $keys[0] );
		}

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
	 * Get standard attribute value
	 *
	 * @param WC_Product $wc_product
	 * @param $attribute
	 *
	 * @return float|int|string|null
	 */
	protected function get_standard_attribute( WC_Product $wc_product, $attribute ) {
		switch ( $attribute ) {
			case Standard_Product_Attributes::PREFIX . '_' . 'id':
				return $wc_product->get_id();
			case Standard_Product_Attributes::PREFIX . '_' . 'name':
				return $wc_product->get_name();
			case Standard_Product_Attributes::PREFIX . '_' . 'vat':
				if ( $wc_product->get_tax_class() === 'reduced-rate' ) {
					return 'Reduced rate';
				}

				if ( $wc_product->get_tax_class() === 'zero-rate' ) {
					return 'Zero rate';
				}

				return 'Standard rate';
			case Standard_Product_Attributes::PREFIX . '_' . 'stock':
				if ( $wc_product->get_stock_quantity() > 0 ) {
					return $wc_product->get_stock_quantity();
				}

				return 0;
			case Standard_Product_Attributes::PREFIX . '_' . 'description':
				return $wc_product->get_description();
			case Standard_Product_Attributes::PREFIX . '_' . 'short_description':
				return $wc_product->get_short_description();
			case Standard_Product_Attributes::PREFIX . '_' . 'price_incl_tax':
				return wc_get_price_including_tax( $wc_product, [ 'price' => $wc_product->get_regular_price() ] );
			case Standard_Product_Attributes::PREFIX . '_' . 'price_excl_tax':
				return $wc_product->get_regular_price();
			case Standard_Product_Attributes::PREFIX . '_' . 'sale_price_incl_tax':
				return wc_get_price_including_tax( $wc_product, [ 'price' => $wc_product->get_sale_price() ] );
			case Standard_Product_Attributes::PREFIX . '_' . 'sale_price_excl_tax':
				return $wc_product->get_sale_price();
			case Standard_Product_Attributes::PREFIX . '_' . 'sku':
				return $wc_product->get_sku();
			case Standard_Product_Attributes::PREFIX . '_' . 'product_url':
				return $wc_product->get_permalink();
			case Standard_Product_Attributes::PREFIX . '_' . 'category':
				return $this->get_product_category_trail( $wc_product->get_id() );
			case Standard_Product_Attributes::PREFIX . '_' . 'image_url':
				return $this->images[ $wc_product->get_image_id() ]->guid;
			case Standard_Product_Attributes::PREFIX . '_' . 'weight':
				return $wc_product->get_weight()();
			case Standard_Product_Attributes::PREFIX . '_' . 'length':
				return $wc_product->get_length();
			case Standard_Product_Attributes::PREFIX . '_' . 'width':
				return $wc_product->get_width();
			case Standard_Product_Attributes::PREFIX . '_' . 'height':
				return $wc_product->get_height();
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
	 * @param WC_Product $wc_product
	 * @param array $meta_lookup
	 * @param array $extra_data_attributes
	 *
	 * @return array
	 */
	protected function get_custom_attributes( WC_Product $wc_product, array $meta_lookup, array $extra_data_attributes ) {
		$custom_attributes = [];

		foreach ( $extra_data_attributes as $extra_attribute_key => $extra_attribute_value ) {
            $attr = $this->get_attribute(
                $wc_product,
                $meta_lookup,
                [ $extra_attribute_value ]
            );
            if ($attr) {
                $custom_attributes[] = new CustomAttribute(
                    $extra_attribute_key,
                    $attr,
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
	 * Retrieves price attribute value.
	 *
	 * @param WC_Product $wc_product
	 *
	 * @return float|string
	 */
	protected function get_price_value( WC_Product $wc_product ) {
		$now        = new DateTime();

		if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
			$price = $wc_product->get_price();
			if ( $wc_product->get_date_on_sale_from() >= $now
			     && $wc_product->get_date_on_sale_to() <= $now ) {
				$price = $wc_product->get_sale_price();
			}
		} else {
			$price = wc_get_price_including_tax( $wc_product );
			if ( $wc_product->get_date_on_sale_from() >= $now
			     && $wc_product->get_date_on_sale_to() <= $now ) {
				$price = wc_get_price_including_tax(
					$wc_product,
					[ 'price' => $wc_product->get_sale_price() ]
				);
			}
		}

		return $price;
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

	/**
	 * Retrieves an instance of Attribute_Mappings_Service.
	 *
	 * @return Attribute_Mappings_Service
	 */
	protected function get_attribute_mapping_service() {
		if ( $this->attribute_mapping_service === null ) {
			$this->attribute_mapping_service = ServiceRegister::getService( Attribute_Mappings_Service::class );
		}

		return $this->attribute_mapping_service;
	}

	/**
	 * Retrieves an instance of Extra_Data_Attribute_Mappings_Service.
	 *
	 * @return Extra_Data_Attribute_Mappings_Service
	 */
	protected function get_extra_data_attribute_mapping_service() {
		if ( $this->extra_data_attribute_mapping_service === null ) {
			$this->extra_data_attribute_mapping_service = ServiceRegister::getService( Extra_Data_Attribute_Mappings_Service::class );
		}

		return $this->extra_data_attribute_mapping_service;
	}

	protected function get_product_repository() {
		return new Product_Repository();
	}

	/**
	 * @param WC_Product $product
	 */
	protected function get_product_tax_rate( $product ) {
		return ! empty( static::$tax_class_map[ $product->get_tax_class() ] ) ? static::$tax_class_map[ $product->get_tax_class() ] : 'STANDARD';
	}
}
