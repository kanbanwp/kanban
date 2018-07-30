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

	public function ajax_load () {

		$file = sprintf(
			'%s%s.php',
			Kanban_Router::instance()->get_path(''),
			sanitize_text_field($_POST['path'])
		);

		if ( !is_file($file) ) exit;

		$this->include_from_path($file);
		exit;
	}

	public function include_from_path ($path) {
		if ( is_file($path)) {
			include_once $path;
			return;
		}

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