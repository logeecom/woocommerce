<?php
/**
 * Created by PhpStorm.
 * User: Zooma
 * Date: 22/09/15
 * Time: 12:11
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function channel_engine_shipping_method_init() {
		if ( ! class_exists( 'Channel_Engine_Shipping_Method' ) ) {
			class Channel_Engine_Shipping_Method extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'channel_engine';
					$this->title       = __( 'ChannelEngine' , 'channelengine' );
					$this->method_title       = __( 'ChannelEngine', 'channelengine' );
					$this->method_description = __( 'Here you can set the default shipping cost and time for ChannelEngine.',  'channelengine' );
					$this->enabled            = 'no';
					$this->init();
				}
		
				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					
					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
		
					$this->fee          = $this->get_option( 'fee' );
					$this->time          = $this->get_option( 'time' );

					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}
		
				/**
				 * Init form fields.
				 */
				public function init_form_fields() {
					$this->form_fields = array(
						'fee' => array(
							'title'       => __( 'Delivery Fee', 'channelengine' ),
							'type'        => 'price',
							'description' => __( 'What fee do you want to charge for local delivery?', 'channelengine' ),
							'default'     => '',
							'placeholder' => wc_format_localized_price( 0 )
						),
						'time' => array(
							'title'       => __( 'Delivery Time Indication', 'channelengine' ),
							'description' => __( 'e.g. Ordered before 22:00: Shipped Today.', 'channelengine' ),
							'default'     => ''
						)
					);
				}

				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package = array() ) {
					// This is where you'll add your rates
					$rate = array(
						'id' => $this->id,
						'label' => $this->title,
						'cost' => '5',
						'calc_tax' => 'per_order'
					);
		
					// Register the rate
					$this->add_rate( $rate );
				}
			}
		}
	}
	add_action( 'woocommerce_shipping_init', 'channel_engine_shipping_method_init' );
	function add_channel_engine_shipping_method( $methods ) {
		$methods[] = 'Channel_Engine_Shipping_Method';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'add_channel_engine_shipping_method' );
}