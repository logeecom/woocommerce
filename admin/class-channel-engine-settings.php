<?php


class Channel_Engine_Settings extends Channel_Engine_Base_Class{

    public   $account_name;
    public   $api_key;
    public   $api_secret;

    private  $key_account_name ;
    private  $key_api_key;
    private  $key_api_secret;
    private  $key_valid_credentials;
    private  $option_name    = 'channel-engine-options';
    private  $client;

    public function __construct(){

        //Set variables
        $this->key_account_name         = parent::PREFIX.'_account_name';
        $this->key_api_key              = parent::PREFIX.'_api_key';
        $this->key_api_secret           = parent::PREFIX.'_api_secret';
        $this->key_valid_credentials    = parent::PREFIX.'_valid_credentials';

        $this->account_name = get_option($this->key_account_name);
        $this->api_key      = get_option($this->key_api_key);
        $this->api_secret   = get_option($this->key_api_secret);

        //Hooks
        add_action( 'admin_init',       array($this, 'register_channel_engine_settings') );
        add_action( 'admin_init',       array($this, 'check_credentials') );
        add_action( 'admin_menu',       array($this, 'channel_engine_admin_menu') );
    }

    /**
     * Our admin menu
     */
    public function channel_engine_admin_menu() {
        // Add a new submenu under Woocommerce:
        add_submenu_page( 'woocommerce' , 'ChannelEngine', 'ChannelEngine', 'manage_options', 'channel-engine-plugin', array($this, 'channel_engine_options_page_callback') );
    }

    public function channel_engine_options_page_callback() {
        ?>

        <div class="wrap">

            <img src="<?php echo plugin_dir_url(__FILE__) . 'images/channel-engine-logo.png'?>" alt="ChannelEngine" style="width:300px;height:71px;">

            <form method="post" action="options.php">

                <?php settings_fields( $this->option_name ); ?>
                <?php do_settings_sections( $this->option_name ); ?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Account name</th>
                        <td><input type="text" name="<?php echo $this->key_account_name ?>" value="<?php echo esc_attr( get_option($this->key_account_name) ); ?>" style="
                        width: 80%;"/></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">API Key</th>
                        <td><input type="text" name="<?php echo $this->key_api_key ?>" value="<?php echo esc_attr( get_option($this->key_api_key) ); ?>" style="
                        width: 80%;"/></td>
                    </tr>

                    <tr valign="top" style="display: none">
                        <th scope="row">API Secret Key</th>
                        <td><input type="text" name="<?php echo $this->key_api_secret ?>" value="<?php echo esc_attr( get_option($this->key_api_secret) ); ?>" style="
                        width: 80%;"/></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Product feed URL</th>
                        <td><input readonly type="text" name="product_feed_url" value="<?php echo get_site_url() ."/ChannelEngine/product_feed" ?>" style="
                        width: 80%;"/></td>
                    </tr>

                </table>

                <?php submit_button(); ?>

            </form>

        </div>

        <?php
    }

    public function check_credentials(){

        //Check if credentials are valid
        $valid_credentials = get_option($this->key_valid_credentials);
        if($valid_credentials === 'false'){
            add_action( 'admin_notices', array($this,'admin_invalid_credentials_notice') );
        }elseif($valid_credentials === 'true'){
        	add_action( 'admin_notices', array($this,'admin_valid_credentials_notice') );
			update_option($this->key_valid_credentials, ''); // show message only once!
        }
		
    }


 	public function admin_valid_credentials_notice() {

        //Check if we are on the channel engine plugin page before showing the admin notice.
        if( isset( $_GET['page'] ) ){
            $page = $_GET['page'];

            if ($page == 'channel-engine-plugin') {

                $class = "updated";
                $message = "Credentials OK! Please add the Product feed URL to ChannelEngine (if not already added).";
                echo "<div class=\"$class\"> <p>$message</p></div>";
            }
        }
    }
	
    public function admin_invalid_credentials_notice() {

        //Check if we are on the channel engine plugin page before showing the admin notice.
        if( isset( $_GET['page'] ) ){
            $page = $_GET['page'];

            if ($page == 'channel-engine-plugin') {

                $class = "error";
                $message = "Invalid credentials.";
                echo "<div class=\"$class\"> <p>$message</p></div>";
            }
        }
    }

    public function register_channel_engine_settings(){
        register_setting($this->option_name, $this->key_account_name);
        register_setting($this->option_name, $this->key_api_key);
		 //Execute validate_credentials last, so that all necessary values are set before executing the check.
        register_setting($this->option_name, $this->key_api_secret , array($this, 'validate_credentials'));
    }

    public function validate_credentials($api_secret)
    {
        try
        {
            $value_account_name = get_option($this->key_account_name);
            $value_api_key      = get_option($this->key_api_key);
            $value_api_secret   = $api_secret;

            //Check if all values are present before doing the API call
            if(strlen($value_account_name) && strlen($value_api_key) && strlen($value_api_secret)) {
                //Create client with given credentials
                ChannelEngine\Merchant\ApiClient\Configuration::getDefaultConfiguration()->setHost('https://' . $value_account_name . '.channelengine.net/api');
                ChannelEngine\Merchant\ApiClient\Configuration::getDefaultConfiguration()->setApiKey('apikey', $value_api_key);
                $this->client = new ChannelEngine\Merchant\ApiClient\Api\OrderApi(new \GuzzleHttp\Client(), ChannelEngine\Merchant\ApiClient\Configuration::getDefaultConfiguration());

                try{
                    //Test credentials by doing an api call with a non existing order status.
                    $orders = $this->client->orderGetByFilter(-1);
                }catch(Exception $e){
                    //Write exception to error log
                    error_log( print_r( $e, true ) );
                    update_option($this->key_valid_credentials, 'false');
                }

                //API Call succeeded, set valid credentials to true.
                update_option($this->key_valid_credentials, 'true');
            }else{
                //Credentials not complete, set valid credentials to false
                update_option($this->key_valid_credentials, 'false');
            }
        }
        catch(Exception $e){
            //Error occured, most likely invalid credentials.
            //Set valid credentials to false
            update_option($this->key_valid_credentials, 'false');
        }
        return $api_secret;
    }
}