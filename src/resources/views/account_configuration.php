<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();
?>
<div id="ce-loader" class="ce-overlay">
	<div class="ce-loader"></div>
</div>
<div class="channel-engine ce-hidden">
	<header>
		<img src="<?php echo esc_attr( Asset_Helper::get_image_url( 'logo.svg' ) ); ?>" height="30" alt="ChannelEngine" />
	</header>
	<main>
		<div class="ce-onboarding">
			<div class="ce-onboarding-steps">
				<div class="ce-step active">
					<div class="ce-step-number">1</div>
					<div class="ce-step-title"><?php esc_html_e( 'Account', 'channelengine-integration' ); ?></div>
				</div>
				<div class="ce-step">
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
			<?php
			$pageTitle = '';
			require plugin_dir_path( __FILE__ ) . 'partials/account.php'
			?>
			<a id="ceAuth"
			   class="ce-button ce-button__primary"><?php esc_html_e( 'Connect and continue', 'channelengine-integration' ); ?></a>
			<input type="hidden" id="ceAuthUrl"
				   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Auth', 'auth' ) ); ?>">
		</div>
	</main>
</div>
