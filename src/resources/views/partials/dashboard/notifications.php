<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

?>
<div class="ce-page ce-horizontal ce-notifications-page">
	<img src="<?php echo esc_url_raw( Asset_Helper::get_image_url( 'warning.png' ) ); ?>" alt="" class="ce-icon__big">
	<div class="ce-notifications">
		<h2><?php esc_html_e( 'Notifications', 'channelengine-integration' ); ?></h2>
		<div class="ce-notifications__items">
			<div class="ce-notifications__load-more">
				<button class="ce-button ce-button__primary"><?php esc_html_e( 'Load more', 'channelengine-integration' ); ?></button>
			</div>
		</div>
	</div>
	<input id="ce-notifications-url" type="hidden"
		   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Notifications', 'get' ) ); ?>">
	<input id="ce-details-url" type="hidden"
		   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Notifications', 'show_details' ) ); ?>">
	<input id="ce-details-get" type="hidden"
		   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Transactions', 'get_details' ) ); ?>">
	<input id="ce-show-details-text" type="hidden" value="<?php esc_attr_e( 'Show details', 'channelengine-integration' ); ?>">
	<input id="ce-details-header" type="hidden" value="<?php esc_attr_e( 'Transaction log details', 'channelengine-integration' ); ?>">
	<input id="ce-modal-button-text" type="hidden" value="<?php esc_attr_e( 'Close', 'channelengine-integration' ); ?>">
	<input id="ce-details-identifier" type="hidden" value="<?php esc_attr_e( 'Identifier', 'channelengine-integration' ); ?>">
	<input id="ce-details-message" type="hidden" value="<?php esc_attr_e( 'Message', 'channelengine-integration' ); ?>">
	<input id="ce-details-display" type="hidden" value="<?php esc_attr_e( 'Displayed', 'channelengine-integration' ); ?>">
	<input id="ce-details-to" type="hidden" value="<?php esc_attr_e( 'to', 'channelengine-integration' ); ?>">
	<input id="ce-details-from" type="hidden" value="<?php esc_attr_e( 'of', 'channelengine-integration' ); ?>">
	<input id="ce-details-page-size" type="hidden" value="<?php esc_attr_e( 'Page size:', 'channelengine-integration' ); ?>">
	<input id="ce-details-go-to-previous" type="hidden"
		   value="<?php esc_attr_e( 'Go to previous page', 'channelengine-integration' ); ?>">
	<input id="ce-details-previous" type="hidden" value="<?php esc_attr_e( 'Previous', 'channelengine-integration' ); ?>">
	<input id="ce-details-go-to-next" type="hidden" value="<?php esc_attr_e( 'Go to next page', 'channelengine-integration' ); ?>">
	<input id="ce-details-next" type="hidden" value="<?php esc_attr_e( 'Next', 'channelengine-integration' ); ?>">
	<input id="ce-notifications-offset" type="hidden" value="0">
</div>
