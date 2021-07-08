<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

?>
<div class="ce-page ce-horizontal ce-notifications-page">
    <script src="<?php echo Asset_Helper::get_js_url( 'DashboardNotifications.js' ); ?>"></script>
    <script src="<?php echo Asset_Helper::get_js_url( 'ModalService.js' ); ?>"></script>
    <script src="<?php echo Asset_Helper::get_js_url( 'Details.js' ) ?>"></script>
    <img src="<?php echo Asset_Helper::get_image_url( 'warning.png' ); ?>" alt="" class="ce-icon__big">
    <div class="ce-notifications">
        <h2><?php echo __( 'Notifications', 'channelengine' ); ?></h2>
        <div class="ce-notifications__items">
            <div class="ce-notifications__load-more">
                <button class="ce-button ce-button__primary"><?php echo __( 'Load more', 'channelengine' ); ?></button>
            </div>
        </div>
    </div>
    <input id="ce-notifications-url" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url( 'Notifications', 'get' ) ?>">
    <input id="ce-details-url" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url( 'Notifications', 'show_details' ) ?>">
    <input id="ce-details-get" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url( 'Transactions', 'get_details' ) ?>">
    <input id="ce-show-details-text" type="hidden" value="<?php echo __( 'Show details', 'channelengine' ); ?>">
    <input id="ce-details-header" type="hidden" value="<?php echo __( 'Transaction log details', 'channelengine' ); ?>">
    <input id="ce-modal-button-text" type="hidden" value="<?php echo __( 'Close', 'channelengine' ); ?>">
    <input id="ce-details-identifier" type="hidden" value="<?php echo __( 'Identifier', 'channelengine' ); ?>">
    <input id="ce-details-message" type="hidden" value="<?php echo __( 'Message', 'channelengine' ); ?>">
    <input id="ce-details-display" type="hidden" value="<?php echo __( 'Displayed', 'channelengine' ); ?>">
    <input id="ce-details-to" type="hidden" value="<?php echo __( 'to', 'channelengine' ); ?>">
    <input id="ce-details-from" type="hidden" value="<?php echo __( 'of', 'channelengine' ); ?>">
    <input id="ce-details-page-size" type="hidden" value="<?php echo __( 'Page size:', 'channelengine' ); ?>">
    <input id="ce-details-go-to-previous" type="hidden"
           value="<?php echo __( 'Go to previous page', 'channelengine' ); ?>">
    <input id="ce-details-previous" type="hidden" value="<?php echo __( 'Previous', 'channelengine' ); ?>">
    <input id="ce-details-go-to-next" type="hidden" value="<?php echo __( 'Go to next page', 'channelengine' ); ?>">
    <input id="ce-details-next" type="hidden" value="<?php echo __( 'Next', 'channelengine' ); ?>">
    <input id="ce-notifications-offset" type="hidden" value="0">
</div>
<div id="ce-modal" style="display: none">
	<?php include plugin_dir_path( __FILE__ ) . '/../modal.php' ?>
</div>