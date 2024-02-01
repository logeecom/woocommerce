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
			<?php echo __( 'Order ID:', 'channelengine-wc' ); ?>
        </label>
        <label>
			<?php echo $data['order_id']; ?>
        </label>
        <label class="property-name">
			<?php echo __( 'Channel name:', 'channelengine-wc' ); ?>
        </label>
        <label>
			<?php echo $data['channel_name']; ?>
        </label>
        <label class="property-name">
            <?php echo __( 'Type of fulfillment:', 'channelengine-wc' ); ?>
        </label>
        <label>
            <?php echo $data['type_of_fulfillment']; ?>
        </label>
        <label class="property-name">
			<?php echo __( 'Channel Order No:', 'channelengine-wc' ); ?>
        </label>
        <label>
			<?php echo $data['channel_order_no']; ?>
        </label>
        <label class="property-name">
			<?php echo __( 'Payment method:', 'channelengine-wc' ); ?>
        </label>
        <label>
			<?php echo $data['payment_method']; ?>
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
            <div id="ce-shipment-error" class="notice notice-error" style="display: none">
                <p id="ce-shipment-error-description">
                </p>
            </div>
            <label class="property-name">
				<?php echo __( 'Track & trace', 'channelengine-wc' ); ?>
            </label>
            <label for="ce-shipping-methods">
				<?php echo __( 'Shipping method:', 'channelengine-wc' ); ?>
            </label>
            <select name="ce-chipping-methods" id="ce-shipping-methods">
				<?php
				if ( ! $data['chosen_shipping_method'] ) {
					echo '<option selected="selected" value="">' . __( '--Shipping Method--' ) . '</option>';
				}
				foreach ( $data['shipping_methods'] as $method ) {
					if ( $method->instance_id === $data['chosen_shipping_method'] ) {
						echo '<option selected="selected" value="' . $method->instance_id . '">' . $method->title
						     . '</option>';
					} else {
						echo '<option value="' . $method->instance_id . '">' . $method->title
						     . '</option>';
					}
				}
				?>
            </select>
            <label>
				<?php echo __( 'Track and trace:', 'channelengine-wc' ); ?>
                <input id="ce-track-and-trace" class="ce-track-and-trace-input" type="text"
                       value="<?php echo $data['track_and_trace'] ?>">
            </label>
            <button id="ce-update-info" class="ce-update-button page-title-action">
				<?php echo __( 'Update tracking information', 'channelengine-wc' ); ?>
            </button>
            <input type="hidden" id="ce-create-endpoint"
                   value="<?php echo Shop_Helper::get_controller_url( 'Order_Overview', 'save' ); ?>">
            <input type="hidden" id="ce-post-id" value="<?php echo $data['post_id']; ?>">
            <input id="ceSyncShipmentStatusUrl" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url( 'Order_Status', 'get_sync_shipment_status' ); ?>">
        </div>
    </div>
	<?php
}
?>