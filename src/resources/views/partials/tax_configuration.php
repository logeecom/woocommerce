<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

?>
<form class="ce-form" onsubmit="return false">
    <div class="ce-extra-data-heading">
        <h3 style="width: 200px"><?php esc_html_e( 'Tax configuration', 'channelengine-integration' ); ?></h3>
    </div>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php esc_html_e( 'Import VAT-excluded order prices', 'channelengine-integration' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php esc_html_e( 'If checked, prices excluding VAT are imported from ChannelEngine during order synchronization.', 'channelengine-integration' ); ?>
                </span>
            </span>
            <input id="enableVatExcludedPrices" type="checkbox" class="checkbox">
        </label>
    </div>
    <div id="vatExcludedPricesMessage" class="channel-engine ce-warning ce-hidden">
        <p>
            <?php
            esc_html_e(
                'If import VAT-excluded prices is chosen, all orders will be imported without any taxes, despite the configuration in the WooCommerce.',
                'channelengine-integration'
            );
            ?>
        </p>
    </div>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php esc_html_e( 'Recalculate taxes using WooCommerce values', 'channelengine-integration' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php esc_html_e( "If checked, recalculate taxes using WooCommerce values from the configuration in WooCommerce > Settings > Tax > Standard rates. If location isn't taxable, use ChannelEngine taxes.", 'channelengine-integration' ); ?>
                </span>
            </span>
            <input id="enableWCTaxCalculation" type="checkbox" class="checkbox">
        </label>
    </div>
</form>
