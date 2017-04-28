<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }



class Kanban_Template
{
	// the list of kanban-related URL's
	static $page_slugs = array(
		'board' => array(
			'style'  => array(
				'bootstrap' => '%sbootstrap/css/bootstrap.min.css',
				'board' => '%scss/board.css',
			),
			'script' => array(
				'jquery' => '%sjs/min/jquery-1.11.3-min.js',
				'jquery-ui' => '%sjs/min/jquery-ui-min.js',
				'bootstrap' => '%sbootstrap/js/bootstrap.min.js',
				'bootstrap-growl' => '%sjs/min/jquery.bootstrap-growl-min.js',
				'matchHeight' => '%sjs/min/jquery.matchHeight-min.js',
				'cookie' => '%sjs/min/js.cookie-min.js',
				'notify' => '%sjs/min/notify-min.js',
				'screenfull' => '%sjs/min/screenfull-min.js',
				't' => '%sjs/min/t-min.js',
				'modal-projects' => '%sjs/min/modal-projects-min.js',
				'user' => '%sjs/min/user-min.js',
				'project' => '%sjs/min/project-min.js',
				'board' => '%sjs/min/board-min.js',
				'task' => '%sjs/min/task-min.js',
				'functions' => '%sjs/min/functions-min.js',
				'init' => '%sjs/min/init-min.js',
			),
		),
		'login' => array(
			'style' => array(
				'bootstrap' => '%sbootstrap/css/bootstrap.min.css',
			),
		),
	);



	static function init() {
		add_action( 'wp_loaded', array( __CLASS__, 'template_chooser' ) );
	}



	/**
	 * make sure only authenticated users can see our pages
	 */
	static function protect_slug( $slug ) {
		// get my id, and allowed id's
		$current_user_id = get_current_user_id();

		// $users_field_name = sprintf( '%s_user', Kanban::get_instance()->settings->basename );
		$allowed_user_ids = array_keys( Kanban_User::get_allowed_users() );

		// return if I'm allowed on *any* board
		if ( in_array( $current_user_id, $allowed_user_ids ) ) {
			// redirect away from login
			if ( $slug == 'login' ) {
				wp_redirect( Kanban_Template::get_uri() );
				exit;
			} else {
				return true;
			}
		}

		// allow for addition checks
		$can_view = apply_filters( 'kanban_template_protect_slug_check', false );

		if ( $can_view ) { return true; }

		$use_default_login_page = Kanban_Option::get_option( 'use_default_login_page' );

		// if still on login, and $use_default_login_page, redirect
		if ( $slug == 'login' && (bool) $use_default_login_page && ! is_user_logged_in() ) {
			wp_redirect(
				wp_login_url( Kanban_Template::get_uri() )
			);
			exit;
		}

		// anyone can see login screen
		if ( $slug == 'login' ) { return true; }

		// otherwise redirect to login
		if ( (bool) $use_default_login_page && ! is_user_logged_in() ) {
			wp_redirect(
				wp_login_url( Kanban_Template::get_uri() )
			);
			exit;
		} else {
			// otherwise redirect to login
			wp_redirect(
				add_query_arg(
					array(
					 	'redirect' => urlencode( $_SERVER['REQUEST_URI'] ),
					),
					Kanban_Template::get_uri( 'login' )
				)
			);
			exit;
		}

		return false;
	}



	/**
	 * routing to interpret our custom URLs
	 *
	 * @param  string $template the template being passed
	 * @return string           the template found (or not found)
	 */
	static function template_chooser() {
		if ( is_admin() ) { return; }

		$uri = strtolower( strtok( $_SERVER['REQUEST_URI'], '?' ) );

		$matches = array();
		if ( ! Kanban_Template::is_plain_permalink() ) {
			// last "kanban", optional /, capture everything up to the next /
			// /kanban, /kanban/ returns set but empty matches[1]
			// /kanban/board, /kanban/board/, /kanban/board/123 returns matches[1] = board
			$pattern = '/(?:.*kanban\/?)([^\/]*)/';

			preg_match( $pattern, $uri, $matches );

		} elseif ( isset( $_GET['kanban'] ) ) {
			$matches[1] = $_GET['kanban'];
		}

		// if url doesn't include our slug, return
		if ( empty( $matches ) ) { return; }

		$slug = $matches[1];

		self::$page_slugs = apply_filters( 'kanban_template_chooser_slugs', self::$page_slugs );

		if ( ! isset( self::$page_slugs[ $slug ] ) ) { return; }

		$continue = self::protect_slug( $slug );
		if ( ! $continue ) { return; }

		$template = sprintf( '%s/templates/%s', Kanban::get_instance()->settings->path, sprintf( '%s.php', $slug ) );

		global $wp_query;
		if ( ! isset( $wp_query->query_vars['kanban'] ) ) {
			$wp_query->query_vars['kanban'] = (object) array();
		}
		$wp_query->query_vars['kanban']->slug = $slug;

		// page found. set the header
		status_header( 200 );

		echo self::render_template( $template );
		exit;

	}



