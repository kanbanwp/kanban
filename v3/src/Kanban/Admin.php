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


	/**
	 * construct that can't be overwritten
	 */
	private function __construct() {
		// add custom pages to admin


//		if ( Kanban::instance()->settings()->is_network ) {
//			add_action( 'network_admin_menu', array( __CLASS__, 'network_admin_menu' ) );
//		}

	}

	/**
	 * render the welcome page
	 */
	static function render_welcome_page() {
		wp_enqueue_style(
			'kanban',
			sprintf( '%sadmin/css/admin.css', Kanban::instance()->settings()->uri )
		);

		include_once Kanban::instance()->settings()->path . 'admin/templates/welcome.php';

	}

	static function render_contact_page() {

		wp_enqueue_style(
			'kanban',
			sprintf( '%sadmin/css/admin.css', Kanban::instance()->settings()->uri )
		);

		include_once Kanban::instance()->settings()->path . 'admin/templates/contact.php';
	} // admin_menu

	static function render_pro_page() {

		wp_enqueue_style(
			'kanban',
			sprintf( '%sadmin/css/admin.css', Kanban::instance()->settings()->uri )
		);

		$status  = get_site_option( 'kanbanpro_license_status' );

		ob_start();
		include_once Kanban::instance()->settings()->path . 'admin/templates/kanbanpro.php';
		$html_output = ob_get_contents();
		ob_end_clean();

		echo apply_filters(
			'kanban_admin_render_kanbanpro_page_return',
			$html_output
		);

	} // admin_menu

	static function contact_post() {

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'kanban-admin-comment' ) ) {
			return false;
		}

		if ( empty( $_POST['request'] ) && empty( $_POST['message'] ) ) {
			return;
		}

		if ( empty( $_POST['request'] ) ) {
			$_POST['request'] = '';
		}

		if ( empty( $_POST['from'] ) ) {
			$_POST['from'] = get_option( 'admin_email' );
		}

		try {
			wp_mail(
				'support@kanbanwp.com',
				stripcslashes( sprintf( '[kbwp] %s', $_POST['request'] ) ),
				stripcslashes( sprintf(
					"%s\n\n%s\n%s v%s\n%s",
					stripcslashes( $_POST['message'] ),
					site_url(),
					Kanban_Router::instance()->get_page_uri(),
					Kanban::instance()->settings()->plugin_data['Version'],
					$_SERVER['HTTP_USER_AGENT']
				) ),
				sprintf( 'From: "%s" <%s>', get_option( 'blogname' ), $_POST['from'] )
			);

			$alert = __( "Email sent! We'll get back to you as soon as we can.", 'kanban' );
		} catch ( Exception $e ) {
			$alert = __( 'Email could not be sent. Please contact us through <a href="http://kanbanwp.com" target="_blank">https://kanbanwp.com</a>.', 'kanban' );
		}

		wp_redirect(
			add_query_arg( array(
					'message' => urlencode($alert)
				),
				sanitize_text_field( $_POST['_wp_http_referer'] )
			)
		);
	}

//
//
//	static function render_kanbanpro_page() {
//		$template = Kanban_Template::find_template( 'admin/kanbanpro' );
//
//		ob_start();
//		include_once $template;
//		$html_output = ob_get_contents();
//		ob_end_clean();
//
//		echo apply_filters(
//			'kanban_admin_render_kanbanpro_page_return',
//			$html_output
//		);
//	}

/**
	 * add pages to admin menu, including custom icon
	 *
	 * @return   [type] [description]
	 */
	public function admin_menu() {

		// add the base slug and page
		add_menu_page(
			Kanban::instance()->settings()->pretty_name,
			apply_filters('kanban_admin_admin_menu_page_title', Kanban::instance()->settings()->pretty_name),
			'read',
			Kanban::instance()->settings()->basename,
			null,
			self::get_icon_svg()
		);

		// redeclare same page to change name to settings
		// @link https://codex.wordpress.org/Function_Reference/add_submenu_page#Inside_menu_created_with_add_menu_page.28.29
		add_submenu_page(
			Kanban::instance()->settings()->basename,
			__( 'Guides' ),
			__( 'Guides' ),
			'read',
			Kanban::instance()->settings()->basename,
			array( __CLASS__, 'render_welcome_page' )
		);

		add_submenu_page(
			'kanban',
			'Kanban Pro',
			'Kanban Pro',
			'read',
			'kanban_pro',
			array( __CLASS__, 'render_pro_page' )
		);

		add_submenu_page(
			Kanban::instance()->settings()->basename,
			__( 'Contact Us' ),
			__( 'Contact Us' ),
			'read',
			'kanban_contact',
			array( __CLASS__, 'render_contact_page' )
		);

	} // admin_menu



	/**
	 * add pages to admin menu, including custom icon
	 *
	 * @return   [type] [description]
	 */
	public function network_admin_menu() {


		add_menu_page(
			Kanban::instance()->settings()->pretty_name . '_network',
			'Kanban',
			'manage_options',
			Kanban::instance()->settings()->pretty_name . '_network',
			array( __CLASS__, 'render_pro_page' ),
			self::get_icon_svg()
		);

		add_submenu_page(
			Kanban::instance()->settings()->pretty_name . '_network',
			__( 'Kanban Pro' ),
			__( 'Kanban Pro' ),
			'manage_options',
			Kanban::instance()->settings()->pretty_name . '_network',
			array( __CLASS__, 'render_pro_page' )
		);

		add_submenu_page(
			Kanban::instance()->settings()->pretty_name . '_network',
			__( 'Contact Us' ),
			__( 'Contact Us' ),
			'manage_options',
			'kanban_contact',
			array( __CLASS__, 'render_contact_page' )
		);
	}



	public function get_icon_svg() {
		$svg = file_get_contents(Kanban::instance()->settings()->path . 'img/kanbanwp-favicon.svg');

		return apply_filters(
			'kanban_admin_get_icon_svg_return',
			'data:image/svg+xml;base64,' . base64_encode( $svg )
		);
	}

	public function get_logo_svg($color = 'white') {

		if ( $color == 'white' ) {
			$svg = file_get_contents(Kanban::instance()->settings()->path . 'img/kanbanwp-sq-white.svg');
		} else {
			$svg = file_get_contents(Kanban::instance()->settings()->path . 'img/kanbanwp-sq-black.svg');
		}

		return apply_filters(
			'kanban_admin_get_logo_svg_return',
			'data:image/svg+xml;base64,' . base64_encode( $svg )
		);
	}



	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	public static function instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();

			add_action( 'admin_menu', array( self::$instance, 'admin_menu' ) );

			if ( Kanban::instance()->settings()->is_network ) {
				add_action( 'network_admin_menu', array( self::$instance, 'network_admin_menu' ) );
			}

			add_action( 'admin_init', array( self::$instance, 'contact_post' ) );
		}

		return self::$instance;
	}
}

