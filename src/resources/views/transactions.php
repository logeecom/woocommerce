<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Frontend_Helper;
use ChannelEngine\Utility\Shop_Helper;

$basePath = Shop_Helper::get_plugin_resources_path( __DIR__ );
$baseUrl  = Shop_Helper::get_plugin_page_url();
?>
<div id="ce-loader" class="ce-overlay">
	<div class="ce-loader"></div>
</div>
<div class="channel-engine ce-hidden">
	<?php require plugin_dir_path( __FILE__ ) . 'partials/header.php'; ?>
	<main>
		<nav class="nav-tab-wrapper">
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'dashboard' ) ); ?>"
			   class="nav-tab"><?php esc_html_e( 'Dashboard', 'channelengine-wc' ); ?></a>
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'config' ) ); ?>"
			   class="nav-tab"><?php esc_html_e( 'Configuration', 'channelengine-wc' ); ?></a>
			<a href="<?php echo esc_attr( Frontend_Helper::get_subpage_url( 'transactions' ) ); ?>"
			   class="nav-tab nav-tab-active"><?php esc_html_e( 'Transaction log', 'channelengine-wc' ); ?></a>
		</nav>
		<div class="ce-page">
			<h1><?php esc_html_e( 'Transactions history log', 'channelengine-wc' ); ?></h1>
			<ul class="sub-page-nav">
				<li><a id="ce-product-link" href="#"
					   class="ce-current"><?php esc_html_e( 'Product sync', 'channelengine-wc' ); ?></a></li>
				<li><a id="ce-order-link" href="#"><?php esc_html_e( 'Order sync', 'channelengine-wc' ); ?></a></li>
				<li><a id="ce-errors-link" href="#"><?php esc_html_e( 'Errors', 'channelengine-wc' ); ?></a></li>
			</ul>
			<table class="ce-table">
				<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Task Type', 'channelengine-wc' ); ?></th>
					<th scope="col" class="text-center"><?php esc_html_e( 'Status', 'channelengine-wc' ); ?></th>
					<th scope="col"
						class="text-center ce-table-compact-view"><?php esc_html_e( 'Details', 'channelengine-wc' ); ?></th>
					<th scope="col"
						class="text-center ce-table-full-view"><?php esc_html_e( 'Start Time', 'channelengine-wc' ); ?></th>
					<th scope="col"
						class="text-center ce-table-full-view"><?php esc_html_e( 'Time Completed', 'channelengine-wc' ); ?></th>
					<th scope="col"
						class="text-center ce-table-full-view"><?php esc_html_e( 'Details', 'channelengine-wc' ); ?></th>
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
									<?php esc_html_e( 'Displayed', 'channelengine-wc' ); ?>
									<strong id="ce-logs-from">1</strong> <?php esc_html_e( 'to', 'channelengine-wc' ); ?>
									<strong id="ce-logs-to">17</strong> <?php esc_html_e( 'of', 'channelengine-wc' ); ?>
									<strong id="ce-logs-total">17</strong>
								</div>
								<div class="ce-page-size">
									<label><?php esc_html_e( 'Page size:', 'channelengine-wc' ); ?>
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
										title="<?php esc_html_e( 'Go to the previous page', 'channelengine-wc' ); ?>">
									<span class="ce-table-compact-view"><</span>
									<span class="ce-table-full-view"><?php esc_html_e( 'Previous', 'channelengine-wc' ); ?></span>
								</button>
								<button class="ce-button ce-button__next"
										title="<?php esc_html_e( 'Go to the next page', 'channelengine-wc' ); ?>">
									<span class="ce-table-compact-view">></span>
									<span class="ce-table-full-view"><?php esc_html_e( 'Next', 'channelengine-wc' ); ?></span>
								</button>
							</div>
						</div>
					</td>
				</tr>
				</tfoot>
			</table>
		</div>
		<input id="ce-transactions-get" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Transactions', 'get' ) ); ?>">
		<input id="ce-details-get" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Transactions', 'get_details' ) ); ?>">
		<input id="ceGetAccountName" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'get_account_name' ) ); ?>">
		<input id="ce-disconnect-url" type="hidden"
			   value="<?php echo esc_attr( Shop_Helper::get_controller_url( 'Config', 'disconnect' ) ); ?>">
		<input id="ce-view-details-translation" type="hidden"
			   value="<?php echo esc_attr_e( 'View details', 'channelengine-wc' ); ?>">
		<input id="ce-start-translation" type="hidden"
			   value="<?php echo esc_attr_e( 'Start time', 'channelengine-wc' ); ?>">
		<input id="ce-completed-translation" type="hidden"
			   value="<?php echo esc_attr_e( 'Time completed', 'channelengine-wc' ); ?>">
		<input id="ce-modal-header" type="hidden"
			   value="<?php esc_attr_e( 'Transaction log details', 'channelengine-wc' ); ?>">
		<input id="ce-modal-button-text" type="hidden" value="<?php esc_attr_e( 'Close', 'channelengine-wc' ); ?>">
		<input id="ce-details-identifier" type="hidden"
			   value="<?php esc_attr_e( 'Identifier', 'channelengine-wc' ); ?>">
		<input id="ce-details-message" type="hidden" value="<?php esc_attr_e( 'Message', 'channelengine-wc' ); ?>">
		<input id="ce-details-display" type="hidden" value="<?php esc_attr_e( 'Displayed', 'channelengine-wc' ); ?>">
		<input id="ce-details-to" type="hidden" value="<?php esc_attr_e( 'to', 'channelengine-wc' ); ?>">
		<input id="ce-details-from" type="hidden" value="<?php esc_attr_e( 'of', 'channelengine-wc' ); ?>">
		<input id="ce-details-page-size" type="hidden" value="<?php esc_attr_e( 'Page size:', 'channelengine-wc' ); ?>">
		<input id="ce-details-go-to-previous" type="hidden"
			   value="<?php esc_attr_e( 'Go to previous page', 'channelengine-wc' ); ?>">
		<input id="ce-details-previous" type="hidden" value="<?php esc_attr_e( 'Previous', 'channelengine-wc' ); ?>">
		<input id="ce-details-go-to-next" type="hidden"
			   value="<?php esc_attr_e( 'Go to next page', 'channelengine-wc' ); ?>">
		<input id="ce-details-next" type="hidden" value="<?php esc_attr_e( 'Next', 'channelengine-wc' ); ?>">
		<input id="ce-no-results" type="hidden" value="<?php esc_attr_e( 'No results', 'channelengine-wc' ); ?>">
	</main>
	<div id="ce-modal" class="ce-hidden">
		<?php require plugin_dir_path( __FILE__ ) . 'partials/modal.php'; ?>
	</div>
</div>
