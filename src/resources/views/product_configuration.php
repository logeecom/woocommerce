<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();
?>
<div id="ce-loader" class="ce-overlay">
    <div class="ce-loader"></div>
</div>
<div class="channel-engine" style="display: none;">
    <header>
        <img src="<?php echo Asset_Helper::get_image_url( 'logo.svg' ); ?>" height="30" alt="ChannelEngine" />
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
                <div class="ce-step">
                    <div class="ce-step-number">3</div>
                    <div class="ce-step-title"><?php echo __( 'Order synchronization', 'channelengine' ); ?></div>
                </div>
                <div class="ce-step">
                    <div class="ce-step-number">4</div>
                    <div class="ce-step-title"><?php echo __( 'Initial sync', 'channelengine' ); ?></div>
                </div>
            </div>
			<?php include plugin_dir_path( __FILE__ ) . 'partials/product_feed.php' ?>
            <a id="ceSave"
               class="ce-button ce-button__primary"><?php echo __( 'Save and continue', 'channelengine' ); ?></a>
            <input id="ceProductSave" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url( 'Product_Sync', 'save' ); ?>">
            <div id="ce-modal" style="display: none">
		        <?php include plugin_dir_path(__FILE__) . 'modal.php' ?>
            </div>
            <input id="ce-extra-data-duplicates-text" type="hidden"
                   value="<?php echo __('Duplicate or empty keys founded in extra data mapping. Delete them to continue.', 'channelengine'); ?>">
            <input id="ce-extra-data-duplicates-header" type="hidden"
                   value="<?php echo __('Warning', 'channelengine'); ?>">
        </div>
    </main>
</div>