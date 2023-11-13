<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

?>
<div class="ce-page">
	<h1><?php esc_html_e( 'Integration status', 'channelengine-wc' ); ?></h1>
	<p>
		<?php esc_html_e( 'Integration between WooCommerce and ChannelEngine is disabled. Synchronization is not performed in the background.', 'channelengine-wc' ); ?>
	</p>
	<div>
		<div class="ce-input-group">
			<label>
				<span class="label"><?php esc_html_e( 'Enable the integration', 'channelengine-wc' ); ?></span>
				<span class="ce-help">
					<span class="ce-help-tooltip">
						<?php esc_html_e( 'By enabling integration, you will be asked whether you want to perform full synchronization (products and orders) again since synchronization was disabled for some time.', 'channelengine-wc' ); ?>
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
		   value="<?php esc_attr( Shop_Helper::get_controller_url( 'Config', 'trigger_sync' ) ); ?>">
	<input id="ce-check-status-url" type="hidden"
		   value="<?php esc_attr( Shop_Helper::get_controller_url( 'Config', 'check_status' ) ); ?>">
	<div id="ce-trigger-modal" style="display: none">
		<?php require plugin_dir_path( __FILE__ ) . '/../trigger_sync.php'; ?>
	</div>
</div>
