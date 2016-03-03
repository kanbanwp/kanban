<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Template::init();



class Kanban_Template
{
	// the instance of this object
	private static $instance;



	// the list of kanban-related URL's
	static $page_slugs = array(
		'board' => array(
			'style'  => array(
				'bootstrap' => '%sbootstrap/css/bootstrap.min.css', // "//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js",
				'board'     => '%scss/board.css'
			),
			'script' => array(
				'jquery'               => '%sjs/jquery-1.11.3.min.js', // "https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js",
				'jquery-ui'            => '%sjs/jquery-ui.min.js', // "//code.jquery.com/ui/1.11.3/jquery-ui.min.js",
				'bootstrap'            => '%sbootstrap/js/bootstrap.min.js', // "//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js,
				'bootstrap-growl'      => '%sjs/jquery.bootstrap-growl.min.js', // "//cdnjs.cloudflare.com/ajax/libs/bootstrap-growl/1.0.0/jquery.bootstrap-growl.min.js",
				// 'autoresize' => "%sjs/jquery.textarea.autoresize.min.js",
				'matchHeight'          => '%sjs/jquery.matchHeight.min.js',
				't'                    => '%sjs/t.min.js',
				'board-util'           => '%sjs/board-util.min.js',
				'board-modal-projects' => '%sjs/board-modal-projects.min.js',
				'board-sidebar-header' => '%sjs/board-sidebar-header.min.js',
				// 'board-tour' => "%sjs/board-tour.min.js",
				'board-search'         => '%sjs/board-search.min.js',
				'board-filter'         => '%sjs/board-filter.min.js',
				'board-view'           => '%sjs/board-view.min.js',
				'board-task'           => '%sjs/board-task.min.js',
				'board'                => '%sjs/board.min.js'
			)
		),
		'login' => array(
			'style' => array(
				'bootstrap' => '%sbootstrap/css/bootstrap.min.css', // "//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js",
			)
		)
	);



	static function init()
	{
		add_action( 'init', array( __CLASS__, 'protect_slug' ) );

		add_filter( 'template_include', array( __CLASS__, 'template_chooser' ), 99 );

	}



	/**
	 * make sure only authenticated users can see our pages
	 */
	static function protect_slug()
	{
		// only protect pages with our slug
		if ( strpos( $_SERVER['REQUEST_URI'], sprintf( '/%s/', Kanban::$slug ) ) === FALSE ) return;



		// admins can see anything
		// if ( current_user_can('manage_options') ) return;



		// get my id, and allowed id's
		$current_user_id = get_current_user_id();

		$users_field_name = sprintf( '%s_user', Kanban::get_instance()->settings->basename );
		$allowed_user_ids = Kanban_User::get_allowed_users();

		// return if I'm allowed
		if ( in_array( $current_user_id, array_keys( $allowed_user_ids ) ) )
		{
			// redirect away from login
			if ( strpos( $_SERVER['REQUEST_URI'], sprintf( '/%s/login', Kanban::$slug ) ) !== FALSE )
			{
				wp_redirect( sprintf( '%s/%s/board', site_url(), Kanban::$slug ) );
				exit;
			}
			else
			{
				return;
			}

		}



		// allow for addition checks
		$can_view = apply_filters ( 'kanban_template_protect_slug_check', FALSE );



		if ( $can_view ) return;



		// anyone can see login screen
		if ( strpos( $_SERVER['REQUEST_URI'], sprintf( '/%s/login', Kanban::$slug ) ) !== FALSE ) return;



		// otherwise redirect to login
		wp_redirect( sprintf( '%s/%s/login', site_url(), Kanban::$slug ) );
		exit;
	}



