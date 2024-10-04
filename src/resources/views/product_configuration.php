<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
					<div class="ce-step-title"><?php esc_html_e( 'Account', 'channelengine-integration' ); ?></div>
				</div>
				<div class="ce-step active">
					<div class="ce-step-number">2</div>
					<div class="ce-step-title"><?php esc_html_e( 'Product synchronization', 'channelengine-integration' ); ?></div>
				</div>
				<div class="ce-step">
					<div class="ce-step-number">3</div>
					<div class="ce-step-title"><?php esc_html_e( 'Order synchronization', 'channelengine-integration' ); ?></div>
				</div>
				<div class="ce-step">
					<div class="ce-step-number">4</div>
					<div class="ce-step-title"><?php esc_html_e( 'Initial sync', 'channelengine-integration' ); ?></div>
				</div>
			</div>
			<?php require plugin_dir_path( __FILE__ ) . 'partials/product_feed.php'; ?>
			<a id="ceSave"
			   class="ce-button ce-button__primary"><?php esc_html_e( 'Save and continue', 'channelengine-integration' ); ?></a>
			<input id="ceProductSave" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Product_Sync', 'save' ) ); ?>">
			<input id="ceGetAccountName" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_account_name' ) ); ?>">
			<input id="ce-disconnect-url" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'disconnect' ) ); ?>">
			<div id="ce-modal" class="ce-hidden">
				<?php require plugin_dir_path( __FILE__ ) . 'partials/modal.php'; ?>
			</div>
			<input id="ce-extra-data-duplicates-text" type="hidden"
				   value="<?php esc_attr_e( 'Duplicate or empty keys founded in extra data mapping. Delete them to continue.', 'channelengine-integration' ); ?>">
			<input id="ce-extra-data-duplicates-header" type="hidden"
				   value="<?php esc_attr_e( 'Warning', 'channelengine-integration' ); ?>">
			<input id="ceExportProductsUrl" type="hidden"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'is_export_products_enabled' ) ); ?>">
		</div>
	</main>
</div>
