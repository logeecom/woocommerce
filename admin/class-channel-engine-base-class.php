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