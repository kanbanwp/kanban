<?php

/**
 * for all interactions w the WordPress admin
 */



// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// instantiate the class
// Kanban_Admin::init();
class Kanban_Admin {
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

		if ( Kanban_Utils::is_network() ) {
			add_action( 'network_admin_menu', array( __CLASS__, 'network_admin_menu' ) );
		}

		// if migrating from older version, show upgrade notice with progress bar
		// add_action( 'admin_notices', array( __CLASS__, 'render_upgrade_notice' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_link_to_board' ), 999 );

		add_action( 'init', array( __CLASS__, 'contact_support' ) );

		add_action( 'admin_init', array( __CLASS__, 'add_preset' ) );

		add_action( 'wp_ajax_kanban_register_user', array( __CLASS__, 'ajax_register_user' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_deactivate_thickbox' ) );

		add_action( 'wp_ajax_kanban_diagnostic_info', array( __CLASS__, 'get_diagnostic_info' ) );

		add_action('admin_init', array(__CLASS__, 'download_export_file'), 10);
		add_action('admin_init', array(__CLASS__, 'upload_import_file'), 10);

	}



	static function add_deactivate_thickbox( $hook ) {
		if ( $hook != 'plugins.php' ) {
			return;
		}

		wp_register_script(
			'kanban-deactivate',
			sprintf( '%s/js/admin-deactivate.min.js', Kanban::get_instance()->settings->uri ),
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
			'url_contact'     => admin_url(),
			'url_plugins'     => admin_url( 'plugins.php' ),
		);
		wp_localize_script( 'kanban-deactivate', 'kanban', $translation_array );

		wp_enqueue_script( 'kanban-deactivate' );
	}



	/**
	 * render the welcome page
	 */
	static function welcome_page() {

		global $wpdb;

		// Get status table.
		$status_table = Kanban_Status::table_name();

		// See if there are any statuses yet.
		$sql = "SELECT count(`id`)
				FROM `{$status_table}`
		;";

		// Passed to template.
		$status_count = $wpdb->get_var( $sql );



		wp_enqueue_style(
			'kanban',
			sprintf( '%s/css/admin.css', Kanban::get_instance()->settings->uri )
		);


		wp_enqueue_style(
			'kanban-welcome',
			sprintf( '%s/css/admin-welcome.css', Kanban::get_instance()->settings->uri ),
			array( 'kanban' )
		);

		wp_enqueue_script(
			'kanban-admin',
			sprintf( '%s/js/min/admin-min.js', Kanban::get_instance()->settings->uri ),
			array( 'jquery' )
		);

		$template = Kanban_Template::find_template( 'admin/welcome' );

		include_once $template;
	}



	/**
	 * render the Add-ons page
	 */
//	static function addons_page() {
//
//		wp_enqueue_script(
//			'addon',
//			sprintf( '%s/js/min/admin-addons-min.js', Kanban::get_instance()->settings->uri ),
//			array( 'jquery', 'masonry' )
//		);
//
//
//
//		// Get stored add-ons.
//		if ( Kanban_Utils::is_network() ) {
//			$addons = get_site_transient( 'kanban-admin-addons' );
//		} else {
//			$addons = get_transient( 'kanban-admin-addons' );
//		}
//
//
//
//		// $addons is false if not set
//		if ( empty( $addons ) || ! $addons ) {
//
//			$response = wp_remote_get( 'https://kanbanwp.com?feed=addons' );
//
//			try {
//				$addons = @json_decode( $response[ 'body' ] );
//			} catch ( Exception $e ) {
//				$addons = array();
//			}
//
//			if ( Kanban_Utils::is_network() ) {
//				set_site_transient( 'kanban-admin-addons', $addons, 24 * HOUR_IN_SECONDS );
//			} else {
//				set_transient( 'kanban-admin-addons', $addons, 24 * HOUR_IN_SECONDS );
//			}
//
//		}
//
//
//
//		// get the template data
//		global $wp_query;
//
//		// attach our object to the template data
//		$wp_query->query_vars[ 'addons' ] = $addons;
//
//		$template = Kanban_Template::find_template( 'admin/addons' );
//
//
//
//		include_once $template;
//	}


	static function v3_page() {
		$template = Kanban_Template::find_template( 'admin/v3' );

		include_once $template;
	}


	static function contact_page() {
		$template = Kanban_Template::find_template( 'admin/contact' );

		include_once $template;
	}


	static function import_page() {

		if ( !isset($_GET[Kanban_Utils::get_nonce()]) || !wp_verify_nonce($_GET[Kanban_Utils::get_nonce()], 'import-process') || !isset($_GET['filename']) || !isset($_GET['current']) || !isset($_GET['max']) ) {
			wp_redirect(add_query_arg(
				array(
					'page' => 'kanban_settings',
					'message' => urlencode(__('Something went wrong with your import. Please try again.', 'kanban'))
				),
				admin_url('admin.php')
			));
			exit;
		}

		$done = false;
		$backup = false;
		$percentage = round($_GET['current'] == 0 ? 1 : ($_GET['current']/$_GET['max'])*100);
		$template = Kanban_Template::find_template( 'admin/import' );

		if ( isset($_GET['backup']) ) {
			$backup = true;

			$redirect = remove_query_arg('backup');

			include_once $template;
			exit;
		} else if ( $_GET['current'] == $_GET['max'] ) {
			$done = true;

			$redirect = add_query_arg(array(
				'page' => 'kanban_settings'
			), admin_url('admin.php'));
		} else {

			$redirect = add_query_arg(array(
				'current' => $_GET['current']+1
			));
		}

		$file = self::exports_upload_dir() . '/' . $_GET['filename'] . '-' . $_GET['current'];

		if ( !is_file($file) ) {
			wp_redirect(add_query_arg(
				array(
					'page' => 'kanban_settings',
					'message' => urlencode(__('Something went wrong with your import. Please try again.', 'kanban'))
				),
				admin_url('admin.php')
			));
			exit;
		}

		include $file;

		if ( $_GET['current'] == $_GET['max'] ) {
			// Cleanup chunk files AFTER running the last one.
			foreach (glob(self::exports_upload_dir() . '/' . $_GET['filename'] . "-*") as $chfile) {
				unlink($chfile);
			}
		}

		include_once $template;
	}


	static function render_kanbanpro_page() {
		$template = Kanban_Template::find_template( 'admin/kanbanpro' );

		ob_start();
		include_once $template;
		$html_output = ob_get_contents();
		ob_end_clean();

		echo apply_filters(
			'kanban_admin_render_kanbanpro_page_return',
			$html_output
		);
	}



	static function add_preset() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-add-preset' ) || ! isset( $_POST[ 'kanban_preset' ] ) ) {
			return false;
		}

