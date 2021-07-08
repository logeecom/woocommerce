<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Frontend_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();
?>
<script src="<?php echo Asset_Helper::get_js_url( 'Transactions.js' ) ?>"></script>
<script src="<?php echo Asset_Helper::get_js_url( 'ModalService.js' ) ?>"></script>
<script src="<?php echo Asset_Helper::get_js_url( 'Details.js' ) ?>"></script>

<div id="ce-loader" class="ce-overlay">
    <div class="ce-loader"></div>
</div>
<div class="channel-engine" style="display: none;">
    <header>
        <img src="<?php echo Asset_Helper::get_image_url( 'logo.svg' ); ?>" alt="ChannelEngine"/>
    </header>

    <main>
        <nav class="nav-tab-wrapper">
            <a href="<?php echo Frontend_Helper::get_subpage_url( 'dashboard' ) ?>"
               class="nav-tab"><?php echo __( 'Dashboard', 'channelengine' ); ?></a>
            <a href="<?php echo Frontend_Helper::get_subpage_url( 'config' ) ?>"
               class="nav-tab"><?php echo __( 'Configuration', 'channelengine' ); ?></a>
            <a href="<?php echo Frontend_Helper::get_subpage_url( 'transactions' ) ?>"
               class="nav-tab nav-tab-active"><?php echo __( 'Transaction log', 'channelengine' ); ?></a>
        </nav>
        <div class="ce-page">
            <h1><?php echo __( 'Transactions history log', 'channelengine' ); ?></h1>
            <ul class="sub-page-nav">
                <li><a id="ce-product-link" href="#"
                       class="ce-current"><?php echo __( 'Product sync', 'channelengine' ); ?></a></li>
                <li><a id="ce-order-link" href="#"><?php echo __( 'Order sync', 'channelengine' ); ?></a></li>
                <li><a id="ce-errors-link" href="#"><?php echo __( 'Errors', 'channelengine' ); ?></a></li>
            </ul>
            <table class="ce-table">
                <thead>
                <tr>
                    <th scope="col"><?php echo __( 'Task Type', 'channelengine' ); ?></th>
                    <th scope="col" class="text-center"><?php echo __( 'Status', 'channelengine' ); ?></th>
                    <th scope="col"
                        class="text-center ce-table-compact-view"><?php echo __( 'Details', 'channelengine' ); ?></th>
                    <th scope="col"
                        class="text-center ce-table-full-view"><?php echo __( 'Start Time', 'channelengine' ); ?></th>
                    <th scope="col"
                        class="text-center ce-table-full-view"><?php echo __( 'Time Completed', 'channelengine' ); ?></th>
                    <th scope="col"
                        class="text-center ce-table-full-view"><?php echo __( 'Details', 'channelengine' ); ?></th>
                </tr>
                </thead>
                <tbody id="ce-table-body">
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="10">
                        <div class="ce-table-pagination">
                            <div class="ce-horizontal">
                                <div class="ce-pagination-status">
									<?php echo __( 'Displayed', 'channelengine' ); ?>
                                    <strong id="ce-logs-from">1</strong> <?php echo __( 'to', 'channelengine' ); ?>
                                    <strong id="ce-logs-to">17</strong> <?php echo __( 'of', 'channelengine' ); ?>
                                    <strong id="ce-logs-total">17</strong>
                                </div>
                                <div class="ce-page-size">
                                    <label><?php echo __( 'Page size:', 'channelengine' ); ?>
                                        <select id="ce-page-size">
                                            <option value="10" selected>10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            <div class="ce-pagination-pages">
                                <button class="ce-button ce-button__prev"
                                        title="<?php echo __( 'Go to the previous page', 'channelengine' ); ?>">
                                    <span class="ce-table-compact-view"><</span>
                                    <span class="ce-table-full-view"><?php echo __( 'Previous', 'channelengine' ); ?></span>
                                </button>
                                <button class="ce-button ce-button__next"
                                        title="<?php echo __( 'Go to the next page', 'channelengine' ); ?>">
                                    <span class="ce-table-compact-view">></span>
                                    <span class="ce-table-full-view"><?php echo __( 'Next', 'channelengine' ); ?></span>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
        <input id="ce-transactions-get" type="hidden"
               value="<?php echo Shop_Helper::get_controller_url( 'Transactions', 'get' ); ?>">
        <input id="ce-details-get" type="hidden"
               value="<?php echo Shop_Helper::get_controller_url( 'Transactions', 'get_details' ) ?>">
        <input id="ce-view-details-translation" type="hidden"
               value="<?php echo __( 'View details', 'channelengine' ); ?>">
        <input id="ce-start-translation" type="hidden" value="<?php echo __( 'Start time', 'channelengine' ); ?>">
        <input id="ce-completed-translation" type="hidden"
               value="<?php echo __( 'Time completed', 'channelengine' ); ?>">
        <input id="ce-modal-header" type="hidden" value="<?php echo __( 'Transaction log details', 'channelengine' ); ?>">
        <input id="ce-modal-button-text" type="hidden" value="<?php echo __( 'Close', 'channelengine' ); ?>">
        <input id="ce-details-identifier" type="hidden" value="<?php echo __( 'Identifier', 'channelengine' ); ?>">
        <input id="ce-details-message" type="hidden" value="<?php echo __( 'Message', 'channelengine' ); ?>">
        <input id="ce-details-display" type="hidden" value="<?php echo __( 'Displayed', 'channelengine' ); ?>">
        <input id="ce-details-to" type="hidden" value="<?php echo __( 'to', 'channelengine' ); ?>">
        <input id="ce-details-from" type="hidden" value="<?php echo __( 'of', 'channelengine' ); ?>">
        <input id="ce-details-page-size" type="hidden" value="<?php echo __( 'Page size:', 'channelengine' ); ?>">
        <input id="ce-details-go-to-previous" type="hidden"
               value="<?php echo __( 'Go to previous page', 'channelengine' ); ?>">
        <input id="ce-details-previous" type="hidden" value="<?php echo __( 'Previous', 'channelengine' ); ?>">
        <input id="ce-details-go-to-next" type="hidden" value="<?php echo __( 'Go to next page', 'channelengine' ); ?>">
        <input id="ce-details-next" type="hidden" value="<?php echo __( 'Next', 'channelengine' ); ?>">
        <input id="ce-no-results" type="hidden" value="<?php echo __( 'No results', 'channelengine' ); ?>">
    </main>
    <div id="ce-modal" style="display: none">
		<?php include plugin_dir_path( __FILE__ ) . 'partials/modal.php' ?>
    </div>
</div>