<?php

use ChannelEngine\Utility\Shop_Helper;

/**
 * ChannelEngine details.
 *
 * @var array $data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="overview-box">
	<label class="property-name">
		<?php esc_html_e( 'Order ID:', 'channelengine-wc' ); ?>
	</label>
	<label>
		<?php echo esc_html( $data['order_id'] ); ?>
	</label>
	<label class="property-name">
		<?php esc_html_e( 'Channel name:', 'channelengine-wc' ); ?>
	</label>
	<label>
		<?php echo esc_html( $data['channel_name'] ); ?>
	</label>
	<label class="property-name">
		<?php esc_html_e( 'Type of fulfillment:', 'channelengine-wc' ); ?>
	</label>
	<label>
		<?php echo esc_html( $data['type_of_fulfillment'] ); ?>
	</label>
	<label class="property-name">
		<?php esc_html_e( 'Channel Order No:', 'channelengine-wc' ); ?>
	</label>
	<label>
		<?php echo esc_html( $data['channel_order_no'] ); ?>
	</label>
	<label class="property-name">
		<?php esc_html_e( 'Payment method:', 'channelengine-wc' ); ?>
	</label>
	<label>
		<?php echo esc_html( $data['payment_method'] ); ?>
	</label>
</div>

<?php
if ( ! $data['order_cancelled'] ) {
	?>
	<div>
		<div id="ce-loader" class="ce-overlay">
			<div class="ce-loader"></div>
		</div>
		<div id="ce-track-and-trace-content" class="track-and-trace-box">
			<div id="ce-shipment-error" class="notice notice-error ce-hidden">
				<p id="ce-shipment-error-description">
				</p>
			</div>
			<label class="property-name">
				<?php esc_html_e( 'Track & trace', 'channelengine-wc' ); ?>
			</label>
			<label for="ce-shipping-methods">
				<?php esc_html_e( 'Shipping method:', 'channelengine-wc' ); ?>
			</label>
			<select name="ce-chipping-methods" id="ce-shipping-methods">
				<?php
				if ( ! $data['chosen_shipping_method'] ) {
					echo wp_kses( '<option selected="selected" value="">' . __( '--Shipping Method--' ) . '</option>', array(
						'option' => array( 'selected' => array(), 'value' => array() )
					) );
				}
				foreach ( $data['shipping_methods'] as $method ) {
					if ( $method->instance_id === $data['chosen_shipping_method'] ) {
						echo wp_kses( '<option selected="selected" value="' . $method->instance_id . '">' . $method->title . '</option>', array(
							'option' => array( 'selected' => array(), 'value' => array() )
						) );
					} else {
						echo wp_kses( '<option value="' . $method->instance_id . '">' . $method->title . '</option>', array(
							'option' => array( 'selected' => array(), 'value' => array() )
						) );
					}
				}
				?>
			</select>
			<label>
				<?php esc_html_e( 'Track and trace:', 'channelengine-wc' ); ?>
				<input id="ce-track-and-trace" class="ce-track-and-trace-input" type="text"
					   value="<?php echo esc_attr( $data['track_and_trace'] ); ?>">
			</label>
			<button id="ce-update-info" class="ce-update-button page-title-action">
				<?php esc_html_e( 'Update tracking information', 'channelengine-wc' ); ?>
			</button>
			<input type="hidden" id="ce-create-endpoint"
				   value="<?php echo esc_url( Shop_Helper::get_controller_url( 'Order_Overview', 'save' ) ); ?>">
			<input type="hidden" id="ce-post-id" value="<?php echo esc_attr( $data['post_id'] ); ?>">
			<input id="ceSyncShipmentStatusUrl" type="hidden"
				   value="<?php echo esc_url( Shop_Helper::get_controller_url( 'Order_Status', 'get_sync_shipment_status' ) ); ?>">
		</div>
	</div>
	<?php
}
?>
