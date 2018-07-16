<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Kanban_Template {

	// the instance of this object
	private static $instance;

	/**
	 * construct that can't be overwritten
	 */
	private function __construct() {

	}

	public function include_from_path ($path) {
		$dir = new RecursiveDirectoryIterator( $path );
		foreach ( new RecursiveIteratorIterator( $dir ) as $filename => $file ) {
			if ( substr( basename( $filename ), 0, 1 ) == '.' ) {
				continue;
			}

			include_once $filename;

		}
	}


	public function render( $file, $tags = array() ) {

		if ( !file_exists( $file ) ) {
			die ( 'Error:Template file ' . $file . ' not found' );
		}

		if ( !empty($tags) ) {
			extract($tags);
		}

		ob_start();

		include( $file );

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

		}

		return self::$instance;
	}
}