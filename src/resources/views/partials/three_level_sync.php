<?php
?>

<input id="ce-standard-attributes-label" type="hidden" value="<?php _e('WooCommerce standard fields', 'channelengine-wc' ) ?>">
<input id="ce-custom-attributes-label" type="hidden" value="<?php _e('WooCommerce custom fields', 'channelengine-wc' ) ?>">
<form class="ce-form" onsubmit="return false">
    <h3><?php echo __("Three-level synchronization") ?></h3>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Enable three-level synchronization (Zalando)', 'channelengine-wc' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'By enabling this feature all your products will be synchronized in a three-level structure. The virtual products will be created using the selected attribute below as a parent attribute.', 'channelengine-wc' ); ?>
                </span>
            </span>
            <input id="enableThreeLevelSync" type="checkbox" class="checkbox" checked>
        </label>
    </div>
    <div class="ce-input-group">
        <label>
            <span class="label"><?php echo __( 'Three-level synchronization attribute', 'channelengine-wc' ); ?></span>
            <span class="ce-help">
                <span class="ce-help-tooltip">
                    <?php echo __( 'Please select the attribute which should be used for creating the parent in the three-level product structure.', 'channelengine-wc' ); ?>
                </span>
            </span>
            <select id="ceThreeLevelSyncAttribute">
            </select>
        </label>
    </div>
</form>