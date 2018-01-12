<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 17/09/15
 * Time: 14:45
 */

class SimpleXMLExtended extends SimpleXMLElement {
	public function addCData($cdata_text) {
		$node = dom_import_simplexml($this); 
		$no = $node->ownerDocument; 
		$node->appendChild($no->createCDATASection($cdata_text)); 
	} 

	public function addChildCData($element_name, $cdata) {
		$this->$element_name = NULL;
		$this->$element_name->addCData($cdata);
	}
}

class Channel_Engine_Product_Feed extends Channel_Engine_Base_Class{

	private $product_validation;
	

	public function __construct($product_validation) {
		// Enable errors
		error_reporting(-1);
		set_time_limit(60 * 5);
		ini_set('display_errors', 'On');
		ini_set('memory_limit', '2048M');

		$this->product_validation = $product_validation;
	}

	private function br2nl($string) {
		return preg_replace('/<br\s*?\/?>|<\/p>/i', "\r\n", $string); 
	}

	private function getStock($product) {
		
		// Product out of stock, return 0
		if(!$product->is_in_stock()) return 0;
		
		// Stock amount is managed, return stock amount
		if($product->managing_stock()) return $product->get_stock_quantity();
		
		// Product is in stock, but no amount is managed, return 100 as a placeholder
		return 100;
    }

	public function generate_product_feed() {
		global $wpdb;
		$xml = new SimpleXMLExtended('<products></products>');
		$attrs_lookup = $this->getAttributeLookup();
		$meta_lookup = $this->getMetaLookup();

		/*
		$tax = WC_TAX::get_base_tax_rates();
        $tax_rate = isset($tax[1]['rate']) ? $tax[1]['rate'] : null;

        return $tax_rate;*/
        $args = array(
            'status' => 'publish',
            'return' => 'objects',
            'posts_per_page' => -1
        );
		$newProducts = wc_get_products($args);

		foreach($newProducts as $item) {
            $id = $item->get_id();
            //Skip products with incomplete data
            // if(! $this->product_validation->validate_channel_engine_product($id)) continue;
            $meta = $this->getOrEmpty($meta_lookup, $id);
            $attrs = $this->getOrEmpty($attrs_lookup, $id);
            $pr = parent::PREFIX;

            $wcProduct = $item;

            $product = array();

            $product['id'] = $id;
            $product['meta'] = $meta;
            $product['attrs'] = $attrs;

            // featured image
            $product['image_url'] = '';
            $images = $this->get($meta, '_wp_attached_file');
            if (count($images) && isset($images[$item->get_image_id()])) {
                $product['image_url'] = $this->getUrlForFilename($images[$item->get_image_id()]);
            }

            // gallery images
            $product['gallery_url'] = array();
            foreach($wcProduct->get_gallery_image_ids() as $imgId){
                if(count($images) && isset($images[$imgId])) {
                    $product['gallery_url'][] = $this->getUrlForFilename($images[$imgId]);
                }
            }
			$product['category'] = parent::get_product_category($id);
			$product['url'] = $wcProduct->get_permalink();
			$product['name'] = $item->get_title();
			$product['description'] = strip_tags($this->br2nl($item->get_description()));
			$product['stock'] = $this->getStock($wcProduct);
			$product['gtin'] = $this->getGtin($meta);
			$product['price'] = wc_get_price_including_tax($wcProduct);
			$product['price_ex_vat'] = wc_get_price_excluding_tax($wcProduct);
			$product['list_price'] = wc_get_price_including_tax($wcProduct, 1, $wcProduct->get_regular_price());
			$product['vat'] = $this->calcVat($product['price'], $product['price_ex_vat']);
			$product['brand'] = $this->get($meta, $pr.'_brand');
			$product['custom_attributes'] = $this->get($meta, '_product_attributes');
			$product['sku'] = $this->get($meta, '_sku');
			$product['shipping_costs'] = $this->get($meta, $pr.'_shipping_costs');
			$product['shipping_time'] = $this->get($meta, $pr.'_shipping_time');
			$product['parent_id'] = null;
			$product['size'] = $this->get($meta, $pr.'_size');
			$product['color'] = $this->get($meta, $pr.'_color');
			$product['type'] = $wcProduct->get_type();

            $this->createProductNode($xml, $product);


			$vars =  $item->get_children();

			if(count($vars)) {

                foreach ($vars as $variant_id) {

                    $variant = wc_get_product($variant_id);

                    $varId = $variant->get_id();
                    $wcProductVar = $variant;

                    $meta = $this->getOrEmpty($meta_lookup, $varId);

                    $attrs = $this->getOrEmpty($attrs_lookup, $varId);

                    // featured image
                    $product['image_url'] = '';
                    $images = $this->get($meta, '_wp_attached_file');
                    if (count($images) && isset($images[$variant->get_image_id()])) {
                        $product['image_url'] = $this->getUrlForFilename($images[$variant->get_image_id()]);
                    }

                    // gallery images
                    $product['gallery_url'] = array();
                    foreach ($wcProduct->get_gallery_image_ids() as $imgId) {
                        if (count($images) && isset($images[$imgId])) {
                            $product['gallery_url'][] = $this->getUrlForFilename($images[$imgId]);
                        }
                    }

                    $product['id'] = $variant->get_id();
                    $product['parent_id'] = $id;
                    $product['stock'] = $this->getStock($wcProductVar);
                    $product['attrs'] = $attrs;
                    $product['meta'] = $meta;
                    $product['type'] = $wcProductVar->get_type();
                    $product['sku'] = $this->get($meta, '_sku');
                    $product['gtin'] = $this->getGtin($meta);
                    $product['price'] = wc_get_price_including_tax($wcProductVar);
					$product['price_ex_vat'] = wc_get_price_excluding_tax($wcProductVar, 1, $wcProductVar->get_price());
					$product['list_price'] = wc_get_price_including_tax($wcProductVar, 1, $wcProduct->get_regular_price());
                    $product['vat'] = $this->calcVat($product['price'], $product['price_ex_vat']);

                    $this->createProductNode($xml, $product);
                }
            }
		}
		$this->writeXML($xml);
	}

