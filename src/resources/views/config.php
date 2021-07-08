<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Frontend_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path(__DIR__);
$baseUrl = Shop_Helper::get_plugin_page_url();

Frontend_Helper::render_view('config');
?>
<script src="<?php echo Asset_Helper::get_js_url('Config.js') ?>"></script>
<script src="<?php echo Asset_Helper::get_js_url('ModalService.js') ?>"></script>
<script src="<?php echo Asset_Helper::get_js_url('TriggerSyncService.js') ?>"></script>

<div id="ce-loader" class="ce-overlay">
    <div class="ce-loader"></div>
</div>
<div class="channel-engine" style="display: none;">
    <header>
        <img src="<?php echo Asset_Helper::get_image_url('logo.svg');  ?>" height="30" alt="ChannelEngine" />
    </header>
    <main>
        <nav class="nav-tab-wrapper">
            <a href="<?php echo Frontend_Helper::get_subpage_url('dashboard') ?>"
               class="nav-tab"><?php echo __('Dashboard', 'channelengine'); ?></a>
            <a href="<?php echo Frontend_Helper::get_subpage_url('config') ?>"
               class="nav-tab nav-tab-active"><?php echo __('Configuration', 'channelengine'); ?></a>
            <a href="<?php echo Frontend_Helper::get_subpage_url('transactions') ?>"
               class="nav-tab"><?php echo __('Transaction log', 'channelengine'); ?></a>
        </nav>
        <div id="ce-config-page" class="ce-page-with-header-footer">
            <header>
                <label>
                    <span class="label"><?php echo __('Disable integration', 'channelengine'); ?></span>
                    <label class="ce-switch">
                        <input id="ce-disable-switch" type="checkbox" checked="checked">
                        <span class="ce-switch__slider"></span>
                    </label>
                </label>
                <div>
                    <span><?php echo __('Manually trigger synchronization', 'channelengine'); ?></span>
                    <button id="ce-sync-now" class="ce-button ce-button__primary ce-start-sync">
                        <?php echo __('Start sync now', 'channelengine'); ?></button>
                    <button id="ce-sync-in-progress" class="ce-button ce-button__primary ce-loading"
                            style="display: none" disabled><?php echo __('In progress', 'channelengine'); ?></button>
                </div>
            </header>
            <main class="ce-page">
                <section>
                    <?php $pageTitle = __("Disconnect your account", 'channelengine');
                    include plugin_dir_path(__FILE__) . 'partials/account.php' ?>
                    <button id="ce-disconnect-btn"
                            class="ce-button ce-button__primary"><?php echo __('Disconnect', 'channelengine'); ?></button>
                </section>
                <section>
                    <?php include plugin_dir_path(__FILE__) . 'partials/product_feed.php' ?>
                </section>
                <section>
                    <?php include plugin_dir_path(__FILE__) . 'partials/order_status_mapping.php' ?>
                </section>
            </main>
            <footer>
                <button id="ce-save-config"
                        class="ce-button ce-button__primary"><?php echo __('Save changes', 'channelengine'); ?></button>
            </footer>
            <input id="ce-account-data-url" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url('Config', 'get_account_data'); ?>">
            <input id="ce-disconnect-url" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url('Config', 'disconnect'); ?>">
            <input id="ce-disable-url" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url('Enable', 'disable'); ?>">
            <input id="ce-trigger-sync-url" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url('Config', 'trigger_sync'); ?>">
            <input id="ce-stock-url" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url('Config', 'get_stock_quantity') ?>">
            <input id="ce-order-statuses-url" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url('Order_Status', 'get') ?>">
            <input id="ce-save-url" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url('Config', 'save'); ?>">
            <input id="ce-check-status-url" type="hidden"
                   value="<?php echo Shop_Helper::get_controller_url('Config', 'check_status'); ?>">
        </div>
        <div id="ce-modal" style="display: none">
            <?php include plugin_dir_path(__FILE__) . 'partials/modal.php' ?>
        </div>
        <div id="ce-trigger-modal" style="display: none">
            <?php include plugin_dir_path(__FILE__) . 'partials/trigger_sync.php' ?>
        </div>
        <input id="ce-disconnect-header-text" type="hidden"
               value="<?php echo __('Disconnect account', 'channelengine'); ?>">
        <input id="ce-disconnect-button-text" type="hidden" value="<?php echo __('Disconnect', 'channelengine'); ?>">
        <input id="ce-disable-header-text" type="hidden"
               value="<?php echo __('Disable integration', 'channelengine'); ?>">
        <input id="ce-disable-button-text" type="hidden" value="<?php echo __('Disable', 'channelengine'); ?>">
        <input id="ce-disable-text" type="hidden"
               value="<?php echo __('If you disable integration, synchronization between WooCommerce and ChannelEngine will be disabled.', 'channelengine') ?>">
        <input id="ce-disconnect-text" type="hidden"
               value="<?php echo __('You are about to disconnect your ChannelEngine account.', 'channelengine'); ?>">
    </main>
</div>