<?php

/**
 * for all interactions w the WordPress admin
 */



// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}



// instantiate the class
// Kanban_Admin::init();
class Kanban_Admin
{
	// the instance of this object
	private static $instance;



	static function init() {
		// redirect to welcome screen on activation
		add_action( 'admin_init', array( __CLASS__, 'screen_do_activation_redirect' ) );

		// add settings link
		add_filter(
			'plugin_action_links_' . Kanban::get_instance()->settings->plugin_basename,
			array( __CLASS__, 'add_plugin_settings_link' )
		);

		// Remove Admin bar
		if ( strpos( $_SERVER[ 'REQUEST_URI' ], sprintf( '/%s/', Kanban::$slug ) ) !== false ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}

		// add custom pages to admin
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

		// if migrating from older version, show upgrade notice with progress bar
		// add_action( 'admin_notices', array( __CLASS__, 'render_upgrade_notice' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_link_to_board' ), 999 );

		add_action( 'init', array( __CLASS__, 'contact_support' ) );

		add_action( 'wp_ajax_kanban_register_user', array( __CLASS__, 'ajax_register_user' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_deactivate_thickbox' ) );

		add_action( 'wp_ajax_kanban_diagnostic_info', array( __CLASS__, 'get_diagnostic_info' ) );
	}



	static function add_deactivate_thickbox( $hook ) {
		if ( $hook != 'plugins.php' ) {
			return;
		}

		wp_register_script(
			'kanban-deactivate',
			sprintf( '%s/js/min/admin-deactivate-min.js', Kanban::get_instance()->settings->uri ),
			array( 'jquery' )
		);

		ob_start();
		?>
		<div id="kanban-deactivate-modal" style="display: none;">
			<form id="kanban-deactivate-form" style="background: white; padding: 5px;">
				<p style="font-size: 1.618em; margin-bottom: 0;">
					<?php echo __( 'OPTIONAL: Please Let us know why you are deactivating Kanban.', 'kanban' ) ?>
				</p>
				<p style="padding: 0;">
					<label><input type="radio" name="request"
								  value="deactivated: decided to use something else"><?php echo __( 'I decided to use something else' ); ?>
					</label><br>
					<textarea rows="2" class="large-text"
							  placeholder="<?php echo __( 'What else did you decide to use?' ); ?>"
							  style="display: none;"></textarea>
				</p>
				<p style="padding: 0;">
					<label><input type="radio" name="request"
								  value="deactivated: not what I was looking for"><?php echo __( 'The plugin is not what I was looking for' ); ?>
					</label><br>
					<textarea rows="2" class="large-text"
							  placeholder="<?php echo __( 'What were you looking for?' ); ?>"
							  style="display: none;"></textarea>
				<p style="padding: 0;">
					<label><input type="radio" name="request"
								  value="deactivated: didn't have the features I wanted"><?php echo __( 'The plugin didn\'t have the features I wanted' ); ?>
					</label><br>
					<textarea rows="2" class="large-text"
							  placeholder="<?php echo __( 'What features did you want?' ); ?>"
							  style="display: none;"></textarea>
				<p style="padding: 0;">
					<label><input type="radio" name="request"
								  value="deactivated: didn't work as expected"><?php echo __( 'The plugin didn\'t work as expected' ); ?>
					</label><br>
					<textarea rows="2" class="large-text" placeholder="<?php echo __( 'What were you expecting?' ); ?>"
							  style="display: none;"></textarea>
				<p style="padding: 0;">
					<label><input type="radio" name="request"
								  value="deactivated: is not working"><?php echo __( 'The plugin is not working' ); ?>
					</label><br>
					<textarea rows="2" class="large-text" placeholder="<?php echo __( 'What didn\'t work?' ); ?>"
							  style="display: none;"></textarea>
				</p>
				<p align="right">
					<button type="button" class="button button-primary kanban-deactivate-submit">
						<?php echo __( 'Skip &amp; Deactivate', 'kanban' ) ?>
					</button>
					<button type="button" class="button kanban-deactivate-remove">
						<?php echo __( 'Cancel', 'kanban' ) ?>
					</button>
				</p>
				<?php wp_nonce_field( 'kanban-admin-comment', Kanban_Utils::get_nonce() ); ?>

			</form>
		</div>


		<?php
		$html_output = ob_get_contents();
		ob_end_clean();

		// Localize the script with new data
		$translation_array = array(
			'form_deactivate' => $html_output,
			'url_contact' => admin_url(),
			'url_plugins' => admin_url( 'plugins.php' ),
		);
		wp_localize_script( 'kanban-deactivate', 'kanban', $translation_array );

		wp_enqueue_script( 'kanban-deactivate' );
	}



	/**
	 * render the welcome page
	 */
	static function welcome_page() {
		$template = Kanban_Template::find_template( 'admin/welcome' );

		include_once $template;
	}



	/**
	 * render the welcome page
	 */
	static function addons_page() {

		wp_enqueue_script(
			'addon',
			get_stylesheet_directory_uri() . '/js/addon.js',
			array( 'jquery', 'masonry' )
		);

		global $wpdb;

		$current_user_id = get_current_user_id();
		$lastRun = (int)Kanban_Option::get_option( 'admin-addons-check' );

		if ( time() - $lastRun >= 60 * 60 * 24 ) {
			Kanban_Option::update_option( 'admin-addons-check', time() );

			$response = wp_remote_get( 'https://kanbanwp.com?feed=addons' );

			try {
				$addons = @json_decode( $response[ 'body' ] );
			} catch ( Exception $e ) {
				$addons = array();
			}

			Kanban_Option::update_option( 'admin-addons', $addons );
		} else {
			$addons = Kanban_Option::get_option( 'admin-addons' );
		}

		// get the template data
		global $wp_query;

		// attach our object to the template data
		$wp_query->query_vars[ 'addons' ] = $addons;

		$template = Kanban_Template::find_template( 'admin/addons' );

		include_once $template;
	}



	static function contact_page() {
		$template = Kanban_Template::find_template( 'admin/contact' );

		include_once $template;
	}



	static function licenses_page() {
		$template = Kanban_Template::find_template( 'admin/licenses' );

		include_once $template;
	}



	static function contact_support() {
		if ( !isset( $_POST[ Kanban_Utils::get_nonce() ] ) || !wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-admin-comment' ) || !is_user_logged_in() ) {
			return false;
		}

		if ( empty( $_POST[ 'request' ] ) && empty( $_POST[ 'message' ] ) ) {
			return;
		}

		if ( empty( $_POST[ 'request' ] ) ) {
			$_POST[ 'request' ] = '';
		}

		if ( empty( $_POST[ 'from' ] ) ) {
			$_POST[ 'from' ] = get_option( 'admin_email' );
		}

		try {
			wp_mail(
				'support@kanbanwp.com',
				stripcslashes( sprintf( '[kbwp] %s', $_POST[ 'request' ] ) ),
				stripcslashes( sprintf(
					"%s\n\n%s\n%s\n%s",
					stripcslashes( $_POST[ 'message' ] ),
					site_url(),
					Kanban_Template::get_uri(),
					$_SERVER[ 'HTTP_USER_AGENT' ]
				) ),
				sprintf( 'From: "%s" <%s>', get_option( 'blogname' ), $_POST[ 'from' ] )
			);

			$_GET[ 'alert' ] = __( "Email sent! We'll get back to you as soon as we can.", 'kanban' );
		} catch ( Exception $e ) {
			$_GET[ 'alert' ] = __( 'Email could not be sent. Please contact us through <a href="http://kanbanwp.com" target="_blank">https://kanbanwp.com</a>.', 'kanban' );
		}
	}



	static function ajax_register_user() {
		if ( !wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-new-user' ) ) {
			return;
		}

		$user_login = $_POST[ 'new-user-login' ];
		$user_email = $_POST[ 'new-user-email' ];
		$user_first = $_POST[ 'new-user-first' ];
		$user_last = $_POST[ 'new-user-last' ];
		$board_id = $_POST[ 'board_id' ];

		$errors = array();

		if ( username_exists( $user_login ) ) {
			$errors[] = __( 'Username already taken' );
		}

		if ( !validate_username( $user_login ) ) {
			$errors[] = __( 'Invalid username' );
		}

		if ( $user_login == '' ) {
			$errors[] = __( 'Please enter a username' );
		}

		if ( !is_email( $user_email ) ) {

			$errors[] = __( 'Invalid email' );
		}

		if ( email_exists( $user_email ) ) {
			$errors[] = __( 'Email already registered' );
		}

		if ( !empty( $errors ) ) {
			wp_send_json_error( array( 'error' => implode( '<br>', $errors ) ) );
			return;
		}

		$boards = Kanban_Board::get_all();

		if ( !in_array( $board_id, array_keys( $boards ) ) ) {
			$board_id = Kanban_Board::get_current();
		}

		$userdata = array(
			'user_login' => $user_login,
			'user_email' => $user_email,
			'first_name' => $user_first,
			'last_name' => $user_last,
			'user_pass' => null,// When creating an user, `user_pass` is expected.
		);

		$user_id = wp_insert_user( $userdata );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'error' => __( 'User could not be created. Please use the User > Add New page', 'kanban' ) ) );
			return;
		}

