<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 17/09/15
 * Time: 14:41
 */

// Import the required namespaces
use ChannelEngine\Merchant\ApiClient\Model\MerchantOrderResponse;
use ChannelEngine\Merchant\ApiClient\Api\OrderApi;
use ChannelEngine\Merchant\ApiClient\Api\ReturnApi;
use ChannelEngine\Merchant\ApiClient\Api\ShipmentApi;
use ChannelEngine\Merchant\ApiClient\Api\CancellationApi;
use ChannelEngine\Merchant\ApiClient\Model\MerchantReturnResponse;
use ChannelEngine\Merchant\ApiClient\Model\MerchantShipmentRequest;
use ChannelEngine\Merchant\ApiClient\Model\MerchantShipmentTrackingRequest;
use ChannelEngine\Merchant\ApiClient\Model\MerchantShipmentLineRequest;
use ChannelEngine\Merchant\ApiClient\Model\MerchantCancellationRequest;
use ChannelEngine\Merchant\ApiClient\Model\MerchantCancellationLineRequest;


class Channel_Engine_API extends Channel_Engine_Base_Class{

    private $client;
    private $last_returned;

    /* @var OrderApi|ReturnApi|ShipmentApi|CancellationApi $client */
    public function __construct($client){
        $this->client = $client;
        $this->last_returned = parent::PREFIX.'_returns_last_modified';
    }

    public function fetch_returns(){
        $modified_since = get_option($this->last_returned);
        if($modified_since == ""){
            $modified_since = "2000-01-01T00:00:00+01:00";
        }

        try{
            $result = $this->client->returnGetDeclaredByChannel(new \DateTime($modified_since))->getContent();
        }catch(Exception $e){
            error_log( print_r( $e, true ) );
            return;
        }
        /* @var MerchantReturnResponse $returnOrder */
        foreach($result as $returnOrder){
            //Get wc_order based on orderID
            $orderID = $returnOrder->getMerchantOrderNo(); // $orderID = get_the_ID();
            $wc_order = new WC_Order($orderID);

            $wc_order->update_status('wc-returned', '', false);
            if( $returnOrder->getReason() )  {
                $wc_order->add_order_note('Channel Engine - The order has been returned, reason: ' . $returnOrder->getReason());
            }else{
                $wc_order->add_order_note('Channel Engine - The order has been returned');
            }
            wp_reset_postdata();
        }

        update_option($this->last_returned, (new \DateTime())->format("Y-m-d\TH:i:sP"));
    }

    public function post_order_cancelled_status($wc_order_id) {

        $order = new WC_Order($wc_order_id);
        $ceOrderId = get_post_meta($order->get_id(), parent::PREFIX . '_order_id', true);

        if(!$ceOrderId) return;

        $cancellation = new MerchantCancellationRequest();
        $cancellation->setMerchantOrderNo($order->get_id());
        $cancellation->setMerchantCancellationNo($order->get_id());

        $cancellationLines = $cancellation->getLines();
        foreach ($order->get_items() as $wc_line_item_id => $lineItem) {
            //Create shipment lines
            $cancellationLine = new MerchantCancellationLineRequest();

            $productId = $lineItem['product_id'];
            $order_line_id = wc_get_order_item_meta($wc_line_item_id, parent::PREFIX.'_channel_order_line_id');
            $qty = $lineItem['qty'];
            $cancellationLine->setMerchantProductNo($productId);
            $cancellationLine->setQuantity(intval($qty));
            $cancellationLines[] =  $cancellationLine;
        }
        $cancellation->setLines($cancellationLines);
        $cancellation->setReason(1);

        try{
            //Post shipment status to channel engine
            $this->client->cancellationCreate($cancellation);
            $order->add_order_note( parent::ORDER_CANCELLED_SUCCESS );
        }catch(Exception $e){
            //Add note to order that specifies the exception
            $order->add_order_note( parent::PREFIX_ORDER_ERROR.$e->getMessage() );
            error_log( print_r( $e, true ) );
        }
    }

