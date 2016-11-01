<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 17/09/15
 * Time: 14:45
 */

class Channel_Engine_Product_Validation extends Channel_Engine_Base_Class{

    public function __construct(){

        add_filter( 'manage_edit-product_columns', array($this,'add_channel_engine_validation_column') );
        add_action( 'manage_product_posts_custom_column', array($this, 'validate_channel_engine_product_column'), 2  );
    }

    public function add_channel_engine_validation_column($columns){

        $new_columns = $columns;
        $new_columns['channel_engine_validation_column'] = '<span class="channel_engine_product_column_image"></span>';

        return $new_columns;
    }

    public function validate_channel_engine_product_column($column){
        global $post;

        if ( $column == 'channel_engine_validation_column' ) {
            if( $this->validate_channel_engine_product($post->ID) ){
                echo '<span class="channel_engine_product_complete"></span>';
            }else{
                echo '<span class="channel_engine_product_incomplete"></span>';
            }
        }
    }

    /**
     * Check if all required fields contain a value.
     * */
    public function validate_channel_engine_product($post_id){
        $product = wc_get_product( $post_id );
        $product_meta = get_post_meta( get_the_ID() );

        //Name
        if( empty($product->post->post_title) ){ return false; }
        //Description
        if( empty(parent::get_product_description($post_id)) ){ return false; }
        //Price
        if( empty(parent::get_product_price($product)) ){ return false; }
        //ListPrice
        if( empty(parent::get_product_list_price($product)) ){ return false; }
        //PurchasePrice
        if( empty(parent::get_product_purchase_price($product)) ){ return false; }
        //VAT
        if( empty(parent::get_product_vat($product)) ){ return false; }
        //Stock
        if( empty(parent::get_product_stock($product)) ){return false;}
        //Brand
        if( empty($product_meta[parent::PREFIX.'_brand'][0]) ){return false;}
        //MerchantProductNo
        if( empty(parent::get_product_merchant_no($product))){return false;}
        //VendorProductNo
        if( empty($product_meta[parent::PREFIX.'_vendor_product_no'][0]) ){return false;}
        //GTIN
        if( empty($product_meta[parent::PREFIX.'_gtin'][0]) ){return false;}
        //ShippingCosts
        if( empty($product_meta[parent::PREFIX.'_shipping_costs'][0]) ){return false;}
        //ShippingTime
        if( empty($product_meta[parent::PREFIX.'_shipping_time'][0]) ){return false;}
        //ProductURL
        if( empty($product->post->guid) ){return false;}
        //ImageURL
        if( empty(wp_get_attachment_url(get_post_thumbnail_id($post_id))) ){return false;}
        //Category
        if( empty(parent::get_product_category( get_the_ID() )) ){return false;};

        return true;
    }
}