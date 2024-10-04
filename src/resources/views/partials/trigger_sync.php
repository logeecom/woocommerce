<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Asset_Helper;

?>
<div class="ce-modal">
	<div class="ce-modal-dialog ce-modal-xl">
		<div class="ce-modal-content">
			<header>
				<h3><?php esc_html_e( 'Manually trigger synchronization', 'channelengine-integration' ); ?></h3>
				<span class="ce-close-modal ce-close-button">✕</span>
			</header>
			<main>
				<h3>
					<?php esc_html_e( 'Choose what you would like to synchronize:', 'channelengine-integration' ); ?>
				</h3>
				<div>
					<div class="ce-input-group">
						<label>
							<span class="label"><?php esc_html_e( 'Synchronize products', 'channelengine-integration' ); ?></span>
							<span class="ce-help">
								<span class="ce-help-tooltip">
									<?php esc_html_e( 'The integration will synchronize all published shop products to ChannelEngine in the background.', 'channelengine-integration' ); ?>
								</span>
							</span>
							<input id="ce-product-sync-checkbox" type="checkbox">
						</label>
					</div>
					<div class="ce-input-group">
						<label>
							<span class="label"><?php esc_html_e( 'Synchronize orders', 'channelengine-integration' ); ?></span>
							<span class="ce-help">
								<span class="ce-help-tooltip">
									<?php esc_html_e( 'The integration will synchronize new and closed orders (fulfilled by the merchant and fulfilled by the marketplace) from ChannelEngine into the shop.', 'channelengine-integration' ); ?>
								</span>
							</span>
							<input id="ce-order-sync-checkbox" type="checkbox">
						</label>
					</div>
				</div>
			</main>
			<footer>
				<button id="ce-start-sync-btn" class="ce-button ce-button__primary ce-close-modal" disabled>
					<?php esc_html_e( 'Start sync now', 'channelengine-integration' ); ?>
				</button>
				<button class="ce-button ce-button__secondary ce-close-modal">
					<?php esc_html_e( 'Skip sync', 'channelengine-integration' ); ?>
				</button>
			</footer>
		</div>
	</div>
</div>
