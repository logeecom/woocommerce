<?php

use ChannelEngine\Utility\Shop_Helper;

?>

<form class="ce-form" onsubmit="return false">
    <h3><?php echo __("Attribute mapping") ?></h3>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Brand'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('This is mapping for brand attribute. Default selected value (if exists) is brand attribute value assigned to a product as: global or custom attribute.'); ?>
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
                    <?php echo __('This is mapping for color attribute. Default selected value (if exists) is color attribute value assigned to a product as: global or custom attribute.'); ?>
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
                    <?php echo __('This is mapping for size attribute. Default selected value (if exists) is size attribute value assigned to a product as: global or custom attribute.'); ?>
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
                    <?php echo __('This is mapping for GTIN attribute. Default selected value (if exists) is ean or gtin attribute value assigned to a product as: global or custom attribute.'); ?>
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
                    <?php echo __('This is mapping for catalogue price attribute. Default selected value (if exists) is msrp or manufacturer_price or vendor_price attribute value assigned to a product as: global or custom attribute.'); ?>
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
                    <?php echo __('This is mapping for price attribute. Default selected value (if exists) is Product data > General > Sale price if Sale price is active for a product, otherwise, Product data > General > Price. If mapping value is not selected, price attribute value will be set to WooCommerce product price value.'); ?>
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
                    <?php echo __('This is mapping for purchase price attribute. Default selected value (if exists) is purchase_price attribute value assigned to a product as: global or custom attribute.'); ?>
                </span>
            </span>
            <select id="cePurchasePrice">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <div class="ce-input-group ce-flex">
        <label>
            <span class="label"><?php echo __('Details'); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __('This is mapping for details attribute. Default selected value (if exists) is plain text representation of the product description (description content without HTML tags).'); ?>
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
                    <?php echo __('This is mapping for category attribute. Default selected value (if exists) is most specific product category trail (category with the most ancestors).'); ?>
                </span>
            </span>
            <select id="ceCategory">
                <option value=""><?php _e('Not mapped', 'channelengine') ?></option>
            </select>
        </label>
    </div>
    <input id="ceProductAttributes" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url( 'Config', 'get_product_attributes' ); ?>">
</form>