    public function post_shipment_complete_status($wc_order_id) {

        $order = new WC_Order($wc_order_id);
        $ceOrderId = get_post_meta($order->get_id(), parent::PREFIX . '_order_id', true);
        $update = (boolean) get_post_meta($order->get_id(), parent::PREFIX . '_shipment_created', true);

        if(!$ceOrderId) return;

        if(!$update)
        {
            $shipment = new MerchantShipmentRequest();

            $shipment->setMerchantOrderNo($order->get_id());
            $shipment->setMerchantShipmentNo($order->get_id());
            $shipmentLines = $shipment->getLines();
            foreach ($order->get_items() as $wc_line_item_id => $lineItem)
            {
                //Create shipment lines
                $shipmentLine = new MerchantShipmentLineRequest();

                $productId = $lineItem['product_id'];
                $order_line_id = wc_get_order_item_meta($wc_line_item_id, parent::PREFIX.'_channel_order_line_id');
                $qty = $lineItem['qty'];
                $shipmentLine->setMerchantProductNo($productId);
                $shipmentLine->setQuantity(intval($qty));
                $shipmentLines[] =  $shipmentLine;
            }
            $shipment->setLines($shipmentLines);
        }
        else
        {
            $shipment = new MerchantShipmentTrackingRequest();
        }

		$trackTrace = get_post_meta($order->get_id(), '_shipping_ce_track_and_trace', true);
        if(empty($trackTrace)) $trackTrace = get_post_meta($order->get_id(), 'TrackAndTraceBarCode', true);

        $shippingMethod = get_post_meta($order->get_id(), '_shipping_ce_shipping_method', true);

        if(empty($shippingMethod) || $shippingMethod == "Other")
            $shippingMethod = get_post_meta($order->get_id(), '_shipping_ce_shipping_method_other', true);

        // Track / trace cannot be empty for non mail box parcels
        if(empty($trackTrace) && $shippingMethod != 'Briefpost')
        {
            $order->add_order_note(parent::PREFIX_ORDER_MESSAGE . 'Shipping method ' . $shippingMethod . ' requires a tracking code.');
            return;
        }

        $shipment->setTrackTraceNo($trackTrace);
        $shipment->setMethod($shippingMethod);

        try
        {
            //Post shipment status to channel engine
            if(!$update)
            {
                $this->client->shipmentCreate($shipment);
                update_post_meta($order->get_id(), parent::PREFIX . '_shipment_created', true);
            }
            else
            {
                $this->client->shipmentUpdate($wc_order_id, $shipment);
            }
            $order->add_order_note( parent::ORDER_COMPLETE_SUCCESS );
        }
        catch(Exception $e)
        {
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
            $api_instance = $this->client;
            //Get all orders
//            $orders = $this->client->getOrders(
//                array(
//                    OrderStatus::IN_PROGRESS,
//                    OrderStatus::NEW_ORDER
//                )
//            );
            $orders = $api_instance->orderGetNew()->getContent();
        }catch(Exception $e){
            //Write exception to error log
            error_log( print_r( $e, true ) );
            $results['Exception'] = $e->getMessage();
            $api_instance = null;
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

                    $results['Failed'][] = array(
                        'Succes' => false,
                        'OrderId' => $currentChannelOrderId,
                        'Message' => 'Order with OrderId ' . $order->getId() . ' already exists, order has not been imported!'
                    );
                }
            }
            wp_reset_postdata();

