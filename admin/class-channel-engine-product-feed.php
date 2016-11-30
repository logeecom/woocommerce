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

	public function generate_product_feed() {
		global $wpdb;
		$xml = new SimpleXMLExtended('<products></products>');
		$products = $this->getProducts();
		$vars_lookup = $this->getProductVariationsLookup();
		$attrs_lookup = $this->getAttributeLookup();
		$meta_lookup = $this->getMetaLookup();

		/*
		$tax = WC_TAX::get_base_tax_rates();
        $tax_rate = isset($tax[1]['rate']) ? $tax[1]['rate'] : null;

        return $tax_rate;*/

		foreach($products as $item) {
			$id = $item->ID;
			//Skip products with incomplete data
			// if(! $this->product_validation->validate_channel_engine_product($id)) continue;
			$vars = $this->getOrEmpty($vars_lookup, $id);
			$meta = $this->getOrEmpty($meta_lookup, $id);
			$attrs = $this->getOrEmpty($attrs_lookup, $id);
			$pr = parent::PREFIX;

			$wcProduct = wc_get_product($id);
			$imageId = get_post_thumbnail_id($id);

			$product = array();

			$price = 

			$product['id'] = $id;
			$product['meta'] = $meta;
			$product['attrs'] = $attrs;
			$product['image_url'] = wp_get_attachment_url($imageId);
			$product['category'] = parent::get_product_category($id);
			$product['url'] = $wcProduct->get_permalink();
			$product['name'] = $item->post_title;
			$product['description'] = strip_tags($item->post_content);
			$product['stock'] = $wcProduct->get_stock_quantity();
			$product['gtin'] = $this->get($meta, $pr.'_gtin');
			$product['price'] = $wcProduct->get_price_including_tax();
			$product['purchase_price'] = $wcProduct->get_price_excluding_tax(1, $wcProduct->get_regular_price());
			$product['list_price'] = $wcProduct->get_price_including_tax(1, $wcProduct->get_regular_price());
			$product['vat'] = $this->calcVat($product['price'], $product['purchase_price']);
			$product['brand'] = $this->get($meta, $pr.'_brand');
			$product['sku'] = $this->get($meta, '_sku');
			$product['shipping_costs'] = $this->get($meta, $pr.'_shipping_costs');
			$product['shipping_time'] = $this->get($meta, $pr.'_shipping_time');
			$product['parent_id'] = null;
			$product['size'] = $this->get($meta, $pr.'_size');
			$product['color'] = $this->get($meta, $pr.'_color');
			$product['type'] = $wcProduct->product_type;

			$this->createProductNode($xml, $product, null);

			foreach($vars as $variant) {
				$varId = $variant->ID;
				$meta = $this->getOrEmpty($meta_lookup, $varId);
				$attrs = $this->getOrEmpty($attrs_lookup, $varId);

				$imageId = get_post_thumbnail_id($varId);
				if($imageId != 0) {
					$product['image_url'] = wp_get_attachment_url($imageId);
				}

				$wcProductVar = wc_get_product($varId);
				$product['id'] = $variant->ID;
				$product['parent_id'] = $id;
				$product['stock'] = $wcProductVar->get_stock_quantity();
				$product['attrs'] = $attrs;
				$product['meta'] = $meta;
				$product['type'] = $wcProductVar->product_type;
				$product['sku'] = $this->get($meta, '_sku');
				$product['gtin'] = $this->get($meta, $pr.'_gtin');

				$product['price'] = $wcProductVar->get_price_including_tax();
				$product['purchase_price'] = $wcProductVar->get_price_excluding_tax(1, $wcProductVar->get_regular_price());
				$product['list_price'] = $wcProductVar->get_price_including_tax(1, $wcProductVar->get_regular_price());
				$product['vat'] = $this->calcVat($product['price'], $product['purchase_price']);

				$this->createProductNode($xml, $product);
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

		/*$gpfData = $this->get($meta, '_woocommerce_gpf_data');
		if(is_null($gpfData)) return null;

		$gpfArr = unserialize($gpfData);	
		$gpfGtin = $this->get($gpfArr, 'gtin');*/
		
		return $gpfGtin;
	}

	private function createProductNode($xml, $product) {

		//Generate XML entities
		$pXml = $xml->addChild('Product');
		//Required attributes
		$pXml->addChildCData('Name', $product['name']);
		$pXml->addChildCData('Description', $product['description']);
		$pXml->addChild('Price', $product['price']);
		$pXml->addChild('PurchasePrice', $product['purchase_price']);
		$pXml->addChild('ListPrice', $product['list_price']);
		$pXml->addChild('VAT', $product['vat']);
		$pXml->addChild('Stock', $product['stock']);
		$pXml->addChild('Brand', $product['brand']);
		$pXml->addChild('MerchantProductNo', $product['id']);
		$pXml->addChild('VendorProductNo', $product['sku']);
		$pXml->addChild('GTIN', $product['gtin']);
		$pXml->addChild('ShippingCosts', $product['shipping_costs']);
		$pXml->addChild('ShippingTime', $product['shipping_time']);
		$pXml->addChild('ProductUrl', $product['url']);
		$pXml->addChild('ImageUrl', $product['image_url']);
		$pXml->addChild('Category', $product['category']);
		//Optional attributes
		$pXml->addChild('MerchantGroupNo', $product['parent_id']);
		$pXml->addChild('Size', $product['size']);
		$pXml->addChild('Color', $product['color']);
		$pXml->addChild('Type', $product['type']);

		$specsNode = $pXml->addChild('Specs');
		$meta = $product['meta'];
		foreach($product['attrs'] as $slug => $values) {
			$specsNode->addChildCData($slug, implode(',', $values));
		}

		$specsNode->addChild('Weight', $this->get($meta, '_weight'));
		$specsNode->addChild('Width', $this->get($meta, '_width'));
		$specsNode->addChild('Length', $this->get($meta, '_length'));
		$specsNode->addChild('Height', $this->get($meta, '_height'));
		
	}

	private function get($arr, $key) {
		return isset($arr[$key]) ? $arr[$key] : null;
	}

	private function getOrEmpty($arr, $key) {
		return isset($arr[$key]) ? $arr[$key] : [];
	}

	private function writeXML($xml) {
		ob_clean();
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
			$lookup[$a->product_id][$a->slug][] = $a->value;
		}

		return $lookup;
	}

	private function getMetaLookup() {
		global $wpdb;

		$p = $wpdb->posts;
		$pm = $wpdb->postmeta;

		$query = "
			SELECT $pm.* 
			FROM $pm
			INNER JOIN $p ON $p.id = $pm.post_id
			WHERE $p.post_status = 'publish' 
			AND $p.post_type IN ('product', 'product_variation')
			AND (
				$pm.meta_key LIKE '" . parent::PREFIX . "%'
				OR $pm.meta_key IN('_woocommerce_gpf_data', '_weight', '_length', '_height', '_width', '_sku')
			)
		";

		$lookup = array();

		$meta = $wpdb->get_results($query, OBJECT);

		foreach($meta as $item) {
			if(!isset($lookup[$item->post_id])) $lookup[$item->post_id] = array();
			$lookup[$item->post_id][$item->meta_key] = $item->meta_value;
		}

		return $lookup;
	}

	private function getProducts() {
		global $wpdb;

		$p = $wpdb->posts;
		
		$query = "
			SELECT $p.* 
			FROM $p
			WHERE $p.post_status = 'publish' 
			AND $p.post_type = 'product'
		";

		return $wpdb->get_results($query, OBJECT);
	}

	private function getProductVariationsLookup() {
		global $wpdb;

		$p = $wpdb->posts;
		
		$query = "
			SELECT $p.* 
			FROM $p
			WHERE $p.post_status = 'publish' 
			AND $p.post_type = 'product_variation'
		";

		$lookup = array();

		$variations = $wpdb->get_results($query, OBJECT);

		foreach($variations as $item) {
			if(!isset($lookup[$item->post_parent])) $lookup[$item->post_parent] = array();
			$lookup[$item->post_parent][] = $item;
		}

		return $lookup;
	}

	private function dd($any) {
		echo('<pre>');
		var_dump($any);
		echo('</pre>');
	}
}

