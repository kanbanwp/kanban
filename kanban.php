<?php
/*
Plugin Name:		Kanban for WordPress
Plugin URI:			http://kanbanwp.com/
Description:		A complete Kanban project management suite for WordPress.
Version:			2.2.1
Release Date:		September 5, 2016
Author:				Gelform Inc
Author URI:			http://gelwp.com
License:			GPL2
Text Domain:		kanban
Domain Path: 		/languages/

Kanban is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Kanban is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Kanban. If not, see <http://www.gnu.org/licenses/>.

*/



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;





spl_autoload_register( 'kanban_autoloader' );
function kanban_autoloader( $class_name )
{
	if ( false !== strpos( $class_name, 'Kanban' ) && !class_exists($class_name) )
	{
		$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
		$class_file = str_replace( '_', DIRECTORY_SEPARATOR, $class_name ) . '.php';
		require_once $classes_dir . $class_file;
	}
}



class Kanban
{
	static $instance = false;
	static $slug = 'kanban';



	static function init()
	{
		// get instance
		self::$instance = self::get_instance();



		// build settings
		Kanban::get_instance()->settings = (object) array();
		Kanban::get_instance()->settings->path = dirname( __FILE__ );
		Kanban::get_instance()->settings->file = basename( __FILE__, '.php' );

		if ( ! function_exists( 'get_plugin_data' ) )
		{
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		Kanban::get_instance()->settings->plugin_data = get_plugin_data( __FILE__ );
		Kanban::get_instance()->settings->basename = strtolower( __CLASS__ );
		Kanban::get_instance()->settings->plugin_basename = plugin_basename( __FILE__ );
		Kanban::get_instance()->settings->uri = plugin_dir_url( __FILE__ );
		Kanban::get_instance()->settings->pretty_name = __( 'Kanban', Kanban::get_instance()->settings->file );
		Kanban::get_instance()->settings->admin_notice = '';



		// require at least PHP 5.3
		if ( version_compare( PHP_VERSION, '5.3', '<' ) )
		{
			Kanban::get_instance()->settings->admin_notice = __('The %s plugin requires at least PHP 5.3. You have %s. Please upgrade and then re-install the plugin.', 'kanban');
			add_action( 'admin_notices', array( __CLASS__, 'notify_php_version' ) );
			return;
		}



		Kanban_Admin::init();
		Kanban_Board::init();
		Kanban_Db::init();
		Kanban_Estimate::init();
//		Kanban_Flash::init();
		Kanban_License::init();
		Kanban_Option::init();
		Kanban_Project::init();
		Kanban_Status::init();
		Kanban_Task::init();
		Kanban_Task_Hour::init();
		Kanban_Template::init();
		Kanban_User::init();
		


		register_activation_hook( __FILE__, array( __CLASS__, 'on_activation' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'on_deactivation' ) );



		do_action( 'kanban_loaded' );
	}

	

	public static function on_activation( $network_wide )
	{
		if ( function_exists( 'is_multisite' ) && is_multisite() )
		{
			if ( $network_wide  )
			{
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id )
				{
					switch_to_blog( $blog_id );
					self::single_activate();
				}
				restore_current_blog();
			}
			else
			{
				self::single_activate();
			}
		}
		else
		{
			self::single_activate();
		}

	}



	static function single_activate()
	{
		// check for db updates and migration
		Kanban_Db::check_for_updates();



		// populate defaults
//		if ( Kanban::get_instance()->settings->records_to_move == 0 )
//		{
//			add_action( 'init', Kanban_Db::add_defaults() );
//		}



		// redirect to welcome page
		// @link http://premium.wpmudev.org/blog/tabbed-interface/
		set_transient(
			sprintf( '_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename ),
			true,
			30
		);
	}



	static function on_deactivation()
	{
		delete_option('kanban_db_version');
	}



	/**
	 * friendly notice about php version requirement
	 */
	static function notify_php_version()
	{
		if ( ! is_admin() ) return;
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



} // Kanban



// instantiate the plugin
Kanban::init();