	private function calcVat($price, $priceExVat) {
		if($price == 0) return 0;
		$vat = (($price - $priceExVat) / $priceExVat) * 100;
		return round($vat);
	}

	private function getGtin($meta) {
		$ceGtin = $this->get($meta, parent::PREFIX.'_gtin');
		if(!empty($ceGtin)) return $ceGtin;
		
		$ceGtin = $this->get($meta, '_ean');
		
		return $ceGtin;
	}

	private function createProductNode(SimpleXMLExtended $xml, $product) {

		//Generate XML entities
		$pXml = $xml->addChild('Product');
		//Required attributes
		$pXml->addChildCData('Name', $product['name']);
		$pXml->addChildCData('Description', $product['description']);
		$pXml->addChild('Price', $product['price']);
		$pXml->addChild('PriceExVat', $product['price_ex_vat']);
		$pXml->addChild('ListPrice', $product['list_price']);
		$pXml->addChild('VAT', $product['vat']);
		$pXml->addChild('Stock', $product['stock']);
		$pXml->addChildCData('Brand', $product['brand']);
		$pXml->addChild('MerchantProductNo', $product['id']);
		$pXml->addChild('VendorProductNo', $product['sku']);
		$pXml->addChild('GTIN', $product['gtin']);
		$pXml->addChild('ShippingCosts', $product['shipping_costs']);
		$pXml->addChild('ShippingTime', $product['shipping_time']);
		$pXml->addChild('ProductUrl', $product['url']);
		$pXml->addChild('ImageUrl', $product['image_url']);
		if(isset($product['gallery_url'])){
            foreach($product['gallery_url'] as $gal_url){
                $pXml->addChild('GalleryUrl', $gal_url);
            }
        }

		$pXml->addChildCData('Category', $product['category']);
		//Optional attributes
		$pXml->addChild('MerchantGroupNo', $product['parent_id']);
		$pXml->addChild('Size', $product['size']);
		$pXml->addChild('Color', $product['color']);
		$pXml->addChild('Type', $product['type']);

		$specsNode = $pXml->addChild('Specs');
		$meta = $product['meta'];
		$attrs = $product['attrs'];

		if(isset($product['custom_attributes']) && $product['custom_attributes'] != null) {
			$custAttrs = unserialize($product['custom_attributes']);

			foreach($custAttrs as $slug => $info) {
				if($this->startsWith($slug, 'pa_') || $info['is_visible'] == 0 || $info['is_variation'] == 1) continue;
				
				$specsNode->addChildCData($this->cleanTag($slug), $info['value']);
			}
		}

		foreach($attrs as $slug => $values) {
			// Ignore group specs.
			if(isset($meta['attribute_pa_' . $slug])) continue;

			$specsNode->addChildCData($this->cleanTag($slug), implode(',', $values));
		}

		foreach($meta as $key => $value) {
			if(!$this->startsWith($key, 'attribute_pa_')) continue;
			$key = str_replace('attribute_pa_', '', $key);
			if(!isset($attrs[$key][$value])) continue;

			$formattedValue = $attrs[$key][$value];

			$specsNode->addChildCData($this->cleanTag($key), $formattedValue);
		}



		$specsNode->addChild('Weight', $this->get($meta, '_weight'));
		$specsNode->addChild('Width', $this->get($meta, '_width'));
		$specsNode->addChild('Length', $this->get($meta, '_length'));
		$specsNode->addChild('Height', $this->get($meta, '_height'));
		
	}

