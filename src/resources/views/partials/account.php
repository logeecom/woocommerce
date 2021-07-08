<?php
/**
 * @var $pageTitle
 */
?>
<h1><?php echo $pageTitle ?: __("Connect the shop with your ChannelEngine account", 'channelengine') ?></h1>
<form class="ce-form">
    <h3><?php echo __( 'Account data', 'channelengine' ); ?></h3>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Account name', 'channelengine' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'The account name is a subdomain of your ChannelEngine instance (subdomain.channelengine.net). For example, the account name for ChannelEngine instance myshop.channelengine.net is myshop.', 'channelengine' ); ?>
                </span>
            </span>
            <input id="ceAccountName" type="text" autocomplete="new-password" />
        </label>
    </div>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'API Key', 'channelengine' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'You can generate merchant API key inside ChannelEngine under Settings > Merchant API keys.', 'channelengine' ); ?>
                </span>
            </span>
            <input id="ceApiKey" type="password" autocomplete="new-password" />
        </label>
    </div>
</form>
