<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath           = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl            = Shop_Helper::get_plugin_page_url();
$startOnboardingUrl = Shop_Helper::get_controller_url( 'Welcome', 'start_onboarding' );

?>
<div id="ce-loader" class="ce-overlay">
    <div class="ce-loader"></div>
</div>
<div class="channel-engine" style="display: none;">
    <header>
        <img src="<?php echo Asset_Helper::get_image_url( 'logo.svg' ); ?>" height="30" alt="ChannelEngine" />
    </header>
    <main>
        <div class="ce-onboarding ce-page ce-page-centered">
            <img src="<?php echo Asset_Helper::get_image_url( 'icon.svg' ); ?>" alt="ChannelEngine" 
                 class="ce-icon__big"/>
            <div class="ce-title"><?php echo __( 'Welcome to ChannelEngine', 'channelengine' ); ?></div>
            <h2 class="ce-subtitle"><?php echo __( 'It will only take 5 minutes of your time to configure the integration.', 'channelengine' ); ?></h2>
            <a id="ce-configure"
               class="ce-button ce-button__primary"><?php echo __( 'Configure', 'channelengine' ); ?></a>
            <input id="ceOnboardingUrl" type="hidden" value="<?php echo $startOnboardingUrl; ?>">
        </div>
    </main>
</div>