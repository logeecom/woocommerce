<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var $pageTitle
 */
?>
<h1><?php esc_html($pageTitle) ?: esc_html_e(
        'Connect the shop with your ChannelEngine account',
        'channelengine-integration'
    ); ?></h1>
<form class="ce-form">
	<h3><?php esc_html_e( 'Account data', 'channelengine-integration' ); ?></h3>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'Account name', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Enter the subdomain of your ChannelEngine instance. This is the name listed before channelengine.net. E.g.: the account name for myshop.channelengine.net is myshop.', 'channelengine-wc' ); ?>
				</span>
			</span>
			<input id="ceAccountName" type="text" autocomplete="new-password" />
		</label>
	</div>
	<div class="ce-input-group">
		<label>
			<span class="label"><?php esc_html_e( 'API Key', 'channelengine-wc' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Enter the API key. To find the API key, on ChannelEngine,go to Settings, Merchant APIkey.', 'channelengine-wc' ); ?>
				</span>
			</span>
			<input id="ceApiKey" type="password" autocomplete="new-password" />
		</label>
	</div>
</form>
