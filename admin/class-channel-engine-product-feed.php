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
		$attrs_lookup = $this->getAttributeLookup();
		$meta_lookup = $this->getMetaLookup();

		foreach($products as $item) {
			$id = $item->ID;
			//Skip products with incomplete data
			// if(! $this->product_validation->validate_channel_engine_product($id)) continue;

			//Get product information
			$product = wc_get_product($id);
			$attributes = $product->get_attributes();
			$meta = $meta_lookup[$id];
			$attrs = $attrs_lookup[$id];

			$img_id = get_post_thumbnail_id($id);
			$product_image = wp_get_attachment_url($img_id);

			$product_category = parent::get_product_category($id); //Get product category name
			$description = parent::get_product_description($id); //Get description without html

			$post_title             = isset($item->post_title) ?  $item->post_title : null;
			$stock                  = parent::get_product_stock($product);
			$gtin                   = isset($meta[parent::PREFIX.'_gtin']) ? $meta[parent::PREFIX.'_gtin'] : null;
			$product_id             = isset($product->id) ?  $product->id : null;
			$product_url            = $product->get_permalink();

			$price                  = parent::get_product_price($product);
			$purchase_price         = parent::get_product_purchase_price($product);
			$list_price             = parent::get_product_list_price($product);
			$vat                    = parent::get_product_vat($product);
			$brand                  = isset($meta[parent::PREFIX.'_brand']) ? $meta[parent::PREFIX.'_brand'] : null;
			$vendor_product_no      = isset($meta[parent::PREFIX.'_vendor_product_no']) ?  $meta[parent::PREFIX.'_vendor_product_no'] : null;
			$shipping_costs         = isset($meta[parent::PREFIX.'_shipping_costs']) ?  $meta[parent::PREFIX.'_shipping_costs'] : null;
			$shipping_time          = isset($meta[parent::PREFIX.'_shipping_time']) ?  $meta[parent::PREFIX.'_shipping_time'] : null;
			$merchant_group_no      = parent::get_product_merchant_no($product);
			$size                   = isset($meta[parent::PREFIX.'_size']) ?  $meta[parent::PREFIX.'_size'] : null;
			$color                  = isset($meta[parent::PREFIX.'_color']) ?  $meta[parent::PREFIX.'_color'] : null;

			//Generate XML entities
			$product_xml = $xml->addChild('product');
			//Required attributes
			$product_xml->addChildCData('Name',              $post_title);
			$product_xml->addChildCData('Description', $description);
			$product_xml->addChild('Price',             $price);
			$product_xml->addChild('PurchasePrice',     $purchase_price);
			$product_xml->addChild('ListPrice',         $list_price);
			$product_xml->addChild('VAT',               $vat);
			$product_xml->addChild('Stock',             $stock);
			$product_xml->addChild('Brand',             $brand);
			$product_xml->addChild('MerchantProductNo', $product_id);
			$product_xml->addChild('VendorProductNo',   $vendor_product_no);
			$product_xml->addChild('GTIN',              $gtin);
			$product_xml->addChild('ShippingCosts',     $shipping_costs);
			$product_xml->addChild('ShippingTime',      $shipping_time);
			$product_xml->addChild('ProductUrl',        $product_url);
			$product_xml->addChild('ImageUrl',          $product_image);
			$product_xml->addChild('Category',          $product_category);
			//Optional attributes
			$product_xml->addChild('MerchantGroupNo',   $merchant_group_no);
			$product_xml->addChild('Size',              $size);
			$product_xml->addChild('Color',             $color);

			$specs = $product_xml->addChild('Specs');

			foreach($attrs as $slug => $values) {
				$specs->addChildCData($slug, implode(',', $values));
			}

			$specs->addChild('Weight', $meta['_weight']);
			$specs->addChild('Width', $meta['_width']);
			$specs->addChild('Length', $meta['_length']);
			$specs->addChild('Height', $meta['_height']);
			

		}

		$this->writeXML($xml);
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
			AND $p.post_type = 'product'
			AND (
				$pm.meta_key LIKE '" . parent::PREFIX . "%'
				OR $pm.meta_key IN('_weight', '_length', '_height', '_width', '_sku')
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

	private function dd($any) {
		echo('<pre>');
		var_dump($any);
		echo('</pre>');
	}
}

