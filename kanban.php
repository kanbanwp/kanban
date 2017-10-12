<?php
/*
Plugin Name:        Kanban Boards for WordPress
Plugin URI:         https://kanbanwp.com/
Description:        Add Kanban boards to your WordPress sites and get organized! Project management, CRM, sales tracking...
Version:            2.4.8
Stable tag:         2.4.8
Release Date:       October 12, 2017
Requires at least:  4.0
Tested up to:	    4.8.1
Requires PHP:       5.3
Author:             Gelform Inc
Author URI:         http://gelform.com
License:            GPL2
Text Domain:        kanban
Domain Path:        /languages/
*/

// Kanban is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 2 of the License, or
// any later version.
//
// Kanban is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kanban. If not, see <http://www.gnu.org/licenses/>.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load Kanban-specific classes
 *
 * @param    string $class_name Name of class to load.
 */
function kanban_autoloader( $class_name ) {
	if ( false !== strpos( $class_name, 'Kanban_' ) && ! class_exists( $class_name ) ) {
		$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
		$class_file  = str_replace( '_', DIRECTORY_SEPARATOR, $class_name ) . '.php';
		require_once $classes_dir . $class_file;
	}
}

spl_autoload_register( 'kanban_autoloader' );



/**
 * Class Kanban
 */
final class Kanban {
	/**
	 * The singleton instance of Kanban.
	 *
	 * @var object @instance The singleton instance of Kanban.
	 */
	static $instance = false;

	/**
	 * The slug used everywhere.
	 *
	 * @var string @slug The slug used everywhere.
	 */
	static $slug = 'kanban';



	/**
	 * Setup the core plugin.
	 */
	public static function init() {

		// Get instance.
		self::$instance = self::get_instance();

		// Build settings used throughout the plugin and add-ons.
		Kanban::get_instance()->settings = (object)array();
		Kanban::get_instance()->settings->path = dirname( __FILE__ );
		Kanban::get_instance()->settings->file = basename( __FILE__, '.php' );

		if ( !function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		Kanban::get_instance()->settings->plugin_data = get_plugin_data( __FILE__ );
		Kanban::get_instance()->settings->basename = strtolower( __CLASS__ );
		Kanban::get_instance()->settings->plugin_basename = plugin_basename( __FILE__ );
		Kanban::get_instance()->settings->uri = plugin_dir_url( __FILE__ );
		Kanban::get_instance()->settings->pretty_name = __( 'Kanban', Kanban::get_instance()->settings->file );
		Kanban::get_instance()->settings->admin_notice = '';

		// Require at least PHP 5.3.
		if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
			Kanban::get_instance()->settings->admin_notice = __( 'The %s plugin requires at least PHP 5.3. You have %s. Please upgrade and then re-install the plugin.', 'kanban' );
			add_action( 'admin_notices', array( __CLASS__, 'notify_php_version' ) );
			return;
		}

		Kanban_Admin::init();
		Kanban_Board::init();
		Kanban_Db::init();
		Kanban_Estimate::init();
		Kanban_License::init();
		Kanban_Option::init();
		Kanban_Project::init();
		Kanban_Status::init();
		Kanban_Task::init();
		Kanban_Task_Hour::init();
		Kanban_Template::init();
		Kanban_User::init();

//		register_activation_hook( __FILE__, array( __CLASS__, 'on_activation' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'on_deactivation' ) );
//		add_action( 'wpmu_new_blog', array( __CLASS__, 'on_new_blog' ), 10, 6 );

		do_action( 'kanban_loaded' );
	}



	/**
	 * On activation, run the single activation across all blogs.
	 * @link http://shibashake.com/wordpress-theme/write-a-plugin-for-wordpress-multi-site
	 *
	 * @param bool $network_wide If plugin is being used across the multisite.
	 */
//	public static function on_activation( $networkwide ) {
//		global $wpdb;
//
//		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
//
//			// Check if it is a network activation - if so, run the activation function for each blog id.
//			if ( $networkwide ) {

//				$old_blog = $wpdb->blogid;
//
//				// Get all blog ids.
//				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = $wpdb->siteid;" );
//
//				foreach ( $blogids as $blog_id ) {
//					switch_to_blog( $blog_id );
//
//					// Activate based on switched-to blog.
//					self::single_activation();
//				}
//
//				// Switch back to previous.
//				switch_to_blog( $old_blog );

//			} else {
//				self::single_activation();
//			}
//		} else {
//			self::single_activation();
//		}
//
//	}



	/**
	 * Functions to do on single blog activation, like update db.
	 */
	static function single_activation() {

//		Kanban_Db::check_for_updates();

		set_transient(
			sprintf( '_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename ),
			true,
			30
		);
	}



	/**
	 * Functions to do on single blog activation, like remove db option.
	 */
	static function on_deactivation() {
		delete_option( 'kanban_db_version' );
	}



//	static function on_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
//
//		//replace with your base plugin path E.g. dirname/filename.php
//		if ( !is_plugin_active_for_network( 'kanban/kanban.php' ) ) return;
//
//		global $wpdb;
//
//		$old_blog = $wpdb->blogid;
//		switch_to_blog( $blog_id );
//		self::single_activation();
//		switch_to_blog( $old_blog );
//	}



	/**
	 * Friendly notice about php version requirement
	 */
	static function notify_php_version() {
		if ( ! is_admin() ) {
			return;
		}
		?>
		<div class="error below-h2">
			<p>
				<?php
				echo sprintf(
					Kanban::get_instance()->settings->admin_notice,
					Kanban::get_instance()->settings->pretty_name,
					PHP_VERSION
				);
				?>
			</p>
		</div>
		<?php
	}



	/**
	 * Get the instance of this class
	 *
	 * @return object the instance
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}



	/**
	 * Construct that can't be overwritten
	 */
	private function __construct() {
	}
} // Kanban



// Instantiate the plugin.
function Kanban()
{
	return Kanban::init();
}

Kanban();

