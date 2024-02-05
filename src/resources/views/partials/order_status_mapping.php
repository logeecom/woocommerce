<?php

use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();

?>
<h1><?php esc_html_e( 'Reduce stock?', 'channelengine-wc' ); ?></h1>
<p><?php esc_html_e( 'Check this option if you want to automatically reduce the stock after importing the order.', 'channelengine-wc' ); ?></p>
<form class="ce-form">
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Automatically reduce the stock', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
					<span class="ce-help-tooltip">
						<?php esc_html_e( 'If checked, product stock is updated once an order has been imported to WooCommerce.', 'channelengine-wc' ); ?>
					</span>
				</span>
			<input id="enableReduceStock" type="checkbox" class="checkbox">
		</label>
	</div>
</form>
<h1><?php esc_html_e( 'Order synchronization', 'channelengine-wc' ); ?></h1>
<form class="ce-form">
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Shipments', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
					<span class="ce-help-tooltip">
						<?php esc_html_e( 'If checked, shipment information is synchronized with ChannelEngine.', 'channelengine-wc' ); ?>
					</span>
				</span>
			<input id="enableShipmentInfoSync" type="checkbox" class="checkbox" checked>
		</label>
	</div>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Cancellations', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
					<span class="ce-help-tooltip">
						<?php esc_html_e( 'If checked, the order cancellation is synchronized with ChannelEngine.', 'channelengine-wc' ); ?>
					</span>
				</span>
			<input id="enableOrderCancellationSync" type="checkbox" class="checkbox" checked>
		</label>
	</div>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Orders fulfilled by the merchant', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
					<span class="ce-help-tooltip">
						<?php esc_html_e( 'If checked, merchant-fulfilled orders with the status New are imported to WooCommerce.', 'channelengine-wc' ); ?>
					</span>
				</span>
			<input id="enableOrdersByMerchantSync" type="checkbox" class="checkbox" checked>
		</label>
	</div>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Orders fulfilled by the marketplace', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
					<span class="ce-help-tooltip">
						<?php esc_html_e( 'If checked, marketplace-fulfilled orders are imported to WooCommerce from set date.', 'channelengine-wc' ); ?>
					</span>
				</span>
			<input id="enableOrdersByMarketplaceSync" type="checkbox" class="checkbox" checked>
			<input type="text" id="startSyncDate" class="datepicker" style="width: 100px;" value="<?php echo esc_attr( gmdate( 'd.m.Y.' ) ); ?>"/>
		</label>
	</div>
	<div id="displayOrderFulfilledDateDiv">
		<span id="displayOrderFulfilledDate" class="label"><?php esc_html_e( 'Orders fulfilled by the marketplace are imported starting from ' ); ?></span>
		<span id="displayDate" class="label"></span>
	</div>
</form>
<p><?php esc_html_e( 'Map WooCommerce shop order statuses to the ChannelEngine order statuses.', 'channelengine-wc' ); ?></p>
<form class="ce-form">
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Status of incoming orders', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select the status for unprocessed orders.', 'channelengine-wc' ); ?>
				</span>
			</span>
			<select id="ceIncomingOrders">
			</select>
		</label>
	</div>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Status that defines a shipped order', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select the status for shipped orders.', 'channelengine-wc' ); ?>
				</span>
			</span>
			<select id="ceShippedOrders">
			</select>
		</label>
	</div>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Status of the orders fulfilled by a marketplace', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select the status for marketplace-fulfilled orders.', 'channelengine-wc' ); ?>
				</span>
			</span>
			<select id="ceFulfilledByMp">
			</select>
		</label>
	</div>
</form>
