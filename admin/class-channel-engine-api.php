<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 17/09/15
 * Time: 14:41
 */

// Import the required namespaces
use ChannelEngineApiClient\Client\ApiClient;
use ChannelEngineApiClient\Enums\OrderStatus;
use ChannelEngineApiClient\Enums\MancoReason;
use ChannelEngineApiClient\Enums\ShipmentStatus;
use ChannelEngineApiClient\Enums\ShipmentLineStatus;
use ChannelEngineApiClient\Models\Order;
use ChannelEngineApiClient\Models\OrderLine;
use ChannelEngineApiClient\Models\Shipment;
use ChannelEngineApiClient\Models\ShipmentLine;
use ChannelEngineApiClient\Helpers\Collection;


class Channel_Engine_API extends Channel_Engine_Base_Class{

    private $client;

    public function __construct(ApiClient $client){
        $this->client = $client;
    }

    public function fetch_returns(){

        try{
            $result = $this->client->getReturns();
        }catch(Exception $e){
            error_log( print_r( $e, true ) );
        }

        foreach($result as $returnOrder){

            $args = array(
                'post_type'     => 'shop_order',
                'posts_per_page' => -1,
                'post_status'   => 'any',
                'meta_query'    => array(
                    array(
                        'key' => parent::PREFIX . '_order_id',
                        'value' => $returnOrder->getOrderId()
                    )
                )
            );

            $query = new WP_Query( $args );

            while ( $query->have_posts() ) {
                $query->the_post();

                //Get wc_order based on orderID
                $orderID = $query->post->ID; // $orderID = get_the_ID();
                $wc_order = new WC_Order($orderID);

                $wc_order->update_status('wc-returned', '', false);
                if( $returnOrder->getReason() )  {
                    $wc_order->add_order_note('Channel Engine - The order has been returned, reason: ' . $returnOrder->getReason());
                }else{
                    $wc_order->add_order_note('Channel Engine - The order has been returned');
                }
                echo 'Setting order with order_id '.$orderID.' to order status returned';
            }
            wp_reset_postdata();
        }
    }

    public function post_shipment_complete_status($wc_order_id) {

        $order = new WC_Order($wc_order_id);
        $ceOrderId = get_post_meta($order->id, parent::PREFIX . '_order_id', true);

        if(!$ceOrderId) return;

        $shipment = new Shipment();
        $shipment->setOrderId(intval($ceOrderId));

		$trackTrace = get_post_meta($order->id, '_shipping_ce_track_and_trace', true);
        if(empty($trackTrace)) $trackTrace = get_post_meta($order->id, 'TrackAndTraceBarCode', true);
        $shipment->setTrackTraceNo($trackTrace);
        $shipment->setMerchantShipmentNo($order->id);

        $shipmentLines = $shipment->getLines();
        foreach ($order->get_items() as $wc_line_item_id => $lineItem) {
            //Create shipment lines
            $shipmentLine = new ShipmentLine();

            $productId = $lineItem['product_id'];
            $order_line_id = wc_get_order_item_meta($wc_line_item_id, parent::PREFIX.'_channel_order_line_id');
            $qty = $lineItem['qty'];
            $shipmentLine->setOrderLineId(intval($order_line_id));
            $shipmentLine->setStatus(ShipmentLineStatus::SHIPPED);
            $shipmentLine->setQuantity(intval($qty));
            $shipmentLines->append( $shipmentLine );
        }

        try{
            //Post shipment status to channel engine
            $this->client->postShipment($shipment);
            $order->add_order_note( parent::ORDER_COMPLETE_SUCCESS );
        }catch(Exception $e){
            //Add note to order that specifies the exception
            $order->add_order_note( parent::PREFIX_ORDER_ERROR.$e->getMessage() );
            error_log( print_r( $e, true ) );
        }
    }

    /**
     * Fetch orders from channel engine
     */
    public function import_orders(){
        $results = array(
            'Success' => array(),
            'Failed' => array(),
            'Exception' => null
        );
        $orders = [];

        try{
            //Get all orders
            $orders = $this->client->getOrders(
                array(
                    OrderStatus::IN_PROGRESS,
                    OrderStatus::NEW_ORDER
                )
            );
        }catch(Exception $e){
            //Write exception to error log
            error_log( print_r( $e, true ) );
            $results['Exception'] = $e->getMessage();
        }

        
		$ordersImported = array();
		$ordersNotImported = array();

        foreach($orders as $order)
        {
        	$currentChannelOrderId = $order->getId();
            $args = array(
                'post_type'     => 'shop_order',
                'posts_per_page' => -1,
                'post_status'   => 'any',
                'meta_query'    => array(
                    array(
                        'key' => parent::PREFIX . '_order_id',
                        'value' => $currentChannelOrderId
                    )
                )
            );

            //Check if the order already exist by fetching the order by the channel engine order number
            $order_exists = false;
            $query = new WP_Query( $args );
			while ( $query->have_posts() ) {
				$query->the_post();
				$orderID = $query->post->ID; // $orderID = get_the_ID();
				if($orderID){
					$order_exists = true;
				}
				
				$results['Failed'][] = array(
					'Succes' => false,
					'OrderId' => $currentChannelOrderId,
					'Message' => 'Order with OrderId ' . $order->getId() . ' already exists, order has not been imported!'
				);
			}
            wp_reset_postdata();
           
		    //Only create order when it does not exist yet
            if(!$order_exists) {
                $result = $this->create_order($order);
				if($result['Success']){
					// order imported, increment successfull import counter
					$results['Success'][] = $result;
				}else{
					$results['Failed'][] = $result;
				}
            }
        }
		echo(json_encode($results));
    }

