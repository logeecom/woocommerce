<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 22/09/15
 * Time: 12:11
 */

class Channel_Engine_Base_Class{

    const PREFIX = '_channel_engine';
    const PREFIX_ORDER_ERROR = 'ChannelEngine Plugin - An unexpected error occured : ';
    const ORDER_COMPLETE_SUCCESS = 'ChannelEngine Plugin - Order moved to Completed status  successfully';
    /**
     * Fetch product 'category chain'
     */
    public function get_product_category($product_id){

        $product_category = null;
        $terms = get_the_terms( $product_id, 'product_cat' );

        if (is_array($terms)) {

            $final_term = null;
            $total_ancestors = -1;

            //Get the category with the most parent categories by looping through each term.
            foreach ($terms as $term) {

                //Determine the number of ancestors of the current term
                $product_cat_id = $term->term_id;
                $parent_categories = get_ancestors( $product_cat_id, 'product_cat' );
                $ancestors_count = count($parent_categories);

                //When an ancestor has more parent categories than the previous one, set the final_term
                if($ancestors_count > $total_ancestors){
                    $total_ancestors = $ancestors_count;
                    $final_term = $term;
                }
            }
            
            //Set category name
            $product_category = $final_term->name;

            //Get category ancestors
            $product_cat_id = $final_term->term_id;
            $parent_categories = get_ancestors( $product_cat_id, 'product_cat' );

            if(count($parent_categories) > 0) {
                //Create a category chain when the category has parent categories.
                foreach ($parent_categories as $parent_category) {
                    //Concatenate product categories seperated by '>'
                    $category = get_term($parent_category, 'product_cat');
                    $product_category = $category->name.'>'.$product_category;
                }
            }
        }

        return $product_category;
    }

    public function get_base_tax_rate(){

        $tax = WC_TAX::get_base_tax_rates();
        $tax_rate = isset($tax[1]['rate']) ? $tax[1]['rate'] : null;

        return $tax_rate;
    }

 	public function get_product_name($post_id){

        $name = strip_tags(get_post($post_id)->post_title);

        return $name;
    }


    public function get_product_description($post_id){

        $description = strip_tags(get_post($post_id)->post_content);

        $saved_description = get_post_meta( $post_id, $this::PREFIX.'_description' );
        //Restore the saved values
        if( isset( $saved_description[0]  ) ) {
            $description = $saved_description[0];
        }

        return $description;
    }
	
	public function get_product_price($product){

        $product_price = $product->get_price_including_tax();
       
        return $product_price;
    }
	
	public function get_product_price_formatted($product){
		
		return wc_format_localized_price($this->get_product_price($product));
	}

    public function get_product_list_price($product){

        $list_price = $product->get_price_including_tax(1, $product->get_regular_price());
        //Check if any saved values are present
        $saved_list_price = get_post_meta( $product->id, $this::PREFIX.'_list_price' );
        //Restore the saved values
        if( isset( $saved_list_price[0]  ) ) {
            $list_price = $saved_list_price[0];
        }
		
        return $list_price;
    }
	
	public function get_product_list_price_formatted($product){
		
		return wc_format_localized_price($this->get_product_list_price($product));
	}

    public function get_product_purchase_price($product){

        $purchase_price = $product->get_price_excluding_tax(1, $product->get_regular_price());
        //Check if any saved values are present
        $saved_purchase_price = get_post_meta( $product->id, $this::PREFIX.'_purchase_price' );
        //Restore the saved values
        if( isset( $saved_purchase_price[0]  ) ) {
            $purchase_price = $saved_purchase_price[0] ;
        }

        //Replace comma's with dots, that is what ChannelEngine demands.
        $purchase_price = str_replace(',', '.',$purchase_price);

        return $purchase_price;
    }

	public function get_product_purchase_price_formatted($product){
		
		return wc_format_localized_price($this->get_product_purchase_price($product));
	}

    public function get_product_vat($product){

        $taxes = WC_TAX::get_base_tax_rates($product->get_tax_class());
		$tax_rate = 0;
		if(count($taxes)){
			$tax_rate = isset($taxes[1]['rate']) ? $taxes[1]['rate'] : null;
		}
        // //Check if any saved values are present
        // $saved_vat = get_post_meta( $product->id, $this::PREFIX.'_vat' );
        // //Restore the saved values
        // if( isset( $saved_vat[0]  ) ) {
            // $vat = $saved_vat[0] ;
        // }

        return $tax_rate;
    }
	
	public function get_product_vat_formatted($product){
        return wc_format_localized_decimal($this->get_product_vat($product));
    }
	
	
	public function get_product_stock($product){
		// if stock is managed, always use stock
		if($product->managing_stock()){
			return $product->get_stock_quantity();
		}
		$saved_default_stock = get_post_meta( $product->id, $this::PREFIX.'_stock' );
        //Restore the saved values
        $stock = 1;
        if( isset( $saved_default_stock[0]  ) ) {
            $stock = $saved_default_stock[0] ;
        }
		return wc_stock_amount($stock);
    }
	public function get_product_SKU($product){
		return $product->get_sku();
    }
	
	public function get_product_URL($product){
		return $product->get_permalink();
	}
	
	public function get_product_image_URL($post_id){
		return wp_get_attachment_url( get_post_thumbnail_id($post_id) );
	}
	
	public function get_product_shipping($product){
		$shipping_fee = 0; // default
		
		$shipping_settings = get_option('woocommerce_channel_engine_settings');
		if(isset($shipping_settings['fee'])){
			$shipping_fee = $shipping_settings['fee'];
		}
		
		$saved_shipping_fee = get_post_meta( $product->id, $this::PREFIX.'_shipping_costs' );
        //Restore the saved values
        if( isset( $saved_shipping_fee[0]  ) ) {
            $shipping_fee = $saved_shipping_fee[0] ;
        }
		return $shipping_fee;
	}
	
	public function get_product_shipping_time($product){
		$shipping_time = ''; // default
		
		$shipping_settings = get_option('woocommerce_channel_engine_settings');
		if(isset($shipping_settings['time'])){
			$shipping_time = $shipping_settings['time'];
		}
		
		$saved_shipping_time = get_post_meta( $product->id, $this::PREFIX.'_shipping_time' );
        //Restore the saved values
        if( isset( $saved_shipping_time[0]  ) ) {
            $shipping_time = $saved_shipping_time[0] ;
        }
		return $shipping_time;
	}
	
	public function get_product_merchant_no($product){
		// required
		return $this::get_product_SKU($product);
	}
}