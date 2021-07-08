<?php

use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();

?>
<h1><?php echo __( 'Order status mapping', 'channelengine' ); ?></h1>
<p><?php echo __( 'Map WooCommerce shop order statuses to the ChannelEngine order statuses.', 'channelengine' ); ?></p>
<form class="ce-form">
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Status of incoming orders', 'channelengine' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'Select the status of orders that are not processed yet.', 'channelengine' ); ?>
                </span>
            </span>
            <select id="ceIncomingOrders">
            </select>
        </label>
    </div>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Status that defines a shipped order', 'channelengine' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'Select the status of orders that are shipped.', 'channelengine' ); ?>
                </span>
            </span>
            <select id="ceShippedOrders">
            </select>
        </label>
    </div>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Status of the orders fulfilled by a marketplace', 'channelengine' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'Select the status of orders that are already fulfilled by a marketplace.', 'channelengine' ); ?>
                </span>
            </span>
            <select id="ceFulfilledByMp">
            </select>
        </label>
    </div>
</form>