    /**
     * Parse order object to woocommerce specific data
     */
    public function create_order(Order $order)
    {

        //Check if the order is valid by checking if the products exist.
        $productExists = true;
		$productMismatches = array();
        foreach ($order->getLines() as $orderLine) {

            $product = wc_get_product( $orderLine->getMerchantProductNo() );
            if(!$product){
                $productExists = false;
				$productMismatches[] = $orderLine->getMerchantProductNo();
            }
        }

        //TODO:: Write order failure to error log?
        if($productExists) {

            //Create billing address
            $ba = $order->getBillingAddress();

            //Create initial order data
            $order_data = array(
                'post_name' => 'order-' . $order->getOrderDate(),
                'post_type' => 'shop_order',
                'post_title' => 'Order &ndash; ' . $order->getOrderDate(),
                'post_status' => $order->getStatus(),
                'ping_status' => 'closed',
                'post_author' => $ba->getFirstName(),
                'post_date' => $order->getOrderDate(),
                'comment_status' => 'open'
            );

            $billingAddress = array(
                'first_name' => $ba->getFirstName(),
                'last_name' => $ba->getLastName(),
                'company' => $ba->getCompanyName(),
                'address_1' => $this->create_address($ba->getStreetName(), $ba->getHouseNr(), $ba->getHouseNrAddition()),
                'address_2' => '',
                'city' => $ba->getCity(),
                'state' => '',
                'postcode' => $ba->getZipCode(),
                'country' => $ba->getCountryIso(),
                'email' => $order->getEmail(),
                'phone' => $order->getPhone()
            );

            //Create shipping address
            $sa = $order->getShippingAddress();
            $shippingAddress = array(
                'first_name' => $sa->getFirstName(),
                'last_name' => $sa->getLastName(),
                'company' => $sa->getCompanyName(),
                'address_1' => $this->create_address($sa->getStreetName(), $sa->getHouseNr(), $sa->getHouseNrAddition()),
                'address_2' => '',
                'city' => $sa->getCity(),
                'state' => '',
                'postcode' => $sa->getZipCode(),
                'country' => $sa->getCountryIso(),
                'email' => $order->getEmail(),
                'phone' => $order->getPhone()
            );

            //Create the wc_order
            $wc_order = wc_create_order($order_data);
            $wc_order->set_address($billingAddress, 'billing');
            $wc_order->set_address($shippingAddress, 'shipping');

            //Woocommerce Payment method can only be set if payment method is active and matches string from ChannelEngine 
            //$wc_order->set_payment_method($order->getPaymentMethod());

            //Add the product lines to our wc_order
            foreach ($order->getLines() as $orderLine) {
                //TODO:: Lower the product stock rate
                $wc_product = wc_get_product($orderLine->getMerchantProductNo());
				$productLineArgs = array('totals'=>
					array(
						'subtotal' => $orderLine->getLineTotalInclVat() - $orderLine->getLineVat(),
						'total' => $orderLine->getLineTotalInclVat() - $orderLine->getLineVat(),
						'subtotal_tax' => $orderLine->getLineVat(),
						'total_tax' => $orderLine->getLineVat()
					)
				);
                $lineItemId = $wc_order->add_product($wc_product, $orderLine->getQuantity(), $productLineArgs);

                //Set channel engine product number on the fetch wc_product
                update_post_meta($wc_product->id, parent::PREFIX.'_channel_product_no', $orderLine->getChannelProductNo());
                //Set order line id on the wc_order
                //TODO::update_post_meta on $lineItemId bugs out in some occasions, what is happening here?
                wc_add_order_item_meta( $lineItemId, parent::PREFIX.'_channel_order_line_id', $orderLine->getId(), true );
            }

            //Order meta
            update_post_meta($wc_order->id, parent::PREFIX . '_order_id', $order->getId());
            update_post_meta($wc_order->id, parent::PREFIX . '_coc_no', $order->getCocNo());
            update_post_meta($wc_order->id, parent::PREFIX . '_vat_no', $order->getVatNo());
            update_post_meta($wc_order->id, parent::PREFIX . '_order_date', $order->getOrderDate());
            update_post_meta($wc_order->id, parent::PREFIX . '_created_at', $order->getCreatedAt());
            update_post_meta($wc_order->id, parent::PREFIX . '_updated_at', $order->getUpdatedAt());
            update_post_meta($wc_order->id, parent::PREFIX . '_channel_id', $order->getChannelId());
            update_post_meta($wc_order->id, parent::PREFIX . '_channel_order_no', $order->getChannelOrderNo());
            update_post_meta($wc_order->id, parent::PREFIX . '_channel_customer_no', $order->getChannelCustomerNo());
            update_post_meta($wc_order->id, parent::PREFIX . '_channel_name', $order->getChannelName());
            update_post_meta($wc_order->id, parent::PREFIX . '_do_send_mails', $order->getDoSendMails());
            update_post_meta($wc_order->id, parent::PREFIX . '_can_ship_partial_order_lines', $order->getCanShipPartialOrderLines());
            update_post_meta($wc_order->id, parent::PREFIX . '_merchant_id', $order->getMerchantId());
            update_post_meta($wc_order->id, parent::PREFIX . '_merchant_order_no', $order->getMerchantOrderNo());
            update_post_meta($wc_order->id, parent::PREFIX . '_shipping_costs_incl_vat', $order->getShippingCostsInclVat());
            update_post_meta($wc_order->id, parent::PREFIX . '_shipping_costs_vat', $order->getShippingCostsVat());
            update_post_meta($wc_order->id, parent::PREFIX . '_sub_total_vat', $order->getSubTotalVat());
            update_post_meta($wc_order->id, parent::PREFIX . '_sub_total_incl_vat', $order->getSubTotalInclVat());
            update_post_meta($wc_order->id, parent::PREFIX . '_total_incl_vat', $order->getTotalInclVat());
            update_post_meta($wc_order->id, parent::PREFIX . '_total_vat', $order->getTotalVat());
            update_post_meta($wc_order->id, parent::PREFIX . '_refund_incl_vat', $order->getRefundInclVat());
            update_post_meta($wc_order->id, parent::PREFIX . '_refund_excl_vat', $order->getRefundExclVat());
            update_post_meta($wc_order->id, parent::PREFIX . '_status', $order->getStatus());
            update_post_meta($wc_order->id, parent::PREFIX . '_closed_date', $order->getClosedDate());
            update_post_meta($wc_order->id, parent::PREFIX . '_max_vat_rate', $order->getMaxVatRate());
			update_post_meta($wc_order->id, parent::PREFIX . '_payment_method', $order->getPaymentMethod());

			// Woocommerce data
			update_post_meta($wc_order->id, '_customer_ip_address', '');
			update_post_meta($wc_order->id, '_shipping_ce_track_and_trace', '');

            if($this->is_plugin_active('woocommerce_wuunder/woocommerce-wuunder.php'))
            {
                update_post_meta($wc_order->id, '_shipping_street_name', $sa->getStreetName());
                update_post_meta($wc_order->id, '_shipping_house_number', $sa->getHouseNr());
                update_post_meta($wc_order->id, '_shipping_house_number_suffix', $sa->getHouseNrAddition());
            }


			$wc_order->order_date = $order->getOrderDate();
			$wc_order->payment_complete();

			$wc_order->calculate_taxes();

			$wc_order->set_total($order->getShippingCostsInclVat(),'shipping');
			$wc_order->set_total($order->getShippingCostsVat(),'shipping_tax');
			$wc_order->set_total($order->getTotalVat(),'tax');
			$wc_order->set_total($order->getTotalInclVat(),'total');
			
			
            //Extra data
            //TODO: Should these be parsed to other objects?
            update_post_meta($wc_order->id, parent::PREFIX . '_extra_data', serialize($order->getExtraData()));
            update_post_meta($wc_order->id, parent::PREFIX . '_shipments', serialize($order->getShipments()));
            update_post_meta($wc_order->id, parent::PREFIX . '_cancellations', serialize($order->getCancellations()));
			return array(
				'Success'=>true,
				'OrderId'=>$order->getId(),
				'MerchantOrderNo'=>$wc_order->get_order_number()
			);
        }
        else{
        	// products mismatch
        	$errorMessage = 'Products with ID [' . implode($productMismatches,',') . '] in order ' . $order->getId() . ' do not exist anymore, order has not been imported!';
        	error_log($errorMessage);
			return array(
				'Success'=>false,
				'OrderId'=>$order->getId(),
				'Message'=>$errorMessage
			);
        }
    }

    function is_plugin_active($plugin) {
        $plugins = (array) get_option('active_plugins', array());
        return in_array($plugin, $plugins);
    }

	// function to concatenate streetname, housenr and addition, seperated by spaces
	function create_address($address, $houseNr, $houseNrAddition){
		return $address . (strlen($houseNr)?(' '.$houseNr):'') . (strlen($houseNrAddition)?(' '.$houseNrAddition):'');
	}
}