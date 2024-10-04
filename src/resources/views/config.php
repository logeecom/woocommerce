<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Frontend_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();

Frontend_Helper::render_view( 'config' );
?>
<div id="ce-loader" class="ce-overlay explicitHide">
	<div class="ce-loader"></div>
</div>
<div class="channel-engine ce-hidden">
	<?php require plugin_dir_path( __FILE__ ) . 'partials/header.php'; ?>
	<main>
		<nav class="nav-tab-wrapper">
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'dashboard' ) ); ?>"
			   class="nav-tab"><?php esc_html_e( 'Dashboard', 'channelengine-integration' ); ?></a>
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'config' ) ); ?>"
			   class="nav-tab nav-tab-active"><?php esc_html_e( 'Configuration', 'channelengine-integration' ); ?></a>
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'transactions' ) ); ?>"
			   class="nav-tab"><?php esc_html_e( 'Transaction log', 'channelengine-integration' ); ?></a>
		</nav>
		<div id="ce-config-page" class="ce-page-with-header-footer">
			<header>
				<label>
					<span class="label"><?php esc_html_e( 'Disable integration', 'channelengine-integration' ); ?></span>
					<label class="ce-switch">
						<input id="ce-disable-switch" type="checkbox" checked="checked">
						<span class="ce-switch__slider"></span>
					</label>
				</label>
				<div>
					<span><?php esc_html_e( 'Manually trigger synchronization', 'channelengine-integration' ); ?></span>
					<button id="ce-sync-now" class="ce-button ce-button__primary ce-start-sync">
						<?php esc_html_e( 'Start sync now', 'channelengine-wc' ); ?></button>
					<button id="ce-sync-in-progress" class="ce-button ce-button__primary ce-loading"
							style="display: none"
							disabled><?php esc_html_e( 'In progress', 'channelengine-wc' ); ?></button>
				</div>
			</header>
			<main class="ce-page">

				<section>
					<?php
					$pageTitle = __( 'Disconnect your account', 'channelengine-wc' );
					require plugin_dir_path( __FILE__ ) . 'partials/account.php'
					?>
					<button id="ce-disconnect-btn"
							class="ce-button ce-button__primary"><?php esc_html_e( 'Disconnect', 'channelengine-wc' ); ?></button>
				</section>
				<section>
					<?php require plugin_dir_path( __FILE__ ) . 'partials/product_feed.php'; ?>
				</section>
				<section>
					<?php require plugin_dir_path( __FILE__ ) . 'partials/order_status_mapping.php'; ?>
				</section>
			</main>
			<footer>
				<button id="ce-save-config"
						class="ce-button ce-button__primary"><?php esc_html_e( 'Save changes', 'channelengine-wc' ); ?></button>
			</footer>
			<input id="ce-account-data-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_account_data' ) ); ?>">
			<input id="ce-disconnect-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'disconnect' ) ); ?>">
			<input id="ce-disable-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Enable', 'disable' ) ); ?>">
			<input id="ce-trigger-sync-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'trigger_sync' ) ); ?>">
			<input id="ce-order-statuses-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Order_Status', 'get' ) ); ?>">
			<input id="ce-save-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'save' ) ); ?>">
			<input id="ce-check-status-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'check_status' ) ); ?>">
			<input id="ceGetAccountName" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_account_name' ) ); ?>">
			<input id="ceEnabledStockSync" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'is_enabled_stock_sync' ) ); ?>">
		</div>
		<div id="ce-modal" class="ce-hidden">
			<?php require plugin_dir_path( __FILE__ ) . 'partials/modal.php'; ?>
		</div>
		<div id="ce-trigger-modal" class="ce-hidden">
			<?php require plugin_dir_path( __FILE__ ) . 'partials/trigger_sync.php'; ?>
		</div>
		<input id="ce-extra-data-duplicates-text" type="hidden"
			   value="<?php esc_attr_e( 'Duplicate or empty keys founded in extra data mapping. Delete them to continue.', 'channelengine-wc' ); ?>">
		<input id="ce-extra-data-duplicates-header" type="hidden"
			   value="<?php esc_attr_e( 'Warning', 'channelengine-wc' ); ?>">
		<input id="ce-disconnect-header-text" type="hidden"
			   value="<?php esc_attr_e( 'Disconnect account', 'channelengine-wc' ); ?>">
		<input id="ce-disconnect-button-text" type="hidden"
			   value="<?php esc_attr_e( 'Disconnect', 'channelengine-wc' ); ?>">
		<input id="ce-disable-header-text" type="hidden"
			   value="<?php esc_attr_e( 'Disable integration', 'channelengine-wc' ); ?>">
		<input id="ce-disable-button-text" type="hidden" value="<?php esc_attr_e( 'Disable', 'channelengine-wc' ); ?>">
		<input id="ce-disable-text" type="hidden"
			   value="<?php esc_attr_e( 'If you disable integration, synchronization between WooCommerce and ChannelEngine will be disabled.', 'channelengine-wc' ); ?>">
		<input id="ce-disconnect-text" type="hidden"
			   value="<?php esc_attr_e( 'You are about to disconnect your ChannelEngine account.', 'channelengine-wc' ); ?>">
		<input id="ceExportProductsUrl" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'is_export_products_enabled' ) ); ?>">
		</main>
	</div>
