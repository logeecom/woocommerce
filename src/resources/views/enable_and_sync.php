<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path(__DIR__);
$baseUrl  = Shop_Helper::get_plugin_page_url();
?>
<div id="ce-loader" class="ce-overlay">
    <div class="ce-loader"></div>
</div>
<div class="channel-engine" style="display: none;">
    <header>
        <img src="<?php echo Asset_Helper::get_image_url('logo.svg'); ?>" height="30" alt="ChannelEngine" />
    </header>
    <main>
        <div class="ce-onboarding">
            <div class="ce-onboarding-steps">
                <div class="ce-step active">
                    <div class="ce-step-number">1</div>
                    <div class="ce-step-title"><?php echo __( 'Account', 'channelengine' ); ?></div>
                </div>
                <div class="ce-step active">
                    <div class="ce-step-number">2</div>
                    <div class="ce-step-title"><?php echo __( 'Product synchronization', 'channelengine' ); ?></div>
                </div>
                <div class="ce-step active">
                    <div class="ce-step-number">3</div>
                    <div class="ce-step-title"><?php echo __( 'Order status mapping', 'channelengine' ); ?></div>
                </div>
                <div class="ce-step active">
                    <div class="ce-step-number">4</div>
                    <div class="ce-step-title"><?php echo __( 'Initial sync', 'channelengine' ); ?></div>
                </div>
            </div>
            <h1><?php echo __( 'You are just one step away', 'channelengine' ); ?></h1>
            <h2><?php echo __( 'Integration status', 'channelengine' ); ?></h2>
            <div class="ce-input-group ce-wide-group">
                <label>
                    <span class="label ce-big-label"><?php echo __( 'Enable the integration and start the initial synchronization', 'channelengine' ); ?></span>
                    <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'By enabling integration, it will trigger the initial synchronization in the background. The integration will synchronize all published shop products to ChannelEngine in the background. Also, the integration will synchronize new and closed orders (fulfilled by the merchant and fulfilled by the marketplace) from ChannelEngine into the shop.', 'channelengine' ); ?>
                </span>
            </span>
                    <a id="ceStartSync"
                       class="ce-button ce-button__primary"><?php echo __( 'Enable and start sync', 'channelengine' ); ?></a>
                    <input id="ceInitialSyncUrl" type="hidden"
                           value="<?php echo Shop_Helper::get_controller_url( 'Initial_Sync', 'start' ); ?>">
                </label>
            </div>
        </div>
    </main>
</div>