            //Only create order when it does not exist yet
            if(!$order_exists) {
                $result = $this->create_order($order);
                if($result['success']){
                    // order imported, increment successfull import counter
                    $results['Success'][] = $result;
                    try {
                        $api_instance->orderAcknowledge(new \ChannelEngine\Merchant\ApiClient\Model\OrderAcknowledgement($result));
                    }
                    catch(Exception $e){
                        //Write exception to error log
                        error_log( print_r( $e, true ) );
                        $results['Exception'] = $e->getMessage();
                    }
                }else{
                    $results['Failed'][] = $result;
                }
            }
        }
		echo(json_encode($results, JSON_PRETTY_PRINT));
    }

    /**
     * Parse order object to woocommerce specific data
     */
    public function create_order(MerchantOrderResponse $order)
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
                'post_name' => 'order-' . $order->getOrderDate()->format("Y-m-d H:i:s"),
                'post_type' => 'shop_order',
                'post_title' => 'Order &ndash; ' . $order->getOrderDate()->format("Y-m-d H:i:s"),
                //'post_status' => $order->getStatus(),
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
						'subtotal' => $orderLine->getUnitPriceInclVat() - $orderLine->getUnitVat(),
                        'total' => $orderLine->getLineTotalInclVat() - $orderLine->getLineVat(),
                        'subtotal_tax' => $orderLine->getUnitVat(),
                        'total_tax' => $orderLine->getLineVat()
					)
				);
                $wc_order->add_product($wc_product, $orderLine->getQuantity(), $productLineArgs);

                //Set channel engine product number on the fetch wc_product
                update_post_meta($wc_product->get_id(), parent::PREFIX.'_channel_product_no', $orderLine->getChannelProductNo());
            }

            //Order meta
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_order_id', $order->getId());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_vat_no', $order->getVatNo());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_order_date', $order->getOrderDate()->format("Y-m-d\TH:i:s"));
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_created_at', $order->getOrderDate()->format("Y-m-d\TH:i:s"));
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_updated_at', (new DateTime())->format("Y-m-d\TH:i:s"));
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_channel_order_no', $order->getChannelOrderNo());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_channel_customer_no', $order->getChannelCustomerNo());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_channel_name', $order->getChannelName());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_shipping_costs_incl_vat', $order->getShippingCostsInclVat());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_shipping_costs_vat', $order->getShippingCostsVat());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_sub_total_vat', $order->getSubTotalVat());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_status', $order->getStatus());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_payment_method', $order->getPaymentMethod());
            update_post_meta($wc_order->get_id(), parent::PREFIX . '_shipment_created', false);

			// Woocommerce data
			update_post_meta($wc_order->get_id(), '_customer_ip_address', '');
			update_post_meta($wc_order->get_id(), '_shipping_ce_track_and_trace', '');

            if($this->is_plugin_active('woocommerce_wuunder/woocommerce-wuunder.php'))
            {
                update_post_meta($wc_order->get_id(), '_shipping_street_name', $sa->getStreetName());
                update_post_meta($wc_order->get_id(), '_shipping_house_number', $sa->getHouseNr());
                update_post_meta($wc_order->get_id(), '_shipping_house_number_suffix', $sa->getHouseNrAddition());
            }

			$wc_order->order_date = $order->getOrderDate();

			// shipping
            $shippingItem = new WC_Order_Item_Shipping();
            $shippingItem->set_total($order->getShippingCostsInclVat());
//
            $wc_order->add_item($shippingItem);

            // totals
            $wc_order->set_total($order->getTotalInclVat());

            $wc_order->set_shipping_total($order->getShippingCostsInclVat());

            $wc_order->calculate_taxes();

            $wc_order->payment_complete();

            $wc_order->save();
//            //Extra data
//            //TODO: Should these be parsed to other objects?
//            update_post_meta($wc_order->get_id(), parent::PREFIX . '_extra_data', serialize($order->getExtraData()));
//            update_post_meta($wc_order->get_id(), parent::PREFIX . '_shipments', serialize($order->getShipments()));
//            update_post_meta($wc_order->get_id(), parent::PREFIX . '_cancellations', serialize($order->getCancellations()));
			return array(
				'success' => true,
				'orderId'=>$order->getId(),
				'merchantOrderNo'=>$wc_order->get_order_number()
			);
        }
        else
        {
        	// products mismatch
        	$errorMessage = 'Products with ID [' . implode($productMismatches,',') . '] in order ' . $order->getId() . ' does not exist, order has not been imported!';
        	error_log($errorMessage);
			return array(
				'success' => false,
				'orderId'=>$order->getId(),
				'message'=>$errorMessage
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