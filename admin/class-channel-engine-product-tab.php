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
		add_action( 'admin_notices',     					array( $this, 'add_admin_notice' )); 
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
     * Add custom channel engine fields
     */
    function woo_add_custom_general_fields() {

        global $woocommerce, $post;

        $product = wc_get_product($post->ID);

        echo '<div id="channel_engine_display_data" class="panel woocommerce_options_panel">';
            echo'<h2>&nbsp;&nbsp;Required attributes</h2>';
			echo '<div class="options_group">';
                // Text input readonly
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_name',
                        'label'       => __( 'Name', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'Product name' ),
                        'custom_attributes'	  => array('readonly'=>'readonly', 'title'=>__('Copied from WooCommerce \'Product Name\'')),
                        'value'       => parent::get_product_name($post->ID)
                    )
                );
            echo '</div>';
            echo '<div class="options_group">';
            // Refresh button
            $this->refresh_description_button();
            // Textarea
            woocommerce_wp_textarea_input(
                array(
                    'id'          => parent::PREFIX.'_description',
                    'label'       => __( 'Description', 'woocommerce' ),
                    'placeholder' => '',
                    'class'		  => 'short channel_engine_textarea',
                    'description' => __( 'Plaintext Product Description (No HTML)' ),
                    'custom_attributes'	  => array('title'=>__('Can be copied from WooCommerce \'Product Content\'')),
                    'style'       => 'height:100px;',
                    'value'       => parent::get_product_description($post->ID)
                )
            );
			
            echo '</div>';
			
			echo '<div class="options_group">';
                // Text input 
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_price',
                        'label'       => __( 'Price', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'Price (Including VAT)' ),
                        'custom_attributes'	  => array('readonly'=>'readonly', 'title'=>__('Copied from WooCommerce \'Active Price\'')),
                        'value'       => parent::get_product_price_formatted($product)
                    )
                );
            echo '</div>';
			
            echo '<div class="options_group">';
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_list_price',
                        'label'       => __( 'List Price', 'woocommerce' ),
                        'data_type'	  => 'price',
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'Manufacturer Suggested Retail Price (Including VAT)', 'woocommerce' ),
                        'custom_attributes'	  => array('title'=>__('Copied from WooCommerce \'Regular Price\'')),
                        'value'       => parent::get_product_list_price($product)
                    )
                );
            echo '</div>';

            echo '<div class="options_group">';
                // Textarea
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_purchase_price',
                        'label'       => __( 'Purchase Price', 'woocommerce' ),
                        'data_type'	  => 'price',
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'Product Purchase Price (Excluding VAT)', 'woocommerce' ),
                        'custom_attributes'	  => array('title'=>__('Copied from WooCommerce \'Regular Price\' (Excluding VAT)')),
                        'value'       => parent::get_product_purchase_price($product)
                    )
                );
            echo '</div>';

            echo '<div class="options_group">';
                // Textarea
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_vat',
                        'label'       => __( 'VAT', 'woocommerce' ),
                        'data_type'	  => 'decimal',
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'VAT Percentage', 'woocommerce' ),
                        'custom_attributes'	  => array('readonly'=>'readonly','title'=>__('Copied from WooCommerce Product Tax')),
                        'value'       => parent::get_product_vat_formatted($product)
                    )
                );
            echo '</div>';
			echo '<div class="options_group">';
                // Textarea
                $custom_attr = $product->managing_stock()?array('readonly'=>'readonly','title'=>__('Copied from WooCommerce Stock Qty')):array('title'=>__('Stock not managed by WooCommerce, set stock manually'));
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_stock',
                        'label'       => __( 'Stock', 'woocommerce' ),
                        'data_type'	  => 'stock',
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'Product Stock', 'woocommerce' ),
                        'custom_attributes'	  => $custom_attr,
                        'value'       => parent::get_product_stock($product)
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
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_merchant_product_no',
                        'label'       => __( 'Merchant Product No', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'Your Unique Product Number', 'woocommerce' ),
                        'custom_attributes'	  => array('readonly'=>'readonly','title'=>__('Copied from WooCommerce SKU')),
                        'value'       => parent::get_product_merchant_no($product)
                    )
                );
            echo '</div>';
            
            echo '<div class="options_group">';
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_vendor_product_no',
                        'label'       => __( 'Vendor Product No', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'custom_attributes'	  => array(),
                        'description' => __( 'Manufacturer / Supplier Product Number, e.g. FTI-BLK-XL', 'woocommerce' )
                    )
                );
            echo '</div>';
			
			echo '<div class="options_group">';
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_gtin',
                        'label'       => __( 'GTIN', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'custom_attributes'	  => array(),
                        'description' => __( 'Product GTIN (EAN, ISBN, UPC), e.g. 8710400311140', 'woocommerce' )
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
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_product_url',
                        'label'       => __( 'Product URL', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'custom_attributes'	  => array('readonly'=>'readonly'),
                        'description' => __( 'Deep link to the product\'s details page', 'woocommerce' ),
                        'value'		  => parent::get_product_URL($product)
                    )
                );
            echo '</div>';
			echo '<div class="options_group">';
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_product_image_url',
                        'label'       => __( 'Image URL', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'custom_attributes'	  => array('readonly'=>'readonly'),
                        'description' => __( 'Deeplink to the product\'s image', 'woocommerce' ),
                        'value'		  => parent::get_product_image_URL($post->ID)
                    )
                );
            echo '</div>';
			echo '<div class="options_group">';
                // Text input
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_category',
                        'label'       => __( 'Category', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'custom_attributes'	  => array('readonly'=>'readonly'),
                        'description' => __( 'The product\'s full category path (each category separated by  > )', 'woocommerce' ),
                        'value'		  => parent::get_product_category($post->ID)
                    )
                );
            echo '</div>';
            echo'<h2>&nbsp;&nbsp;Optional attributes</h2>';

            echo '<div class="options_group">';
            // Textarea
            woocommerce_wp_hidden_input(
                array(
                    'id'          => parent::PREFIX.'_channel_product_no',
                    'label'       => __( 'Channel Product No', 'woocommerce' ),
                    'placeholder' => '',
                    'class'		  => 'short channel_engine_input',
                    'description' => __( 'Channel Engine identifier', 'woocommerce' ),
                )
            );
            echo '</div>';

            echo '<div class="options_group">';
                // Textarea
                woocommerce_wp_text_input(
                    array(
                        'id'          => parent::PREFIX.'_merchant_group_no',
                        'label'       => __( 'Merchant Group No', 'woocommerce' ),
                        'placeholder' => '',
                        'class'		  => 'short channel_engine_input',
                        'description' => __( 'The number that groups product variants together', 'woocommerce' ),
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

        if( !empty( $_POST[ parent::PREFIX.'_description' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_description', esc_textarea( strip_tags($_POST[ parent::PREFIX.'_description' ]) ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_description');
        }

        if( !empty( $_POST[ parent::PREFIX.'_list_price' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_list_price', esc_attr( $_POST[ parent::PREFIX.'_list_price' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_list_price');
        }

        if( !empty( $_POST[ parent::PREFIX.'_purchase_price' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_purchase_price', esc_attr( $_POST[ parent::PREFIX.'_purchase_price' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_purchase_price');
        }

        if( !empty( $_POST[ parent::PREFIX.'_stock' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_stock', esc_attr( $_POST[ parent::PREFIX.'_stock' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_stock');
        }

        if( !empty( $_POST[ parent::PREFIX.'_brand' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_brand', esc_attr( $_POST[parent::PREFIX.'_brand' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_brand');
        }
 		
 		if( !empty( $_POST[ parent::PREFIX.'_gtin' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_gtin', esc_attr( $_POST[parent::PREFIX.'_gtin' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_gtin');
        }

        if( !empty( $_POST[ parent::PREFIX.'_vendor_product_no' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_vendor_product_no', esc_attr( $_POST[ parent::PREFIX.'_vendor_product_no' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_vendor_product_no');
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

        if( !empty( $_POST[ parent::PREFIX.'_channel_product_no' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_channel_product_no', esc_attr( $_POST[ parent::PREFIX.'_channel_product_no' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_channel_product_no');
        }

        if( !empty( $_POST[ parent::PREFIX.'_merchant_group_no' ] ) ) {
            update_post_meta( $post_id, parent::PREFIX.'_merchant_group_no', esc_attr( $_POST[ parent::PREFIX.'_merchant_group_no' ] ) );
        }else{
        	delete_post_meta($post_id, parent::PREFIX.'_merchant_group_no');
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

	function refresh_description_button(){
		echo '<input id="ce-admin-refresh-description-button" type="button" class="button channel-engine-refresh-button" value="' . __( 'Copy from content') . '">';
	}
}