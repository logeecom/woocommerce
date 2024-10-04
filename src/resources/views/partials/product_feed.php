<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Shop_Helper;
?>
<h1><?php esc_html_e( 'Product synchronization settings', 'channelengine-integration' ); ?></h1>
<p></p>
<form class="ce-form">
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Enable product synchronization', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'If this field is disabled, the plugin will not synchronize products to ChannelEngine.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<input id="enableExportProducts" type="checkbox" class="checkbox" checked>
		</label>
	</div>
	<h3><?php esc_html_e( 'Stock synchronization', 'channelengine-integration' ); ?></h3>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Enable stock synchronization', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'If checked, stock information is synchronized with ChannelEngine.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<input id="enableStockSync" type="checkbox" class="checkbox" checked>
		</label>
	</div>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Set default stock quantity', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Enter stock quantity to display when stock information is not tracked or missing.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<input id="ceStockQuantity" type="number" class="small-number-input" min="0" max="1000" step="1"
				   value="0">&nbsp;
			<span id="psc"><?php esc_html_e( 'psc', 'channelengine-integration' ); ?></span>
		</label>
	</div>
	<div>
		<?php require plugin_dir_path( __FILE__ ) . 'attribute_mapping.php'; ?>
		<?php require plugin_dir_path( __FILE__ ) . 'extra_data_mapping.php'; ?>
	</div>
	<input id="ce-stock-url" type="hidden"
		   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_stock_sync_config' ) ); ?>">
</form>
