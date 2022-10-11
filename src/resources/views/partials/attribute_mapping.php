<?php

use ChannelEngine\Utility\Shop_Helper;

?>
<input id="ce-standard-attributes-label" type="hidden" value="<?php _e('WooCommerce standard fields', 'channelengine') ?>">
<input id="ce-custom-attributes-label" type="hidden" value="<?php _e('WooCommerce custom fields', 'channelengine') ?>">
<form class="ce-form" onsubmit="return false">
    <h3><?php echo __("Attribute mapping") ?></h3>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Brand'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Brand attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceBrand">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Color'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Color attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceColor">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Size'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Size attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceSize">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('GTIN'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the GTIN attribute to. I.e.: a valid EAN or UPC.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceGtin">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Catalogue price'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Catalog price attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceCataloguePrice">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Price'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Price attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="cePrice">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Purchase price'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Purchase price attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="cePurchasePrice">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php _e('Shipping time', 'channelengine'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Shipping time attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceShippingTime">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php _e('Description', 'channelengine'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Details attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceDetails">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Category'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Category attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceCategory">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php _e('Vendor product number', 'channelengine'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('Select a value to map the Vendor product number attribute to.', 'channelengine'); ?>
                </span>
            </span>
            <select id="ceVendorProductNumber">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <input id="ceProductAttributes" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url( 'Config', 'get_product_attributes' ); ?>">
</form>