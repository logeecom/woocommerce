<?php

namespace ChannelEngine\Lib;

use RuntimeException;

/**
 * Class Resource_Copier
 *
 * @package ChannelEngine\Lib
 */
class Resource_Copier {
	/**
	 * Copies resources from vendor to resources directory.
	 */
	public static function copy() {
		$from_base = __DIR__ . '/../vendor/channelengine/integration-core/src/BusinessLogic/Resources/';
		$to_base   = __DIR__ . '/../resources/';

		$map = array(
			$from_base . 'js' => $to_base . 'channelengine/js',
		);

		foreach ( $map as $from => $to ) {
			self::copy_directory( $from, $to );
		}
	}

	/**
	 * Copies directory.
	 *
	 * @param string $src Source directory.
	 * @param string $dst Destination directory.
	 */
	private static function copy_directory( $src, $dst ) {
		$dir = opendir( $src );
		self::mkdir( $dst );

		$file = readdir( $dir );

		while ( false !== ( $file ) ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					self::mkdir( $dst . '/' . $file );

					self::copy_directory( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}

			$file = readdir( $dir );
		}

		closedir( $dir );
	}

	/**
	 * Creates directory.
	 *
	 * @param string $destination Destination directory.
	 *
	 * @throws RuntimeException If directory can not be created.
	 */
	private static function mkdir( $destination ) {
		if ( ! file_exists( $destination ) && ! mkdir( $destination ) && ! is_dir( $destination ) ) {
			throw new RuntimeException( sprintf( 'Directory "%s" was not created', $destination ) );
		}
	}
}
