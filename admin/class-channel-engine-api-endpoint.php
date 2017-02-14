<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 16/09/15
 * Time: 12:47
 */

// Import the required namespaces
use ChannelEngineApiClient\Client\ApiClient;

class Channel_Engine_API_Endpoint{

    private $client;
    private $product_validation;

    /** Hook WordPress
     */
    public function __construct(ApiClient $client, $pluginPath, $product_validation){

        add_filter('query_vars', array($this, 'add_query_vars'), 0);
        add_action('parse_request', array($this, 'sniff_requests'), 0);
		add_action('init', array($this, 'add_endpoints'), 0);

        register_activation_hook($pluginPath, array($this, 'add_endpoints_and_flush'));
        register_deactivation_hook($pluginPath, array($this, 'remove_endpoints'));

        $this->client = $client;
        $this->product_validation = $product_validation;
    }

    /** Add public query vars
     *	$vars List of current public query vars
     */
    public function add_query_vars($vars){
        $vars[] = '__channel_engine_product_feed';
        $vars[] = '__channel_engine_callback';
        $vars[] = '__channel_engine_fetch_returns';
        return $vars;
    }

    /** Add API Endpoints
     *	Rewrite www.domain.com/ChannelEngine/product_feed and set 'index.php?__channel_engine_product_feed=1'
     *  Rewrite www.domain.com/ChannelEngine/callback and set 'index.php?__channel_engine_callback=1'
     */
    public function add_endpoints(){
        add_rewrite_rule('^ChannelEngine/product_feed'    ,'index.php?__channel_engine_product_feed=1' ,'top');
        add_rewrite_rule('^ChannelEngine/callback'        ,'index.php?__channel_engine_callback=1'     ,'top');
    }
	
	public function add_endpoints_and_flush(){
        $this->add_endpoints();
        flush_rewrite_rules();
	}

    /** Remove API Endpoints
     *  Cleanup after de-activation of plugin
     */
    public function remove_endpoints(){
        flush_rewrite_rules();
    }

    /**	Sniff Requests
     *	This is where we hijack all API requests
     * 	If $_GET['__ourvariable'] is set, we kill WP and serve up our own feed
     */
    public function sniff_requests(){
        global $wp;
        if(isset($wp->query_vars['__channel_engine_product_feed'])){

            require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-product-feed.php' );
            $product_feed = new Channel_Engine_Product_Feed($this->product_validation);
            $product_feed->generate_product_feed();

            exit;
        }
        if(isset($wp->query_vars['__channel_engine_callback'])){

            require_once( plugin_dir_path( __FILE__ ) . 'class-channel-engine-api.php' );
			// check if callback is fired by channel engine
			$type = isset($_GET['type']) ? $_GET['type'] : '';
			try { 
				$this->client->validateCallbackHash();
			} catch(Exception $e) {
				http_response_code(403);
				exit($e->getMessage());
			}
			switch($type) {
				case 'orders':
					$channel_engine_api_client = new Channel_Engine_API($this->client);
            		$channel_engine_api_client->import_orders();
					break;
				case 'returns':
					$channel_engine_api_client = new Channel_Engine_API($this->client);
            		$channel_engine_api_client->fetch_returns();
					break;
			}
            

            exit;
        }
    }
}