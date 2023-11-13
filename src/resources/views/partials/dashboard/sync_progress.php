<div class="ce-page">
	<h1><?php esc_html_e( 'Full synchronization in progress', 'channelengine-wc' ); ?></h1>
	<section id="ce-product-sync-in-progress" class="ce-sync-progress">
		<div class="label"><?php esc_html_e( 'Product synchronization:', 'channelengine-wc' ); ?></div>
		<div class="ce-progress-bar">
			<div id="ce-product-progress" class="ce-progress-bar__inner">0%</div>
			<div id="ce-product-progress-bar" class="ce-progress-bar__progress" style="clip-path: inset(0 0 0 0%);">0%
			</div>
		</div>
		<div class="ce-sync-status">
			<strong id="ce-product-synced">0</strong> <?php esc_html_e( 'of', 'channelengine-wc' ); ?>
			<strong id="ce-product-total">0</strong> <?php esc_html_e( 'products uploaded', 'channelengine-wc' ); ?>
		</div>
	</section>
	<section id="ce-order-sync-in-progress" class="ce-sync-progress">
		<div class="label"><?php esc_html_e( 'Order synchronization:', 'channelengine-wc' ); ?></div>
		<div class="ce-progress-bar">
			<div id="ce-order-progress" class="ce-progress-bar__inner">0%</div>
			<div id="ce-order-progress-bar" class="ce-progress-bar__progress" style="clip-path: inset(0 0 0 0%);">0%
			</div>
		</div>
		<div class="ce-sync-status">
			<strong id="ce-order-synced">0</strong> <?php esc_html_e( 'of', 'channelengine-wc' ); ?>
			<strong id="ce-order-total">0</strong> <?php esc_html_e( 'orders downloaded', 'channelengine-wc' ); ?>
		</div>
	</section>
</div>