	/**
	 * routing to interpret our custom URLs
	 * @param  string $template the template being passed
	 * @return string           the template found (or not found)
	 */
	static function template_chooser( $template )
	{
		if ( is_admin() ) return $template;



		// if url doesn't include our slug, return
		if ( strpos( $_SERVER['REQUEST_URI'], sprintf( '/%s/', Kanban::$slug ) ) === FALSE ) return $template;



		self::$page_slugs = apply_filters( 'kanban_template_chooser_slugs', self::$page_slugs );



		foreach ( self::$page_slugs as $slug => $data )
		{
			if ( strpos( strtok( $_SERVER['REQUEST_URI'], '?' ), sprintf( '%s/%s', Kanban::$slug, $slug ) ) !== FALSE )
			{
				$template = Kanban_Template::find_template( $slug );

				if ( ! empty( $template ) )
				{
					self::get_instance()->slug = $slug;

					if ( isset( $data['style'] ) )
					{
						foreach ( $data['style'] as $handle => $path )
						{
							if ( ! isset( self::get_instance()->style ) || ! is_array( self::get_instance()->style ) )
							{
								self::get_instance()->style = array();
							}

							if ( strpos( $path, '%s' ) !== FALSE )
							{
								$path = sprintf( $path, Kanban::get_instance()->settings->uri );
							}

							self::get_instance()->style[$handle] = $path;
						}
					}

					if ( isset( $data['script'] ) )
					{
						foreach ( $data['script'] as $handle => $path )
						{
							if ( ! isset( self::get_instance()->script ) || ! is_array( self::get_instance()->script ) )
							{
								self::get_instance()->script = array();
							}

							if ( strpos( $path, '%s' ) !== FALSE )
							{
								$path = sprintf( $path, Kanban::get_instance()->settings->uri );
							}

							self::get_instance()->script[$handle] = $path;
						}
					}
				}
			}
		}

		self::get_instance()->template = $template;

		// page found. set the header
		status_header( 200 );

		// allow additional templates
		return apply_filters( 'kanban_template_chooser_return', $template );
	}



	/**
	 * higherarchy of template locations, to allow for customization
	 * @param  string $basename filename of the template we're looking for
	 * @return string           fill template path
	 */
	static function find_template( $basename )
	{
		// look for template in theme/name_of_class
		$template = sprintf( '%s/%s/%s', get_stylesheet_directory(), Kanban::get_instance()->settings->basename, sprintf( '%s.php', $basename ) );

		// if not found, look for it in the plugin
		if ( ! is_file( $template ) )
		{
			$template = sprintf( '%s/templates/%s', Kanban::get_instance()->settings->path, sprintf( '%s.php', $basename ) );
		}

		// if not found, use the theme default
		if ( ! is_file( $template ) )
		{
			$template = sprintf( '%s/%s', get_stylesheet_directory(), sprintf( '%s.php', $basename ) );
		}

		// if not found, use the original
		if ( ! is_file( $template ) )
		{
			if ( is_file( $basename ) )
			{
				$template = $basename;
			}
			else
			{
				$template = false;
			}
		}

		return apply_filters( 'kanban_template_find_template_return', $template );
	}



	/**
	 * render an html template, and populate variables
	 * @param  string $basename the filename of the template we're looking for
	 * @param  array  $data     the variables to populate
	 * @return string           the html output
	 */
	static function render_template( $basename, $data = array() )
	{
		$template_path = Kanban_Template::find_template( $basename );

		if ( ! $template_path ) return false;

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
	static function add_style()
	{
		if ( ! isset( self::get_instance()->style ) || ! is_array( self::get_instance()->style ) ) return;

		foreach ( self::get_instance()->style as $handle => $path )
		{
			echo sprintf(
				'<link rel="stylesheet" id="%s-css" href="%s?ver=%s">' . '\n',
				$handle,
				$path,
				Kanban::get_instance()->settings->plugin_data['Version']
			);
		}
	}



	/**
	 * add a js script to a kanban template, without using the WordPress queue
	 */
	static function add_script()
	{
		if ( ! isset( self::get_instance()->script ) || ! is_array( self::get_instance()->script ) ) return;

		foreach ( self::get_instance()->script as $handle => $path )
		{
			echo sprintf(
				'<script id="%s-js" src="%s?ver=%s"></script>' . '\n',
				$handle,
				$path,
				Kanban::get_instance()->settings->plugin_data['Version']
			);
		}
	}



	/**
	 * get the instance of this class
	 * @return object the instance
	 */
	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}



	/**
	 * construct that can't be overwritten
	 */
	private function __construct() { }

} // Kanban_Template