		// See if preset file exists.
		$file = sprintf(
			'%s/includes/inc-preset-%s.php',
			Kanban::get_instance()->settings->path,
			$_POST[ 'kanban_preset' ]
		);

		if ( ! is_file( $file ) ) {
			return;
		}



		// Include the preset.
		include $file;



		// Get board
		$board_id = Kanban_Board::get_current_id();



		global $wpdb;



		// Get status table.
		$status_table = Kanban_Status::table_name();

		// Load existing.
		$sql = "SELECT count(`id`)
				FROM `{$status_table}`
				WHERE `board_id` = $board_id
		;";

		$status_count = $wpdb->get_var( $sql );

		// If there are none in the db.
		if ( $status_count == 0 && isset( $statuses ) ) {

			$i = 0;

			// Insert each from preset.
			foreach ( $statuses as $status => $color ) {
				$data = array(
					'title'     => $status,
					'color_hex' => $color,
					'position'  => $i,
					'board_id'  => $board_id,
				);

				Kanban_Status::replace( $data );

				$i ++;
			}
		}

		// Get first status for task.
		$sql = "SELECT `id`
				FROM `{$status_table}`
				WHERE `board_id` = $board_id
				ORDER BY id
				LIMIT 1
		;";

		$status_id = $wpdb->get_var( $sql );



