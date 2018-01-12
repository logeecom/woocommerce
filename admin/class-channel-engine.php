<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 14/09/15
 * Time: 12:07
 */

require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-base-class.php' );

use ChannelEngine\ApiClient\Configuration;
use ChannelEngine\ApiClient\Api\OrderApi;
use ChannelEngine\ApiClient\Api\ReturnApi;
use ChannelEngine\ApiClient\Api\ShipmentApi;
use ChannelEngine\ApiClient\Api\CancellationApi;

class Channel_Engine {

    private $tracker;
    private $settings;
    private $orderClient;
    private $returnClient;
    private $shipmentClient;
    private $cancellationClient;
	private $pluginPath;
    private $product_validation;

    /**
     * Constructor
     */
    public function __construct($pluginPath) {
    	$this->pluginPath = $pluginPath; // set path of main plugin file for hook reference
    	/**
		 * Check if WooCommerce is active
		 **/
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			// Run plugin code
            add_action('admin_enqueue_scripts', array($this, 'include_styles'));
            add_action('admin_enqueue_scripts', array($this, 'include_scripts'));
	        $this->includes();
	        $this->init_classes();
		}
    }

    /**
     * Include the necessary files, so we can use them in this class.
     */
    public function includes(){

        require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-custom-order-status.php' );
        require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-product-tab.php' );
        require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-order-complete.php' );
        require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-order-cancelled.php' );
        require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-settings.php' );
        require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-tracking.php' );
        require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-api-endpoint.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-admin.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-shipping-method.php' );
        require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-product-validation.php' );
    }

    /**
     * Instantiate all necessary classes
     */
    public function init_classes(){
		$this->product_validation = new Channel_Engine_Product_Validation();

    	new Channel_Engine_Admin();
        new Channel_Engine_Product_Tab($this->product_validation);
        new Channel_Engine_Custom_Order_Status();

        $this->settings = new Channel_Engine_Settings();
        $this->tracker  = new Channel_Engine_Tracking($this->settings->account_name);

        $apiConfig = Configuration::getDefaultConfiguration();
        $apiConfig->setHost('https://' . $this->settings->account_name . '.channelengine.net/api');
        $apiConfig->setApiKey('apikey', $this->settings->api_key);

        $this->orderClient = new OrderApi();
        $this->returnClient = new ReturnApi();
        $this->shipmentClient = new ShipmentApi();
        $this->cancellationClient = new CancellationApi();

        new Channel_Engine_Order_Complete($this->shipmentClient);
        new Channel_Engine_Order_Cancelled($this->cancellationClient);
        new Channel_Engine_API_Endpoint($this->orderClient, $this->returnClient, $this->shipmentClient, $this->pluginPath, $this->product_validation);
    }

    public function include_scripts() {
        wp_enqueue_script('channel-engine-admin-edit-script', plugins_url('/js/channel-engine-admin-edit.js', __FILE__));
    }

    public function include_styles() {
        //Global channel engine css file
        wp_enqueue_style( 'channel-engine-admin-style', plugins_url( '/css/channel-engine-admin.css', __FILE__ ) );
    }

}