<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Shop_Helper;

?>
<input id="ce-standard-attributes-label" type="hidden" value="<?php esc_attr_e( 'WooCommerce standard fields', 'channelengine-integration' ); ?>">
<input id="ce-custom-attributes-label" type="hidden" value="<?php esc_attr_e( 'WooCommerce custom fields', 'channelengine-integration' ); ?>">
<input id="ce-other-fields-label" type="hidden" value="<?php esc_attr_e( 'Other fields', 'channelengine-integration' ); ?>">
<form class="ce-form" onsubmit="return false">
	<h3><?php esc_html_e( 'Attribute mapping', 'channelengine-integration' ); ?></h3>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Brand', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Brand attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceBrand">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Color', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Color attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceColor">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Size', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Size attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceSize">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'GTIN', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the GTIN attribute to. I.e.: a valid EAN or UPC.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceGtin">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Catalogue price', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Catalog price attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceCataloguePrice">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Price', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Price attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="cePrice">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Purchase price', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Purchase price attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="cePurchasePrice">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Shipping time', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Shipping time attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceShippingTime">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Description', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Details attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceDetails">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Category', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Category attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceCategory">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<div class="ce-input-group ce-flex">
		<label>
			<span class="label"><?php esc_html_e( 'Vendor product number', 'channelengine-integration' ); ?></span>
			<span class="ce-help">
				<span class="ce-help-tooltip">
					<?php esc_html_e( 'Select a value to map the Vendor product number attribute to.', 'channelengine-integration' ); ?>
				</span>
			</span>
			<select id="ceVendorProductNumber">
				<option value=""><?php esc_html_e( 'Not mapped', 'channelengine-integration' ); ?></option>
			</select>
		</label>
	</div>
	<input id="ceProductAttributes" type="hidden"
		   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_product_attributes' ) ); ?>">
</form>