	private function cleanTag($tag) {
		$tag = str_replace(' ', '_', $tag);
		if(is_numeric(substr($tag, 0, 1))) $tag = '_' . $tag;
		return $tag;
	}

	private function startsWith($input, $query) {
		return substr($input, 0, strlen($query)) === $query;
	}

	private function get($arr, $key) {
		return isset($arr[$key]) ? $arr[$key] : null;
	}

	private function getOrEmpty($arr, $key) {
		return isset($arr[$key]) ? $arr[$key] : [];
	}

	private function writeXML($xml) {
		if(ob_get_length()) ob_clean();
		header('Content-Type: text/xml');
		echo($xml->asXML());
	}

	private function getAttributeLookup() {
		global $wpdb;

		$tr = $wpdb->term_relationships;
		$tx = $wpdb->term_taxonomy;
		$tm = $wpdb->terms;

		$query = "
			SELECT
				$tr.object_id AS product_id,
				$tx.taxonomy AS slug,
				$tm.slug AS value_slug,
				$tm.name AS value
			FROM $tr
			INNER JOIN $tx ON $tr.term_taxonomy_id = $tx.term_taxonomy_id
			INNER JOIN $tm ON $tx.term_id = $tm.term_id
			WHERE $tx.taxonomy LIKE 'pa_%'
		";

		$attributes = $wpdb->get_results($query, OBJECT);

		$lookup = array();
	
		foreach($attributes as $a) {
			$a->slug = str_replace('pa_', '', $a->slug);
			if(!isset($lookup[$a->product_id])) $lookup[$a->product_id] = array();
			if(!isset($lookup[$a->product_id][$a->slug])) $lookup[$a->product_id][$a->slug] = array();
			if(!isset($lookup[$a->product_id][$a->slug][$a->value_slug])) $lookup[$a->product_id][$a->slug][$a->value_slug] = array();
			$lookup[$a->product_id][$a->slug][$a->value_slug] = $a->value;
		}

		return $lookup;
	}

	private function getMetaLookup() {
		global $wpdb;

		$p = $wpdb->posts;
		$pm = $wpdb->postmeta;

		$query = "
			SELECT $pm.*, $p.post_parent 
			FROM $pm
			INNER JOIN $p ON $p.id = $pm.post_id
			WHERE (($p.post_status = 'publish' 
			AND $p.post_type IN ('product', 'product_variation')) or
             ($p.post_status = 'inherit' 
			AND $p.post_type IN ('attachment')))
			AND (
				$pm.meta_key LIKE '" . parent::PREFIX . "%'
				OR $pm.meta_key LIKE 'attribute_pa_%'
				OR $pm.meta_key IN('_product_attributes', '_weight', '_length', '_height', '_width', '_sku', '_ean', '_wp_attached_file')
			)
		";

		$lookup = array();

		$meta = $wpdb->get_results($query, OBJECT);

		foreach($meta as $item) {
            if($item->meta_key == '_wp_attached_file' && $item->post_parent > 0){
                $post_id = $item->post_parent;
                if(!isset($lookup[$post_id])) {
                    $lookup[$post_id] = array();
                    $lookup[$post_id][$item->meta_key] = array();
                }
                $lookup[$post_id][$item->meta_key][$item->post_id] = $item->meta_value;
            }else{
                $post_id = $item->post_id;
                if(!isset($lookup[$post_id])) $lookup[$post_id] = array();
                $lookup[$post_id][$item->meta_key] = $item->meta_value;
            }

		}

		return $lookup;
	}

	private function getUrlForFilename($file){
        if ( ( $uploads = wp_get_upload_dir() ) && false === $uploads['error'] ) {
            // Check that the upload base exists in the file location.
            if ( 0 === strpos( $file, $uploads['basedir'] ) ) {
                // Replace file location with url location.
                $url = str_replace($uploads['basedir'], $uploads['baseurl'], $file);
            } elseif ( false !== strpos($file, 'wp-content/uploads') ) {
                // Get the directory name relative to the basedir (back compat for pre-2.7 uploads)
                $url = trailingslashit( $uploads['baseurl'] . '/' . _wp_get_attachment_relative_path( $file ) ) . basename( $file );
            } else {
                // It's a newly-uploaded file, therefore $file is relative to the basedir.
                $url = $uploads['baseurl'] . "/$file";
            }
        }


        // On SSL front end, URLs should be HTTPS.
        if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $GLOBALS['pagenow'] ) {
            $url = set_url_scheme( $url );
        }

        return $url;

    }

	private function dd($any) {
		echo('<pre>');
		var_dump($any);
		echo('</pre>');
	}
}