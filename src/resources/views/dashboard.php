<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Frontend_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();
/**
 * @var array $data
 */

if ( 'disabled-integration' !== $data['status'] ) {
	Frontend_Helper::render_view( 'dashboard' );
}
?>
<div id="ce-loader" class="ce-overlay">
	<div class="ce-loader"></div>
</div>
<div class="channel-engine ce-hidden">
	<?php require plugin_dir_path( __FILE__ ) . 'partials/header.php'; ?>
	<main>
		<nav class="nav-tab-wrapper">
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'dashboard' ) ); ?>"
			   class="nav-tab nav-tab-active"><?php esc_html_e( 'Dashboard', 'channelengine-wc' ); ?></a>
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'config' ) ); ?>"
			   class="nav-tab"><?php esc_html_e( 'Configuration', 'channelengine-wc' ); ?></a>
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'transactions' ) ); ?>"
			   class="nav-tab"><?php esc_html_e( 'Transaction log', 'channelengine-wc' ); ?></a>
		</nav>

		<div id="sync-in-progress">
			<?php require plugin_dir_path( __FILE__ ) . 'partials/dashboard/sync_progress.php'; ?>
		</div>
		<div id="sync-completed" class="ce-page ce-page-centered">
			<?php require plugin_dir_path( __FILE__ ) . 'partials/dashboard/sync_completed.php'; ?>
		</div>
		<div id="notifications">
			<?php require plugin_dir_path( __FILE__ ) . 'partials/dashboard/notifications.php'; ?>
		</div>
		<div id="disabled-integration">
			<?php require plugin_dir_path( __FILE__ ) . 'partials/dashboard/disable.php'; ?>
		</div>
		<div id="ce-modal" class="ce-hidden">
			<?php require plugin_dir_path( __FILE__ ) . 'partials/modal.php'; ?>
		</div>
		<input id="ce-status" type="hidden" value="<?php echo esc_attr( $data['status'] ); ?>">
		<input id="ce-check-status" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Check_Status', 'get_sync_data' ) ); ?>">
		<input id="ce-enable-plugin" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Enable', 'enable' ) ); ?>">
		<input id="ce-check-order-sync" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Check_Status', 'get_order_sync_config' ) ); ?>">
		<input id="ceGetAccountName" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_account_name' ) ); ?>">
		<input id="ce-disconnect-url" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'disconnect' ) ); ?>">
	</main>
</div>
