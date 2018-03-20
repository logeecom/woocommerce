<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 21/09/15
 * Time: 15:58
 */


class Channel_Engine_Order_Cancelled {


    private $client;

    /**
     * Constructor
     */
    public function __construct($client) {

        $this->client = $client;
        add_action('woocommerce_order_status_cancelled', array($this,'post_shipment_cancelled_status') , 0, 1 );
    }

    public function post_shipment_cancelled_status($wc_order_id){

        require_once(plugin_dir_path(__FILE__) . 'class-channel-engine-api.php');

        $channel_engine_api_client = new Channel_Engine_API($this->client);
        $channel_engine_api_client->post_order_cancelled_status($wc_order_id);
    }

}