	/**
	 * higherarchy of template locations, to allow for customization
	 *
	 * @param  string $basename filename of the template we're looking for
	 * @return string           fill template path
	 */
	static function find_template( $basename ) {

		// Try to build the template path in the context of the plugin.
		$template = sprintf(
			'%s/templates/%s',
			Kanban::get_instance()->settings->path,
			sprintf(
				'%s.php', str_replace('.php', '', $basename) // Strip .php so it can be re-added safely.
			)
		);



		// If not found, use the original.
		if ( ! is_file( $template ) ) {
			if ( is_file( $basename ) ) {
				$template = $basename;
			} else {
				$template = false;
			}
		}

		return apply_filters( 'kanban_template_find_template_return', $template );
	}



	/**
	 * render an html template, and populate variables
	 *
	 * @param  string $basename the filename of the template we're looking for
	 * @param  array  $data     the variables to populate
	 * @return string           the html output
	 */
	static function render_template( $basename, $data = array() ) {

		$template_path = Kanban_Template::find_template( $basename );

		if ( ! is_file( $template_path ) ) { return false; }

		extract( $data );
		ob_start();
		include $template_path;
		$html_output = ob_get_contents();
		ob_end_clean();

		return $html_output;
	} // render_email_template



	/**
	 * add a css sheet to a kanban template, without using the WordPress queue
	 */
	static function add_style( $slug ) {

		if ( ! isset( self::$page_slugs[ $slug ] ) ) { return; }

		if ( ! isset( self::$page_slugs[ $slug ]['style'] ) ) { return; }

		foreach ( self::$page_slugs[ $slug ]['style'] as $handle => $path ) {
			if ( strpos( $path, '%s' ) !== false ) {
				$path = sprintf( $path, Kanban::get_instance()->settings->uri );
			}

			echo sprintf(
				'<link rel="stylesheet" id="%s-css" href="%s?ver=%s">' . "\n",
				$handle,
				$path,
				Kanban::get_instance()->settings->plugin_data['Version']
			);
		}
	}



	/**
	 * add a js script to a kanban template, without using the WordPress queue
	 */
	static function add_script( $slug ) {
		if ( ! isset( self::$page_slugs[ $slug ] ) ) { return; }

		if ( ! isset( self::$page_slugs[ $slug ]['script'] ) ) { return; }

		foreach ( self::$page_slugs[ $slug ]['script'] as $handle => &$path ) {
			if ( isset( $_GET['debug'] ) && $_GET['debug'] == 'script' ) {
				$path = str_replace( 'min/', '', $path );
				$path = str_replace( '-min', '', $path );
			}

			if ( strpos( $path, '%s' ) !== false ) {
				$path = sprintf( $path, Kanban::get_instance()->settings->uri );
			}

			echo sprintf(
				'<script id="%s-js" src="%s?ver=%s"></script>' . "\n",
				$handle,
				$path,
				Kanban::get_instance()->settings->plugin_data['Version']
			);
		}
	}



	/**
	 * Determines if there's a custom permalink used or not
	 *
	 * @return bool Permalink is empty
	 */
	static function is_plain_permalink() {
		$permalink_structure = get_option( 'permalink_structure' );

		// If permalink is not set, or includes index.php, then user ?kanban=board option.
		if ( empty( $permalink_structure ) || strpos($permalink_structure, 'index.php') !== FALSE ) {
			return TRUE;
		}

		return FALSE; // empty( $permalink_structure );
	}



	/**
	 * Builds Kanban urls depending on permalink
	 *
	 * @param string $page	What slug should be used
	 * @return string Url with /kanban/slug or ?kanban=slug
	 */
	static function get_uri( $page = 'board' ) {

		// Set ugly version.
		$board_uri = add_query_arg(
			array(
				Kanban::$slug => $page,
			),
			site_url() );

		// Use the pretty version instead.
		if ( ! self::is_plain_permalink() ) {
			$board_uri = sprintf(
				'%s/%s/%s',
				site_url(),
				Kanban::$slug,
				$page
			);
		}

		// Add the current board.
		if ( isset( $_GET[ 'board_id' ] ) ) {
			$board_uri = add_query_arg(
				array(
					'board_id' => (int) sanitize_text_field( $_GET[ 'board_id' ] )
				),
				$board_uri
			);
		}

		return $board_uri;
	}



	/**
	 * construct that can't be overwritten
	 */
	private function __construct() { }
} // Kanban_Template
