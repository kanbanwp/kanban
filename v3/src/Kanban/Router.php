<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Kanban_Router {

	// the instance of this object
	private static $instance;


	private $slugs = array(
		'login', // Redirect old version 2 url.
		'board',
		'calendar',
		'ajax'
	);


	/**
	 * construct that can't be overwritten
	 */
	private function __construct() {

	}

	/**
	 * routing to interpret our custom URLs
	 *
	 * @param  string $template the template being passed
	 *
	 * @return string           the template found (or not found)
	 */
	public function maybe_load_page() {

		if ( is_admin() ) {
			return;
		}

		$uri = strtolower( strtok( $_SERVER['REQUEST_URI'], '?' ) );

		$matches = array();
		if ( ! $this->is_plain_permalink() ) {
			// last "kanban", optional /, capture everything up to the next /
			// /kanban, /kanban/ returns set but empty matches[1]
			// /kanban/board, /kanban/board/, /kanban/board/123 returns matches[1] = board
			$pattern = '/(?:.*kanban\/?)([^\/]*)/';

			preg_match( $pattern, $uri, $matches );

		} elseif ( isset( $_GET['kanban'] ) ) {
			$matches[1] = $_GET['kanban'];
		}

		// if url doesn't include our slug, return
		if ( empty( $matches ) ) {
			return;
		}

		// Get the "page" part of the url.
		$slug = $matches[1];

		$this->slugs = apply_filters( 'kanban_router_maybe_load_page_slugs', $this->slugs );

		if ( ! in_array( $slug, $this->slugs ) ) {
			return;
		}

		// https://github.com/pippinsplugins/edd-sl-api-endpoint/blob/master/class-edd-sl-api-endpoint.php
		add_filter( 'option_active_plugins', array( self::$instance, 'filter_active_plugins' ) );
		add_filter( 'after_setup_theme', array( self::$instance, 'disable_widgets' ) );

		$app_file_path = apply_filters(
			'kanban_router_maybe_load_page_app_file_path',
			sprintf(
				'%s%s/index.php',
				Kanban::instance()->settings()->path,
				$slug
			),
			$slug
		);

		if ( ! is_file( $app_file_path ) ) {
			return;
		}

		header( "HTTP/1.1 200 OK" );
		status_header( 200 );

		// Prevent browser caching.
		header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

//		$continue = self::protect_page( $slug );
//		if ( ! $continue ) {
//			return;
//		}

		define( 'KANBAN_APP_DIR', dirname( $app_file_path ) );

		require_once $app_file_path;
		exit;
	}

	/**
	 * Determines if there's a custom permalink used or not
	 *
	 * @return bool Permalink is empty
	 */
	public function is_plain_permalink() {
		$permalink_structure = get_option( 'permalink_structure' );

		// If permalink is not set, or includes index.php, then user ?kanban=board option.
		if ( empty( $permalink_structure ) || strpos( $permalink_structure, 'index.php' ) !== false ) {
			return true;
		}

		return false; // empty( $permalink_structure );
	}

	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

			add_action( 'plugins_loaded', array( self::$instance, 'maybe_load_page' ) );

			add_action('template_redirect', array( self::$instance, 'redirect_to_kanban_app'));
		}

		return self::$instance;
	}

	/**
	 * If site_takeover option is enabled, redirect all non-kanban pages to board.
	 */
	public static function redirect_to_kanban_app () {
		if ( is_admin() ) return;

		$app_settings = Kanban_App_Option::instance()->get_row();


		if ( !isset($app_settings->options)) {
			return;
		}

		$app_settings->options = (object) $app_settings->options;

		if ( !isset($app_settings->options->site_takeover) || $app_settings->options->site_takeover !== true ) {
			return;
		}

		$uri = Kanban_Router::instance()->get_page_uri('board');

		wp_redirect($uri);
		exit;
	}

	public function filter_active_plugins( $active_plugins ) {
		$active_plugins = array();

		return $active_plugins;
	}

	public function disable_widgets() {
		remove_all_actions( 'widgets_init' );
	}

	public function get_uri( $page = 'board' ) {
		$path = apply_filters(
			'kanban_router_get_uri_path',
			sprintf(
				'%s%s/',
				Kanban::instance()->settings()->uri,
				$page
			),
			$page
		);

		return  $path;
	}

	public function get_path( $page = 'board' ) {
		$template_file = sprintf(
			'%s%s/',
			Kanban::instance()->settings()->path,
			$page
		);

		return  $template_file;
	}

	/**
	 * Builds Kanban urls depending on permalink
	 *
	 * @param string $page What slug should be used
	 *
	 * @return string Url with /kanban/slug or ?kanban=slug
	 */
	public function get_page_uri( $page = 'board' ) {

		// Set ugly version.
		$board_uri = add_query_arg(
			array(
				Kanban::instance()->settings()->basename => $page,
			),
			site_url() );

		// Use the pretty version instead.
		if ( ! $this->is_plain_permalink() ) {
			$board_uri = sprintf(
				'%s/%s/%s',
				site_url(),
				Kanban::instance()->settings()->basename,
				$page
			);
		}

		// Add the current board.
		if ( isset( $_GET['board_id'] ) ) {
			$board_uri = add_query_arg(
				array(
					'board_id' => (int) sanitize_text_field( $_GET['board_id'] )
				),
				$board_uri
			);
		}

		return $board_uri;
	}

} // Kanban_Router


