<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();
?>
<div id="ce-loader" class="ce-overlay">
	<div class="ce-loader"></div>
</div>
<div class="channel-engine ce-hidden">
	<?php require plugin_dir_path( __FILE__ ) . 'partials/header.php'; ?>
	<main>
		<div class="ce-onboarding">
			<div class="ce-onboarding-steps">
				<div class="ce-step active">
					<div class="ce-step-number">1</div>
					<div class="ce-step-title"><?php esc_html_e( 'Account', 'channelengine-wc' ); ?></div>
				</div>
				<div class="ce-step active">
					<a href="#" id="stepToProductSettings">
						<div class="ce-step-number">2</div>
					</a>
					<div class="ce-step-title"><?php esc_html_e( 'Product synchronization', 'channelengine-wc' ); ?></div>
				</div>
				<div class="ce-step active">
					<a href="#" id="stepToOrderSettings">
						<div class="ce-step-number">3</div>
					</a>
					<div class="ce-step-title"><?php esc_html_e( 'Order synchronization', 'channelengine-wc' ); ?></div>
				</div>
				<div class="ce-step active">
					<div class="ce-step-number">4</div>
					<div class="ce-step-title"><?php esc_html_e( 'Initial sync', 'channelengine-wc' ); ?></div>
				</div>
			</div>
			<h1><?php esc_html_e( 'You are just one step away', 'channelengine-wc' ); ?></h1>
			<h2><?php esc_html_e( 'Integration status', 'channelengine-wc' ); ?></h2>
			<div class="ce-input-group ce-wide-group">
				<label>
					<span class="label ce-big-label"><?php esc_html_e( 'Enable the integration and start the initial synchronization', 'channelengine-wc' ); ?></span>
					<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'When enabled, it triggers the initial synchronization in the background. Products from WooCommerce are transferred to ChannelEngine – and orders from ChannelEngine are imported to WooCommerce.', 'channelengine-wc' ); ?>
				</span>
			</span>
					<a id="ceStartSync"
					   class="ce-button ce-button__primary"><?php esc_html_e( 'Enable and start sync', 'channelengine-wc' ); ?></a>
					<input id="ceInitialSyncUrl" type="hidden"
						   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Initial_Sync', 'start' ) ); ?>">
				</label>
			</div>
			<input id="ceGetAccountName" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_account_name' ) ); ?>">
			<input id="ce-disconnect-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'disconnect' ) ); ?>">
			<input id="ceSwitchOnboardingPage" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Switch_Page', 'switch_page' ) ); ?>">
			<div id="ce-modal" class="ce-hidden">
				<?php require plugin_dir_path( __FILE__ ) . 'partials/modal.php'; ?>
			</div>
		</div>
	</main>
</div>
