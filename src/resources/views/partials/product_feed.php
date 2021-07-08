<h1><?php echo __( 'Product synchronization settings', 'channelengine' ); ?></h1>
<p></p>
<form class="ce-form">
    <h3><?php echo __( 'Stock synchronization', 'channelengine' ); ?></h3>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Set default stock quantity', 'channelengine' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'Set the default stock level for the cases when stock is not being tracked in the shop or when information about the stock is missing for specific product(s).', 'channelengine' ); ?>
                </span>
            </span>
            <input id="ceStockQuantity" type="number" class="small-number-input" min="0" max="1000" step="1"
                   value="200">&nbsp;<?php echo __( 'psc', 'channelengine' ); ?>
        </label>
    </div>
</form>
