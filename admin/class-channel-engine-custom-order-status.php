<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 16/10/15
 * Time: 10:15
 */

class Channel_Engine_Custom_Order_Status extends Channel_Engine_Base_Class
{

    public function __construct(){

        add_action( 'init', array( $this,'register_returned_order_status') );
        add_filter( 'wc_order_statuses', array( $this,'add_returned_to_order_statuses') );
        add_action( 'wp_print_scripts', array( $this,'add_custom_order_status_icon') );

        add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_returned_order_action') , 2, 2);
    }

    public function add_returned_order_action($actions, $the_order){

        if($the_order->post_status == 'wc-returned') {
        	$accountName = get_option(parent::PREFIX.'_account_name');
			$channelEngineOrderId = get_post_meta( $the_order->id, parent::PREFIX . '_order_id', true );
            $actions['returned'] = array(
                'url'       => 'https://' . $accountName . '.channelengine.net/orders/view/' . $channelEngineOrderId,
                'name'      => 'Returned',
                'action'    => "returned"
            );
        }

        return $actions;
    }


    public function register_returned_order_status() {
        register_post_status( 'wc-returned', array(
            'label'                     => 'Returned',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Returned <span class="count">(%s)</span>', 'Returned <span class="count">(%s)</span>' )
        ) );
    }

    // Add to list of WC Order statuses
    public function add_returned_to_order_statuses( $order_statuses ) {

        $new_order_statuses = array();

        // add new order status after processing
        foreach ( $order_statuses as $key => $status ) {

            $new_order_statuses[ $key ] = $status;

            if ( 'wc-refunded' === $key ) {
                $new_order_statuses['wc-returned'] = 'Returned';
            }
        }

        return $new_order_statuses;
    }

    /**
     * Adds icons for any custom order statuses
     **/
    function add_custom_order_status_icon() {

        if( ! is_admin() ) {
            return;
        }

        ?> <style>
            /* Add custom status order icons */
            .column-order_status mark.returned{
                content: url('<?php echo plugin_dir_url(__FILE__) . 'images/returned_order.png'?>');
                width:1.3em;
                height:1.3em;
            }

            /* Repeat for each different icon; tie to the correct status */

        </style> <?php
    }

}