		// add new user to allowed users
		$allowed_users = Kanban_Option::get_option( 'allowed_users' );
		$allowed_users[] = $user_id;

		Kanban_Option::update_option( 'allowed_users', $allowed_users, $board_id );

		// send an email to the admin alerting them of the registration
		wp_new_user_notification( $user_id, null, 'both' );

		wp_send_json_success( array( 'new_user_id' => $user_id ) );
	}



	/**
	 * add pages to admin menu, including custom icon
	 *
	 * @return   [type] [description]
	 */
	static function admin_menu() {
		$svg = '<svg id="Layer_2" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 63.84"><defs><style>.cls-1{fill:#82878c;}</style></defs><title>kanban</title><ellipse class="cls-1" cx="50.52" cy="13.51" rx="13.48" ry="13.51"/><ellipse class="cls-1" cx="13.48" cy="13.51" rx="13.48" ry="13.51"/><ellipse class="cls-1" cx="50.52" cy="50.33" rx="13.48" ry="13.51"/><ellipse class="cls-1" cx="13.48" cy="50.33" rx="13.48" ry="13.51"/></svg>';
		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode( $svg );


		// add the base slug and page
		add_menu_page(
			Kanban::get_instance()->settings->pretty_name,
			Kanban::get_instance()->settings->pretty_name,
			'manage_options',
			Kanban::get_instance()->settings->basename,
			null,
			$icon_svg
		);

		// redeclare same page to change name to settings
		// @link https://codex.wordpress.org/Function_Reference/add_submenu_page#Inside_menu_created_with_add_menu_page.28.29
		add_submenu_page(
			Kanban::get_instance()->settings->basename,
			'Welcome',
			'Welcome',
			'manage_options',
			Kanban::get_instance()->settings->basename,
			array( __CLASS__, 'welcome_page' )
		);

		// add the settings admin page
		add_submenu_page(
			Kanban::get_instance()->settings->basename,
			'Settings',
			'Settings',
			'manage_options',
			'kanban_settings',
			array( 'Kanban_Option', 'settings_page' )
		);

		add_submenu_page(
			Kanban::get_instance()->settings->basename,
			'Add-ons',
			'Add-ons',
			'manage_options',
			'kanban_addons',
			array( __CLASS__, 'addons_page' )
		);

		add_submenu_page(
			Kanban::get_instance()->settings->basename,
			'Contact Us',
			'Contact Us',
			'manage_options',
			'kanban_contact',
			array( __CLASS__, 'contact_page' )
		);

	} // admin_menu



	static function add_admin_bar_link_to_board( $wp_admin_bar ) {
		$args = array(
			'id' => 'kanban_board',
			'title' => 'Kanban Board',
			'href' => Kanban_Template::get_uri(),
			'meta' => array( 'class' => 'kanban-board' ),
		);
		$wp_admin_bar->add_node( $args );
	}



	// add the settings page link on the plugins page
	static function add_plugin_settings_link( $links ) {
		$url = admin_url(
			sprintf(
				'admin.php?page=%s',
				sprintf(
					'%s_settings',
					Kanban::get_instance()->settings->basename
				)
			)
		);

		$mylinks = array(
			sprintf(
				'<a href="%s">%s</a>',
				$url,
				__( 'Settings', 'kanban' )
			)
		);

		return array_merge( $links, $mylinks );
	}



	// redirect to welcome page
	// @link http://premium.wpmudev.org/blog/tabbed-interface/
	static function screen_do_activation_redirect() {
		// Bail if no activation redirect
		if ( !get_transient( sprintf( '_%s_screen_activation_redirect', Kanban::get_instance()->settings->basename ) ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( sprintf( '_%s_screen_activation_redirect', Kanban::get_instance()->settings->basename ) );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET[ 'activate-multi' ] ) ) {
			return;
		}

		// Redirect to about page
		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => Kanban::get_instance()->settings->basename,
					'activation' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
	}



	static function get_diagnostic_info() {
		global $wpdb;
		$table_prefix = $wpdb->base_prefix;

		echo 'site_url: ';
		echo esc_html( site_url() );
		echo "\r\n";

		echo 'home_url: ';
		echo esc_html( home_url() );
		echo "\r\n";

		// echo 'Database Name: ';
		// echo esc_html( $wpdb->dbname );
		// echo "\r\n";
		//
		// echo 'Table Prefix: ';
		// echo esc_html( $table_prefix );
		// echo "\r\n";
		echo 'WordPress Version: ';
		echo bloginfo( 'version' );
		if ( is_multisite() ) {
			echo ' Multisite';
		}
		echo "\r\n";

		echo 'permalink_structure: ';
		echo '"' . get_option( 'permalink_structure' ) . '"' . "\r\n";

		echo 'board: ';
		echo Kanban_Template::get_uri() . "\r\n";

		echo 'Kanban Version: ';
		echo Kanban::get_instance()->settings->plugin_data[ 'Version' ] . "\r\n";

		echo 'Web Server: ';
		echo esc_html( !empty( $_SERVER[ 'SERVER_SOFTWARE' ] ) ? $_SERVER[ 'SERVER_SOFTWARE' ] : '' );
		echo "\r\n";

		echo 'PHP: ';
		if ( function_exists( 'phpversion' ) ) {
			echo esc_html( phpversion() );
		}
		echo "\r\n";

		echo 'MySQL: ';
		echo esc_html( empty( $wpdb->use_mysqli ) ? mysql_get_server_info() : mysqli_get_server_info( $wpdb->dbh ) );
		echo "\r\n";

		echo 'ext/mysqli: ';
		echo empty( $wpdb->use_mysqli ) ? 'no' : 'yes';
		echo "\r\n";

		 echo 'WP Memory Limit: ';
		 echo esc_html( WP_MEMORY_LIMIT );
		 echo "\r\n";
		//
		// echo 'Blocked External HTTP Requests: ';
		// if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || ! WP_HTTP_BLOCK_EXTERNAL ) {
		// echo 'None';
		// } else {
		// $accessible_hosts = ( defined( 'WP_ACCESSIBLE_HOSTS' ) ) ? WP_ACCESSIBLE_HOSTS : '';
		//
		// if ( empty( $accessible_hosts ) ) {
		// echo 'ALL';
		// } else {
		// echo 'Partially (Accessible Hosts: ' . esc_html( $accessible_hosts ) . ')';
		// }
		// }
		// echo "\r\n";
		echo 'WP Locale: ';
		echo esc_html( get_locale() );
		echo "\r\n";

		echo 'DB Charset: ';
		echo esc_html( DB_CHARSET );
		echo "\r\n";

		// if ( function_exists( 'ini_get' ) && $suhosin_limit = ini_get( 'suhosin.post.max_value_length' ) ) {
		// echo 'Suhosin Post Max Value Length: ';
		// echo esc_html( is_numeric( $suhosin_limit ) ? size_format( $suhosin_limit ) : $suhosin_limit );
		// echo "\r\n";
		// }
		//
		// if ( function_exists( 'ini_get' ) && $suhosin_limit = ini_get( 'suhosin.request.max_value_length' ) ) {
		// echo 'Suhosin Request Max Value Length: ';
		// echo esc_html( is_numeric( $suhosin_limit ) ? size_format( $suhosin_limit ) : $suhosin_limit );
		// echo "\r\n";
		// }
		echo 'Debug Mode: ';
		echo esc_html( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No' );
		echo "\r\n";

		 echo 'WP Max Upload Size: ';
		 echo esc_html( size_format( wp_max_upload_size() ) );
		 echo "\r\n";


		 echo 'PHP Time Limit: ';
		 if ( function_exists( 'ini_get' ) ) {
		 echo esc_html( ini_get( 'max_execution_time' ) );
		 }
		 echo "\r\n";

		echo 'PHP Error Log: ';
		if ( function_exists( 'ini_get' ) ) {
			echo esc_html( ini_get( 'error_log' ) );
		}
		echo "\r\n";

		echo 'Upload dir: ';
		if ( function_exists( 'wp_upload_dir' ) ) {
			$wp_upload_dir = wp_upload_dir();
			echo $wp_upload_dir[ 'basedir' ];
			echo "\r\n";

			echo 'Upload dir is writable: ';
			if ( isset( $wp_upload_dir[ 'basedir' ] ) && function_exists( 'is_writable' ) ) {
				echo is_writable( $wp_upload_dir[ 'basedir' ] ) ? 'true' : 'false';
			}
		}
		echo "\r\n";

		// echo 'fsockopen: ';
		// if ( function_exists( 'fsockopen' ) ) {
		// echo 'Enabled';
		// } else {
		// echo 'Disabled';
		// }
		// echo "\r\n";
		echo 'cURL: ';
		if ( function_exists( 'curl_init' ) ) {
			echo 'Enabled';
		} else {
			echo 'Disabled';
		}
		echo "\r\n";

		echo 'Compatibility Mode: ';
		if ( isset( $GLOBALS[ 'wpmdb_compatibility' ] ) ) {
			echo 'Yes';
		} else {
			echo 'No';
		}
		echo "\r\n";

		// do_action( 'wpmdb_diagnostic_info' );
		// if ( has_action( 'wpmdb_diagnostic_info' ) ) {
		// echo "\r\n";
		// }
		// $theme_info = wp_get_theme();
		// echo "Active Theme Name: " . esc_html( $theme_info->Name ) . "\r\n";
		// echo "Active Theme Folder: " . esc_html( basename( $theme_info->get_stylesheet_directory() ) ) . "\r\n";
		// if ( $theme_info->get( 'Template' ) ) {
		// echo "Parent Theme Folder: " . esc_html( $theme_info->get( 'Template' ) ) . "\r\n";
		// }
		// if ( ! file_exists( $theme_info->get_stylesheet_directory() ) ) {
		// echo "WARNING: Active Theme Folder Not Found\r\n";
		// }
		//
		// echo "\r\n";
		echo "Active Plugins:\r\n";

		$active_plugins = (array)get_option( 'active_plugins', array() );

		// if ( is_multisite() ) {
		// $network_active_plugins = wp_get_active_network_plugins();
		// $active_plugins = array_map( array( $this, 'remove_wp_plugin_dir' ), $network_active_plugins );
		// }
		foreach ( $active_plugins as $plugin ) {
			echo '- ' . $plugin . "\r\n";
		}

		$mu_plugins = wp_get_mu_plugins();
		if ( $mu_plugins ) {
			echo "\r\n";

			echo "Must-use Plugins:\r\n";

			foreach ( $mu_plugins as $mu_plugin ) {
				echo '- ' . $mu_plugin . "\r\n";
			}
		}

		exit;
	}




	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	// public static function get_instance()
	// {
	// if ( ! self::$instance )
	// {
	// self::$instance = new self();
	// }
	// return self::$instance;
	// }
	/**
	 * construct that can't be overwritten
	 */
	private function __construct() {
	}
} // Kanban_Admin
