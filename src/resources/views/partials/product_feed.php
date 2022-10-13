<?php
use ChannelEngine\Utility\Shop_Helper;
?>
<h1><?php echo __( 'Product synchronization settings', 'channelengine' ); ?></h1>
<p></p>
<form class="ce-form">
    <h3><?php echo __( 'Stock synchronization', 'channelengine' ); ?></h3>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Enable stock synchronization', 'channelengine' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'If checked, stock information is synchronized with ChannelEngine.', 'channelengine' ); ?>
                </span>
            </span>
            <input id="enableStockSync" type="checkbox" class="checkbox" checked>
        </label>
    </div>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Set default stock quantity', 'channelengine' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'Enter stock quantity to display when stock information is not tracked or missing.', 'channelengine' ); ?>
                </span>
            </span>
            <input id="ceStockQuantity" type="number" class="small-number-input" min="0" max="1000" step="1"
                   value="0">&nbsp;<?php echo __( 'psc', 'channelengine' ); ?>
        </label>
    </div>
    <div>
		<?php include plugin_dir_path( __FILE__ ) . 'attribute_mapping.php' ?>
		<?php include plugin_dir_path( __FILE__ ) . 'extra_data_mapping.php' ?>
    </div>
    <input id="ce-stock-url" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url('Config', 'get_stock_sync_config') ?>">
</form>
