<?php

use ChannelEngine\Utility\Asset_Helper;

?>
<header>
	<div class="ce-header">
		<div>
			<img src="<?php echo esc_attr( Asset_Helper::get_image_url( 'logo.svg' ) ); ?>" height="30" alt="ChannelEngine" />
		</div>
		<div class="ce-account-name">
			<?php esc_html_e( 'Account name: ', 'channelengine-wc' ); ?>
			<div id="ceAccountNameHeader" class="ce-account-name-field" style="margin-left: 5px; margin-right: 5px;"></div>
			<?php esc_html_e( ' (', 'channelengine-wc' ); ?>
			<a href="#" id="ceDisconnectLink"><?php esc_html_e( 'Disconnect', 'channelengine-wc' ); ?></a>
			<?php esc_html_e( ')', 'channelengine-wc' ); ?>
		</div>
	</div>
</header>
