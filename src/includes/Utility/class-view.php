<?php

namespace ChannelEngine\Utility;

use ChannelEngine\ChannelEngine;

class View {
	const VIEW_FOLDER_PATH = '/resources/views';

	/**
	 * @var string
	 */
	private $file;

	private function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Returns view instance if the provided file exists.
	 *
	 * @param $view_name
	 *
	 * @return View
	 */
	public static function file( $view_name ) {
		$file = ChannelEngine::get_plugin_dir_path() . self::VIEW_FOLDER_PATH . $view_name;
		if ( file_exists( $file ) ) {
			return new self( $file );
		}

		throw new \RuntimeException( esc_html( "Could not find specified view file: {$view_name}" ) );
	}

	/**
	 * Render page.
	 *
	 * @param array $data
	 *
	 * @return false|string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function render( $data = array() ) {
		ob_start();

		require $this->file;

		return ob_get_clean();
	}

	/**
	 * Get allowed HTML tags
	 *
	 * @return array
	 */
	public static function get_allowed_tags() {
		return array(
			'a'          => array(
				'id'     => array(),
				'class'  => array(),
				'href'   => array(),
				'rel'    => array(),
				'title'  => array(),
				'target' => array(),
			),
			'abbr'       => array(
				'title' => array(),
			),
			'b'          => array(),
			'blockquote' => array(
				'cite' => array(),
			),
			'br'         => array(),
			'button'     => array(
				'class'    => array(),
				'id'       => array(),
				'disabled' => array(),
			),
			'cite'       => array(
				'title' => array(),
			),
			'code'       => array(),
			'del'        => array(
				'datetime' => array(),
				'title'    => array(),
			),
			'dd'         => array(),
			'div'        => array(
				'class' => array(),
				'id'    => array(),
				'title' => array(),
				'style' => array(),
			),
			'dl'         => array(),
			'dt'         => array(),
			'em'         => array(),
			'form'       => array(
				'class'    => array(),
				'id'       => array(),
				'onsubmit' => array(),
			),
			'footer'     => array(
				'class' => array(),
				'id'    => array(),
			),
			'h1'         => array(
				'class' => array(),
				'id'    => array(),
				'title' => array(),
				'style' => array(),
			),
			'h2'         => array(
				'class' => array(),
				'id'    => array(),
				'title' => array(),
				'style' => array(),
			),
			'h3'         => array(
				'class' => array(),
				'id'    => array(),
				'title' => array(),
				'style' => array(),
			),
			'h4'         => array(
				'class' => array(),
				'id'    => array(),
				'title' => array(),
				'style' => array(),
			),
			'h5'         => array(
				'class' => array(),
				'id'    => array(),
				'title' => array(),
				'style' => array(),
			),
			'h6'         => array(
				'class' => array(),
				'id'    => array(),
				'title' => array(),
				'style' => array(),
			),
			'header'     => array(
				'class' => array(),
			),
			'hr'         => array(
				'class' => array(),
			),
			'i'          => array(
				'class' => array(),
			),
			'img'        => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'input'      => array(
				'id'           => array(),
				'class'        => array(),
				'name'         => array(),
				'value'        => array(),
				'type'         => array(),
				'autocomplete' => array(),
				'style'        => array(),
				'checked'      => array(),
			),
			'li'         => array(
				'class' => array(),
			),
			'label'      => array(
				'class' => array(),
			),
			'main'       => array(
				'class' => array(),
			),
			'nav'        => array(
				'class' => array(),
				'id'    => array(),
			),
			'ol'         => array(
				'class' => array(),
			),
			'option'     => array(
				'value'    => array(),
				'selected' => array(),
			),
			'p'          => array(
				'class' => array(),
				'id'    => array(),
			),
			'path'       => array(
				'fill'            => array(),
				'd'               => array(),
				'class'           => array(),
				'data-v-19c3f3ae' => array(),
			),
			'q'          => array(
				'cite'  => array(),
				'title' => array(),
			),
			'script'     => array(
				'type' => array(),
				'id'   => array(),
			),
			'section'    => array(
				'id'    => array(),
				'class' => array(),
			),
			'select'     => array(
				'id'    => array(),
				'class' => array(),
			),
			'span'       => array(
				'id'          => array(),
				'class'       => array(),
				'title'       => array(),
				'style'       => array(),
				'data-tip'    => array(),
				'data-target' => array(),
			),
			'strike'     => array(),
			'strong'     => array(
				'id'    => array(),
				'class' => array(),
			),
			'svg'        => array(
				'aria-hidden'     => array(),
				'focusable'       => array(),
				'data-prefix'     => array(),
				'data-icon'       => array(),
				'role'            => array(),
				'xmlns'           => array(),
				'viewbox'         => array(),
				'class'           => array(),
				'data-v-19c3f3ae' => array(),
			),
			'table'      => array(
				'id'    => array(),
				'class' => array(),
			),
			'tbody'      => array(
				'id'    => array(),
				'class' => array(),
			),
			'thead'      => array(
				'id'    => array(),
				'class' => array(),
			),
			'tr'         => array(
				'class'     => array(),
				'data-name' => array(),
			),
			'td'         => array(
				'class'   => array(),
				'colspan' => array(),
			),
			'ul'         => array(
				'id'    => array(),
				'class' => array(),
			),
		);
	}
}
