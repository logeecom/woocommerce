<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath           = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl            = Shop_Helper::get_plugin_page_url();
$startOnboardingUrl = Shop_Helper::get_controller_url( 'Welcome', 'start_onboarding' );

?>
<div id="ce-loader" class="ce-overlay">
	<div class="ce-loader"></div>
</div>
<div class="channel-engine ce-hidden">
	<header>
		<img src="<?php echo esc_url( Asset_Helper::get_image_url( 'logo.svg' ) ); ?>" height="30" alt="ChannelEngine"/>
	</header>
	<main>
		<div class="ce-onboarding ce-page ce-page-centered">
			<div class="ce-error-message-banner">
				<p>
					<span class="dashicons dashicons-info ce-error-message-banner-icon"></span>
					<?php
					esc_html_e(
						'Before proceeding with the onboarding process, please contact ChannelEngine support.',
						'channelengine-integration'
					);
					?>
				</p>
			</div>
			<img src="<?php echo esc_url( Asset_Helper::get_image_url( 'icon.svg' ) ); ?>" alt="ChannelEngine"
				 class="ce-icon__big"/>
			<div class="ce-title"><?php esc_html_e( 'Welcome to ChannelEngine', 'channelengine-integration' ); ?></div>
			<h2 class="ce-subtitle"><?php esc_html_e( 'It will only take 5 minutes of your time to configure the integration.', 'channelengine-integration' ); ?></h2>
			<a id="ce-configure"
			   class="ce-button ce-button__primary"><?php esc_html_e( 'Configure', 'channelengine-integration' ); ?></a>
			<input id="ceOnboardingUrl" type="hidden" value="<?php echo esc_attr( $startOnboardingUrl ); ?>">
		</div>
	</main>
</div>
