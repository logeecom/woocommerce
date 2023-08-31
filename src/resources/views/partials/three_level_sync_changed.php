<?php
use ChannelEngine\Utility\Asset_Helper;
?>

<div class="ce-modal">
    <script src="<?php echo Asset_Helper::get_js_url( 'TriggerSyncModal.js' ) ?>"></script>
    <div class="ce-modal-dialog ce-modal-xl">
        <div class="ce-modal-content">
            <header>
                <h3><?php echo __( 'Start product synchronization', 'channelengine-wc' ); ?></h3>
                <span class="ce-close-modal ce-close-button ce-close-modal-button">✕</span>
            </header>
            <main>
                <div>
                    <?php echo __( 'Please note that by changing the three-level synchronization configuration the initial product synchronization will be started in the background.', 'channelengine-wc' ); ?>
                </div>
            </main>
            <footer>
                <button id="ce-cancel-change-three-level-sync-btn" class="ce-button ce-button__secondary ce-close-modal ce-close-modal-button">
                    <?php echo __( 'Cancel', 'channelengine-wc' ); ?>
                </button>
                <button class="ce-button ce-button__primary ce-close-modal">
                    <?php echo __( 'Save configuration', 'channelengine-wc' ); ?>
                </button>
            </footer>
        </div>
    </div>
</div>