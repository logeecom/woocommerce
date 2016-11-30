<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 17/09/15
 * Time: 14:45
 */

class Channel_Engine_Product_Validation extends Channel_Engine_Base_Class{

    public function __construct(){

        //add_filter( 'manage_edit-product_columns', array($this,'add_channel_engine_validation_column') );
        //add_action( 'manage_product_posts_custom_column', array($this, 'validate_channel_engine_product_column'), 2  );
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

        
        return true;
    }
}