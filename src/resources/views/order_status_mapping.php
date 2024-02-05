<?php

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
					<div class="ce-step-number">3</div>
					<div class="ce-step-title"><?php esc_html_e( 'Order synchronization', 'channelengine-wc' ); ?></div>
				</div>
				<div class="ce-step">
					<div class="ce-step-number">4</div>
					<div class="ce-step-title"><?php esc_html_e( 'Initial sync', 'channelengine-wc' ); ?></div>
				</div>
			</div>
			<?php require plugin_dir_path( __FILE__ ) . 'partials/order_status_mapping.php'; ?>
			<a id="ceStatusesSave"
			   class="ce-button ce-button__primary"><?php esc_html_e( 'Save and continue', 'channelengine-wc' ); ?></a>
			<input id="ceStatusesUrl" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Order_Status', 'get' ) ); ?>">
			<input id="ceStatusesSaveUrl" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Order_Status', 'save' ) ); ?>">
			<input id="ceStatusesSaveForSwitchUrl" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Order_Status', 'save_values' ) ); ?>">
			<input id="ceEnabledStockSync" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'is_enabled_stock_sync' ) ); ?>">
			<input id="ceSwitchOnboardingPage" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Switch_Page', 'switch_page' ) ); ?>">
			<input id="ceGetAccountName" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_account_name' ) ); ?>">
			<input id="ce-disconnect-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'disconnect' ) ); ?>">
			<div id="ce-modal" class="ce-hidden">
				<?php require plugin_dir_path( __FILE__ ) . 'partials/modal.php'; ?>
			</div>
		</div>
	</main>
</div>
