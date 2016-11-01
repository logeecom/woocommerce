<?php

use ChannelEngineApiClient\Models\Order;

class Channel_Engine_Tracking extends Channel_Engine_Base_Class{


    private $account_name;
    private $didEnqueueScript;

    function __construct($account_name)
    {
        $this->account_name = $account_name;

        add_action('woocommerce_thankyou', array( $this,'channel_engine_track_order') );
      	add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Load scripts
     */
    function enqueue_scripts(){
        wp_enqueue_script('channel_engine_tracking_script', plugin_dir_url(__FILE__) . 'js/channel-engine-tracking.js');
        wp_localize_script("channel_engine_tracking_script", "channel_engine_data", array('account_name' => $this->account_name));
    }

    /**
     * Track order when 'thank you' screen is entered.
     */
    function channel_engine_track_order($order_id){
        // wp_deregister_script('js/channel-engine-tracking.js');

        $wc_order = new WC_Order($order_id);
        $orderLinesArray = array();

        foreach($wc_order->get_items() as $lineItem){

            $productId = $lineItem['product_id'];
            $product = wc_get_product($productId);
            $category = parent::get_product_category($productId);

            $orderLine = array(
                'merchantProductNo' => $productId,
                'name' => $product->get_title(),
                'category' => $category,
                'price' => $product->get_price(),
                'qty' => intval($lineItem['qty'])
            );

            $orderLinesArray[] = $orderLine;
        }

        $orderArray = array(
            'merchantOrderNo' => "$wc_order->id",
            'total' => $wc_order->get_total(),
            'vat' => $wc_order->get_total_tax(),
            'shippingCost' => $wc_order->get_total_shipping(),
            'city' => $wc_order->billing_city,
            'country' => $wc_order->billing_country,
            'orderLines' => $orderLinesArray,
        );

        //Prevents the script from firing twice.
//        $this->didEnqueueScript = true;

        //Pass the orderArray to 'channel-engine-track-order.js'
        wp_enqueue_script( 'channel_engine_order_tracking_script', plugin_dir_url(__FILE__) . 'js/channel-engine-order-tracking.js' );
        wp_localize_script( "channel_engine_order_tracking_script", "channel_engine_data", array( 'account_name' => $this->account_name, 'order' => $orderArray) );
    }
}