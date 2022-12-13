<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

?>
<script src="<?php echo Asset_Helper::get_js_url( 'TriggerSyncService.js' ) ?>"></script>
<div class="ce-page">
    <h1><?php echo __( 'Integration status', 'channelengine-wc' ); ?></h1>
    <p>
		<?php echo __( 'Integration between WooCommerce and ChannelEngine is disabled. Synchronization is not performed in the background.', 'channelengine-wc' ); ?>
    </p>
    <div>
        <div class="ce-input-group">
            <label>
                <span class="label"><?php echo __( 'Enable the integration', 'channelengine-wc' ); ?></span>
                <span class="ce-help">
                    <span class="ce-help-tooltip">
                        <?php echo __( 'By enabling integration, you will be asked whether you want to perform full synchronization (products and orders) again since synchronization was disabled for some time.', 'channelengine-wc' ); ?>
                    </span>
                </span>
                <label class="ce-switch">
                    <input id="ce-enable-switch" type="checkbox">
                    <span class="ce-switch__slider"></span>
                </label>
            </label>
        </div>
    </div>
    <input id="ce-trigger-sync-url" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url( 'Config', 'trigger_sync' ); ?>">
    <input id="ce-check-status-url" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url( 'Config', 'check_status' ); ?>">
    <div id="ce-trigger-modal" style="display: none">
		<?php include plugin_dir_path( __FILE__ ) . '/../trigger_sync.php' ?>
    </div>
</div>