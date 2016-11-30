<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 17/09/15
 * Time: 10:44
 */

class Channel_Engine_Product_Tab extends Channel_Engine_Base_Class{

	private $product_validation;

    /**
     * Construct
     */
    public function __construct($product_validation){
    	$this->product_validation = $product_validation;
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_script'));
		add_action( 'wp_ajax_ce_admin_refresh_description', array( $this, 'refresh_description' ));
        add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'woo_add_custom_admin_product_tab' ) , 1);
        add_action( 'woocommerce_product_data_panels',      array( $this, 'woo_add_custom_general_fields' ) );
        add_action( 'woocommerce_process_product_meta',     array( $this, 'woo_add_custom_general_fields_save' ));
		//add_action( 'admin_notices',     					array( $this, 'add_admin_notice' )); 

        add_action('woocommerce_product_after_variable_attributes', array($this, 'woo_add_custom_variation_fields'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'woo_add_custom_variation_fields_save'), 10, 2);
    }

	function add_admin_notice(){
		global $woocommerce, $post, $pagenow;
		 if ( $pagenow == 'post.php' && ('product' == get_post_type())) {
			if(!$this->product_validation->validate_channel_engine_product($post->ID)){
				// not valid, show admin notice
				$class = "error";
				$message = "Not all required ChannelEngine fields are set, this product will not show up in the feed!";
			    echo"<div class=\"$class\"> <p>$message</p></div>"; 
			}
		 }
	}

	function refresh_description() {
		if(check_ajax_referer( 'ce_admin_refresh_description_' . $_POST['post_id'], 'ce_security' )){
			$post_id = intval($_POST['post_id']);
			return wp_send_json(array('description'=>strip_tags($_POST['content'])));	
		}
		wp_die('Failed');
	}

	function enqueue_script($hook){
		// use only for product page
	    if ( ('post.php' != $hook) ||  ('product' != get_post_type())) {
	        return;
	    }
        //Below files only get included on the product tab
		wp_enqueue_script( 'channel-engine-admin-script', plugins_url( '/js/channel-engine-admin.js', __FILE__ ), array('jquery') );
		wp_localize_script( 'channel-engine-admin-script', 'ce_admin_data',
            array( 
            	'ajax_url' => admin_url( 'admin-ajax.php' ), // callback URL
             	'action'=>'ce_admin_refresh_description', // callback action
             	'post_id'=>get_the_ID(),
             	'ce_security'=>wp_create_nonce( 'ce_admin_refresh_description_' . get_the_ID() )
			)
		);
	}

    /**
     * Add channel engine product tab
     */
    function woo_add_custom_admin_product_tab() {

        ?>
            <li class="channel_engine_product_display_tab"><a href="#channel_engine_display_data"><?php _e('ChannelEngine', 'woocommerce'); ?></a></li>
        <?php
    }

    /**
     * Add custom channel engine fields to variations
     */
    function woo_add_custom_variation_fields($loop, $variation_data, $variation) {
        $pr = parent::PREFIX;

        echo '<div>';
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => $pr.'_gtin['.$variation->ID.']',
                        'label'       => __( 'GTIN', 'woocommerce' ),
                        'placeholder' => '',
                        'class'       => 'short channel_engine_input',
                        'style'       => 'width:100%;',
                        'description' => __( 'Product GTIN (EAN, ISBN, UPC), e.g. 8710400311140', 'woocommerce' ),
                        'value'       => get_post_meta($variation->ID, $pr.'_gtin', true)
                    )
                );
            echo '</div>';
    }

    /**
     * Add custom channel engine fields to variations
     */
    function woo_add_custom_variation_fields_save($variation_id, $i) {

        $pr = parent::PREFIX;

        $field = $_POST[$pr.'_gtin'][$variation_id];
        if (!empty($field)) {
            update_post_meta($variation_id, $pr.'_gtin', esc_textarea(strip_tags($field)));
        } else {
            delete_post_meta($variation_id, $pr.'_gtin');
        }
    }

    /**
     * Add custom channel engine fields
     */
    function woo_add_custom_general_fields() {

        global $woocommerce, $post;

        $product = wc_get_product($post->ID);

        echo '<div id="channel_engine_display_data" class="panel woocommerce_options_panel">';

            echo '<div class="options_group">';
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_gtin',
                        'label'       => __( 'GTIN', 'woocommerce' ),
                        'placeholder' => '',
                        'class'       => 'short channel_engine_input',
                        'custom_attributes'   => array(),
                        'description' => __( 'Product GTIN (EAN, ISBN, UPC), e.g. 8710400311140', 'woocommerce' )
                    )
                );
            echo '</div>';
            echo '<div class="options_group">';
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_brand',
                        'label'       => __( 'Brand', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'custom_attributes'	  => array(),
                        'description' => __( 'Product Brand Name', 'woocommerce' ),
                    )
                );
            echo '</div>';
            echo '<div class="options_group">';
                // Textarea
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_shipping_costs',
                        'label'       => __( 'Shipping Costs', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'custom_attributes'	  => array(),
                        'description' => __( 'Product Shipping Costs', 'woocommerce' ),
                        'value'       => parent::get_product_shipping($product)
                    )
                );
            echo '</div>';

            echo '<div class="options_group">';
                // Textarea
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_shipping_time',
                        'label'       => __( 'Shipping Time', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'custom_attributes'	  => array(),
                        'description' => __( 'Delivery Time Indication, e.g. Ordered before 22:00: Shipped Today', 'woocommerce' ),
                        'value'       => parent::get_product_shipping_time($product)
                    )
                );
            echo '</div>';

            echo '<div class="options_group">';
                // Textarea
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_size',
                        'label'       => __( 'Size', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'Product Size, e.g. XL', 'woocommerce' ),
                    )
                );
            echo '</div>';
            echo '<div class="options_group">';
                // Textarea
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_color',
                        'label'       => __( 'Color', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'Product Color, e.g. Black', 'woocommerce' ),
                    )
                );
            echo '</div>';

        echo '</div>';
    }

    /**
     * Save values
     */
    function woo_add_custom_general_fields_save($post_id) {

        $pr = parent::PREFIX;

        if( !empty( $_POST[ parent::PREFIX.'_gtin' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_gtin', esc_attr( $_POST[parent::PREFIX.'_gtin' ] ) );
        }else{
            delete_post_meta($post_id, parent::PREFIX.'_gtin');
        }

        if( !empty( $_POST[ parent::PREFIX.'_brand' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_brand', esc_attr( $_POST[parent::PREFIX.'_brand' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_brand');
        }

        if( !empty( $_POST[ parent::PREFIX.'_shipping_costs' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_shipping_costs', esc_attr( $_POST[ parent::PREFIX.'_shipping_costs' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_shipping_costs');
        }

        if( !empty( $_POST[ parent::PREFIX.'_shipping_time' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_shipping_time', esc_attr( $_POST[ parent::PREFIX.'_shipping_time' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_shipping_time');
        }

        if( !empty( $_POST[ parent::PREFIX.'_size'] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_size', esc_attr( $_POST[ parent::PREFIX.'_size' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_size');
        }

        if( !empty( $_POST[ parent::PREFIX.'_color'] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_color', esc_attr( $_POST[ parent::PREFIX.'_color' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_color');
        }
    }
}