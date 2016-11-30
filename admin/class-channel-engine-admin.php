<?php

class Channel_Engine_Admin extends Channel_Engine_Base_Class {
    public function __construct(){
        //Hooks
 		add_action( 'woocommerce_admin_order_data_after_order_details', array($this, 'add_ce_fields_after_order_details'), 10, 1 );

		add_filter( 'woocommerce_admin_shipping_fields' , array($this, 'ce_additional_admin_shipping_fields' ));
    }
	
	function add_ce_fields_after_order_details($order){
		if(get_post_meta( $order->id, parent::PREFIX . '_order_id', true ) > 0){
			echo '<h4 style="float:left">ChannelEngine</h4>';
			echo '<p class="form-field form-field-wide"><strong>Order id:</strong><br>' . get_post_meta( $order->id, parent::PREFIX . '_order_id', true ) . '</p>';
			echo '<p class="form-field form-field-wide"><strong>Channel name:</strong><br>' . get_post_meta( $order->id, parent::PREFIX . '_channel_name', true ) . '</p>';
			echo '<p class="form-field form-field-wide"><strong>Channel Order No:</strong><br>' . get_post_meta( $order->id, parent::PREFIX . '_channel_order_no', true ) . '</p>';
			echo '<p class="form-field form-field-wide"><strong>Payment method:</strong><br>' . get_post_meta( $order->id, parent::PREFIX . '_payment_method', true ) . '</p>';
			
			$trackTrace = get_post_meta($order->id, '_shipping_ce_track_and_trace', true);
			if(empty($trackTrace)) $trackTrace = get_post_meta($order->id, 'TrackAndTraceBarCode', true);

			if(!empty($trackTrace)) {
				$trackTraceValue = $trackTrace;
			}else{
				$trackTraceValue = 'No Track & Trace set<br>Enter Track & Trace information at "Shipping Details"';
			}
			echo '<p class="form-field form-field-wide"><strong>Track & trace:</strong><br>' . $trackTraceValue . '</p>';
		}
	 }
	
	
	function ce_additional_admin_shipping_fields( $fields ) {
	        $fields['ce_track_and_trace'] = array(
	        	'type' => 'text',
	            'label' => __( 'ChannelEngine - Track & Trace', 'woocommerce' ),
	            'placeholder' => 'Track & trace'
	        );
	        return $fields;
	}
}