		// Get estimates table.
		$estimate_table = Kanban_Estimate::table_name();

		// Load existing.
		$sql = "SELECT count(`id`)
				FROM `{$estimate_table}`
				WHERE `board_id` = $board_id
		;";

		$estimate_count = $wpdb->get_var( $sql );

		// If there are none in the db.
		if ( $estimate_count == 0 && isset( $estimates ) ) {

			$i = 0;

			// Insert each from preset.
			foreach ( $estimates as $hours => $title ) {
				$data = array(
					'title'    => $title,
					'hours'    => $hours,
					'position' => $i,
					'board_id' => $board_id,
				);

				Kanban_Estimate::replace( $data );

				$i ++;
			}
		}



		// Get tasks table.
		$tasks_table = Kanban_Task::table_name();

		// Load existing.
		$sql = "SELECT count(`id`)
				FROM `{$tasks_table}`
				WHERE `board_id` = $board_id
		;";

		$tasks_count = $wpdb->get_var( $sql );

		// If there are none in the db.
		if ( $tasks_count == 0 && isset( $tasks ) ) {

			// Insert each from preset.
			foreach ( $tasks as $position => $data ) {

				$data = array_merge(
					array(
						'title'           => '',
						'position'        => $position,
						'board_id'        => $board_id,
						'status_id'       => $status_id,
						'created_dt_gmt'  => Kanban_Utils::mysql_now_gmt(),
						'modified_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
						'user_id_author'  => get_current_user_id(),
						'is_active'       => 1
					),
					$data
				);

				Kanban_Task::replace( $data );
			}
		}



		// Update settings (if $settings is definied in the preset).
		if ( isset( $settings ) ) {

			// Insert each from preset.
			foreach ( $settings as $key => $value ) {

				Kanban_Option::update_option( $key, $value, $board_id );
			}
		}



		wp_safe_redirect( add_query_arg(
			array(
				'page'    => Kanban::get_instance()->settings->basename,
				'message' => urlencode(
					sprintf(
						__( 'Your board is all set up! <a href=%s>Go to your board</a> to check it out.' ),
						Kanban_Template::get_uri()
					)
				)
			),
			admin_url( 'admin.php' )
		) );

