<?php

use ChannelEngine\Utility\Asset_Helper;

?>
<header>
    <div class="ce-header">
        <div>
            <img src="<?php echo Asset_Helper::get_image_url( 'logo.svg' ); ?>" height="30" alt="ChannelEngine" />
        </div>
        <div class="ce-account-name">
            <?php echo __( 'Account name: ', 'channelengine' ); ?>
            <div id="ceAccountNameHeader" class="ce-account-name-field" style="margin-left: 5px; margin-right: 5px;"></div>
            <?php echo __( ' (', 'channelengine' ); ?>
            <a href="#" id="ceDisconnectLink"><?php echo __('Disconnect', 'channelengine'); ?></a>
            <?php echo __( ')', 'channelengine' ); ?>
        </div>
    </div>
</header>
<script src="<?php echo Asset_Helper::get_js_url( 'DisconnectService.js' ) ?>"></script>
<script src="<?php echo Asset_Helper::get_js_url( 'Disconnect.js' ) ?>"></script>