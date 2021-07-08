<?php

namespace ChannelEngine\Utility;

/**
 * Class Frontend_Helper
 *
 * @package ChannelEngine\Utility
 */
class Frontend_Helper {

	/**
	 * Retrieves subpage url.
	 *
	 * @param string $subpage
	 *
	 * @return string
	 */
	public static function get_subpage_url( $subpage ) {
		return admin_url( 'admin.php?page=channel-engine&subpage=' . $subpage );
	}

	/**
	 * Renders subpage view.
	 *
	 * @param string $current_page
	 */
	public static function render_view( $current_page ) {
		$subpage = isset( $_REQUEST['subpage'] ) ? $_REQUEST['subpage'] : '';
		if ( $subpage && $subpage !== $current_page ) {
			echo View::file( '/' . $subpage . '.php' )->render();
		}
	}
}