		exit;
	}



	static function contact_support() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-admin-comment' ) || ! is_user_logged_in() ) {
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

	public static function exports_upload_dir()
	{
		$upload_dir   = wp_upload_dir();
		$user_dirname = $upload_dir['basedir'] . '/kanban-exports';
		if (!file_exists($user_dirname)) {
			wp_mkdir_p($user_dirname);
		}

		return $user_dirname;
	}

	public static function upload_import_file()
	{
		if ( !isset($_GET['page']) || !isset($_GET[Kanban_Utils::get_nonce()]) || $_GET['page'] != 'kanban_settings' || !wp_verify_nonce($_GET[Kanban_Utils::get_nonce()], 'import') ) return;

		if ( !isset($_FILES) || !isset($_FILES['kanban_import']) || empty($_FILES['kanban_import']['name']) ) return;

		$data = json_decode(file_get_contents($_FILES['kanban_import']['tmp_name']));

		$chunk_size = isset($_GET['size']) && is_numeric($_GET['size']) ? $_GET['size'] : 20;

		$chunks = array_chunk($data, $chunk_size);

		$filename = $_FILES['kanban_import']['name'];

		foreach ($chunks as $i => $chunk) {
			file_put_contents(
				self::exports_upload_dir() . '/' . $filename . '-' . ($i+1),
				'<?php ' . "\n"
					. 'global $wpdb;' . "\n"
					. implode($chunk, "\n")
			);
		}

		wp_redirect(add_query_arg(
			array(
				'page' => 'kanban_import',
				Kanban_Utils::get_nonce() => wp_create_nonce('import-process'),
				'current' => 1,
				'max' => count($chunks),
				'filename' => urlencode($filename),
				'backup' => 1
			)
		));
		exit;

	}


	public static function download_export_file()
	{
		if ( !isset($_GET['page']) || !isset($_GET[Kanban_Utils::get_nonce()]) || $_GET['page'] != 'kanban_settings' || !wp_verify_nonce($_GET[Kanban_Utils::get_nonce()], 'export') ) return;

		global $wpdb;

		$tables = $wpdb->get_col(
			$wpdb->prepare (
				'show tables like %s;',
				$wpdb->prefix . 'kanban%'
			)
		);

		$return = array();

		//cycle through
		foreach ($tables as $table) {
			$return[] = sprintf('$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}%s;");',
				str_replace($wpdb->prefix, '', $table)
			);
;

			$result = $wpdb->get_results('SELECT * FROM ' . $table, ARRAY_A);

			foreach ($result as $row) {

				$return[] = sprintf(
					'$wpdb->insert(%s, %s);',
					'$wpdb->prefix . "' . str_replace($wpdb->prefix, '', $table) . '"',
					var_export($row, true)
				);
			}
		}

		$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : 'export';

		$filename = sprintf(
				'kb-%s-%s.kanbanwp',
				$prefix,
				Date('Y-m-d_H-i-s')
		);

		file_put_contents(
			self::exports_upload_dir() . '/' . $filename,
			json_encode($return)
		);

		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		exit(json_encode($return));
	}

	static function ajax_register_user() {
		if ( ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-new-user' ) ) {
			return;
		}

		$user_login = $_POST[ 'new-user-login' ];
		$user_email = $_POST[ 'new-user-email' ];
		$user_first = $_POST[ 'new-user-first' ];
		$user_last  = $_POST[ 'new-user-last' ];
		$board_id   = $_POST[ 'board_id' ];

		$errors = array();

		if ( username_exists( $user_login ) ) {
			$errors[] = __( 'Username already taken' );
		}

		if ( ! validate_username( $user_login ) ) {
			$errors[] = __( 'Invalid username' );
		}

		if ( $user_login == '' ) {
			$errors[] = __( 'Please enter a username' );
		}

		if ( ! is_email( $user_email ) ) {

			$errors[] = __( 'Invalid email' );
		}

		if ( email_exists( $user_email ) ) {
			$errors[] = __( 'Email already registered' );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error( array( 'error' => implode( '<br>', $errors ) ) );

			return;
		}

		$boards = Kanban_Board::get_all();

		if ( ! in_array( $board_id, array_keys( $boards ) ) ) {
			$board_id = Kanban_Board::get_current();
		}

		$userdata = array(
			'user_login' => $user_login,
			'user_email' => $user_email,
			'first_name' => $user_first,
			'last_name'  => $user_last,
			'user_pass'  => null,// When creating an user, `user_pass` is expected.
		);

		$user_id = wp_insert_user( $userdata );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'error' => __( 'User could not be created. Please use the User > Add New page', 'kanban' ) ) );

			return;
		}

		// add new user to allowed users
		$allowed_users   = Kanban_Option::get_option( 'allowed_users' );
		$allowed_users[] = $user_id;

		Kanban_Option::update_option( 'allowed_users', $allowed_users, $board_id );

		// send an email to the admin alerting them of the registration
		wp_new_user_notification( $user_id, null, 'both' );

		wp_send_json_success( array( 'new_user_id' => $user_id ) );
	}


	static function icon_svg() {
		$svg = '<svg id="Layer_2" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 63.84"><defs><style>.cls-1{fill:#82878c;}</style></defs><title>kanban</title><ellipse class="cls-1" cx="50.52" cy="13.51" rx="13.48" ry="13.51"/><ellipse class="cls-1" cx="13.48" cy="13.51" rx="13.48" ry="13.51"/><ellipse class="cls-1" cx="50.52" cy="50.33" rx="13.48" ry="13.51"/><ellipse class="cls-1" cx="13.48" cy="50.33" rx="13.48" ry="13.51"/></svg>';

		return apply_filters(
			'kanban_admin_icon_svg_return',
			'data:image/svg+xml;base64,' . base64_encode( $svg )
		);
	}


	/**
	 * add pages to admin menu, including custom icon
	 *
	 * @return   [type] [description]
	 */
	static function admin_menu() {

		// add the base slug and page
		add_menu_page(
			Kanban::get_instance()->settings->pretty_name,
			Kanban::get_instance()->settings->pretty_name,
			'manage_options',
			Kanban::get_instance()->settings->basename,
			null,
			self::icon_svg()
		);

		// redeclare same page to change name to settings
		// @link https://codex.wordpress.org/Function_Reference/add_submenu_page#Inside_menu_created_with_add_menu_page.28.29
		add_submenu_page(
			Kanban::get_instance()->settings->basename,
			__( 'Welcome' ),
			__( 'Welcome' ),
			'manage_options',
			Kanban::get_instance()->settings->basename,
			array( __CLASS__, 'welcome_page' )
		);

		// add the settings admin page
		add_submenu_page(
			Kanban::get_instance()->settings->basename,
			__( 'Settings' ),
			__( 'Settings' ),
			'manage_options',
			'kanban_settings',
			array( 'Kanban_Option', 'settings_page' )
		);

		add_submenu_page(
			'kanban',
			'Kanban Pro',
			'Kanban Pro',
			'manage_options',
			'kanban_pro',
			array( __CLASS__, 'render_kanbanpro_page' )
		);

		add_submenu_page(
			Kanban::get_instance()->settings->basename,
			__( 'Contact Us' ),
			__( 'Contact Us' ),
			'manage_options',
			'kanban_contact',
			array( __CLASS__, 'contact_page' )
		);

		add_submenu_page(
			null,
			__( 'Version 3' ),
			__( 'Version 3' ),
			'manage_options',
			'kanban_v3',
			array( __CLASS__, 'v3_page' )
		);

		add_submenu_page(
			null,
			__( 'Import' ),
			__( 'Import' ),
			'manage_options',
			'kanban_import',
			array( __CLASS__, 'import_page' )
		);

	} // admin_menu



	/**
	 * add pages to admin menu, including custom icon
	 *
	 * @return   [type] [description]
	 */
	static function network_admin_menu() {


		add_menu_page(
			'kanban_network',
			'Kanban',
			'manage_options',
			'kanban_network',
			array( __CLASS__, 'render_kanbanpro_page' ),
			self::icon_svg()
		);

		add_submenu_page(
			'kanban_network',
			__( 'Kanban Pro' ),
			__( 'Kanban Pro' ),
			'manage_options',
			'kanban_network',
			array( __CLASS__, 'render_kanbanpro_page' )
		);

		add_submenu_page(
			'kanban_network',
			__( 'Contact Us' ),
			__( 'Contact Us' ),
			'manage_options',
			'kanban_contact',
			array( __CLASS__, 'contact_page' )
		);
	}



	static function add_admin_bar_link_to_board( $wp_admin_bar ) {

		if ( is_network_admin() ) {
			return;
		}

		$args = array(
			'id'    => 'kanban_board',
			'title' => 'Kanban Board',
			'href'  => Kanban_Template::get_uri(),
			'meta'  => array( 'class' => 'kanban-board' ),
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
		if ( ! get_transient( sprintf( '_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename ) ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( sprintf( '_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename ) );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET[ 'activate-multi' ] ) ) {
			return;
		}

		// Redirect to about page
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => Kanban::get_instance()->settings->basename,
					'activation'   => '1',
					'kanban-modal' => 'presets'
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
		echo esc_html( ! empty( $_SERVER[ 'SERVER_SOFTWARE' ] ) ? $_SERVER[ 'SERVER_SOFTWARE' ] : '' );
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

		$active_plugins = (array) get_option( 'active_plugins', array() );

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
