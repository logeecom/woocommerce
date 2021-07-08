<div class="ce-page">
    <h1><?php echo __( 'Full synchronization in progress', 'channelengine' ); ?></h1>
    <section id="ce-product-sync-in-progress" class="ce-sync-progress">
        <div class="label"><?php echo __( 'Product synchronization:', 'channelengine' ); ?></div>
        <div class="ce-progress-bar">
            <div id="ce-product-progress" class="ce-progress-bar__inner">0%</div>
            <div id="ce-product-progress-bar" class="ce-progress-bar__progress" style="clip-path: inset(0 0 0 0%);">0%
            </div>
        </div>
        <div class="ce-sync-status">
            <strong id="ce-product-synced">0</strong> <?php echo __( 'of', 'channelengine' ); ?>
            <strong id="ce-product-total">0</strong> <?php echo __( 'products uploaded', 'channelengine' ); ?>
        </div>
    </section>
    <section id="ce-order-sync-in-progress" class="ce-sync-progress">
        <div class="label"><?php echo __( 'Order synchronization:', 'channelengine' ); ?></div>
        <div class="ce-progress-bar">
            <div id="ce-order-progress" class="ce-progress-bar__inner">0%</div>
            <div id="ce-order-progress-bar" class="ce-progress-bar__progress" style="clip-path: inset(0 0 0 0%);">0%
            </div>
        </div>
        <div class="ce-sync-status">
            <strong id="ce-order-synced">0</strong> <?php echo __( 'of', 'channelengine' ); ?>
            <strong id="ce-order-total">0</strong> <?php echo __( 'orders downloaded', 'channelengine' ); ?>
        </div>
    </section>
</div>
