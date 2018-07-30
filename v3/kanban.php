<?php
/*
Plugin Name:        Kanban Boards for WordPress
Plugin URI:         https://kanbanwp.com/
Description:        Add Kanban boards to your WordPress sites and get organized! Project management, CRM, sales tracking...
Version:            3.0.8
Release Date:       July 30, 2018
Tested up to:	    4.9.7
Requires at least:  4.0
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
		if ( is_file( $classes_dir . $class_file ) ) {
			require_once $classes_dir . $class_file;
		}
	}
}

class Kanban {

	/**
	 * The singleton instance of Kanban.
	 *
	 * @var object @instance The singleton instance of Kanban.
	 */
	private static $instance = false;

	/**
	 * The slug used everywhere.
	 *
	 * @var string @slug The slug used everywhere.
	 */
	private static $slug = 'kanban';


	private $settings = array();

	/**
	 * Construct that can't be overwritten
	 */
	private function __construct() {

		// Build settings used throughout the plugin and add-ons.
		$this->settings = (object) array();

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$this->settings->plugin_data     = get_plugin_data( __FILE__ );
		$this->settings->basename        = strtolower( __CLASS__ );
		$this->settings->plugin_basename = plugin_basename( __FILE__ );
		$this->settings->uri             = plugin_dir_url( __FILE__ );
		$this->settings->path            = plugin_dir_path( __FILE__ );
		$this->settings->pretty_name     = __( 'Kanban', 'kanban' );
		$this->settings->is_network      = is_plugin_active_for_network( $this->settings->basename . '/' . basename(__FILE__) ) ? true : false;
		$this->settings->admin_notice    = '';


		// Require at least PHP 5.3.
		if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
			$this->settings->admin_notice = __( 'The %s plugin requires at least PHP 5.3. You have %s. Please upgrade and then re-install the plugin.', 'kanban' );
			add_action( 'admin_notices', array( __CLASS__, 'notify_php_version' ) );

			return;
		}

		do_action( 'kanban_loaded' );
	}

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
					Kanban::instance()->settings->admin_notice,
					Kanban::instance()->settings->pretty_name,
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
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

			spl_autoload_register( 'kanban_autoloader' );


			register_deactivation_hook( __FILE__, array( __CLASS__, 'on_deactivation' ) );

			Kanban_Admin::instance();
			Kanban_Router::instance();
			Kanban_Db::instance();
			Kanban_Field_Date::instance();
			Kanban_Field_Colorpicker::instance();
			Kanban_Field_File::instance();
			Kanban_Field_Img::instance();
			Kanban_Field_Tags::instance();
			Kanban_Field_Text::instance();
			Kanban_Field_Title::instance();
			Kanban_Field_Users::instance();


//		Kanban_Board::init();
//		Kanban_Db::init();
//		Kanban_Estimate::init();
//		Kanban_License::init();
//		Kanban_Option::init();
//		Kanban_Project::init();
//		Kanban_Status::init();
//		Kanban_Task::init();
//		Kanban_Task_Hour::init();
//		Kanban_Template::init();
//		Kanban_User::init();


		}

		return self::$instance;
	}

	/**
	 * Functions to do on single blog activation, like remove db option.
	 */
	public function on_deactivation() {
		delete_option( 'kanban_db_version' );
	}

	public function settings() {
		return $this->settings;
	}


}

function Kanban() {
	Kanban::instance();
}

Kanban();
