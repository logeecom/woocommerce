<?php

use ChannelEngine\Utility\Asset_Helper;

?>

<div class="ce-page ce-page-centered">
	<img src="<?php echo esc_attr( Asset_Helper::get_image_url( 'dashboard.png' ) ); ?>" alt="" />
	<div class="ce-title"><?php esc_html_e( 'All is up and running.', 'channelengine-wc' ); ?></div>
</div>
