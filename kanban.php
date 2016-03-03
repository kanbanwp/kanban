<?php
/*
Plugin Name:	Kanban for WordPress
Plugin URI:		http://kanbanwp.com/
Description:	A complete kanban board for WordPress. Use agile project management to get more done, right inside your WordPress site!
Version:		1.2.9
Author:			Gelform Inc
Author URI:		http://gelwp.com
License:		GPL2
Text Domain:	kanban
Domain Path: 	/languages/



Kanban is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Kanban is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Kanban. if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// instantiate the plugin
Kanban::init();



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



		// require at least PHP 5.3
		if ( version_compare( PHP_VERSION, '5.3', '<' ) )
		{
			add_action( 'admin_notices', array( __CLASS__, 'notify_php_version' ) );
			return;
		}



		// needs to come first
		include_once Kanban::get_instance()->settings->path . '/includes/class-utils.php';
		include_once Kanban::get_instance()->settings->path . '/includes/class-db.php';

		// Automatically load classes
		$files = glob( Kanban::get_instance()->settings->path . '/includes/class-*.php' );
		foreach ( $files as $file )
		{
			include_once $file;
		}



		// check for old records
		Kanban::get_instance()->settings->records_to_move = Kanban_Db::migrate_records_remaining();



		register_activation_hook( __FILE__, array( __CLASS__, 'on_activation' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'on_deactivation' ) );



		do_action( 'kanban_loaded' );
	}



	static function on_activation()
	{
		// check for db updates and migration
		Kanban_Db::check_for_updates();



		// populate defaults
		if ( Kanban::get_instance()->settings->records_to_move == 0 )
		{
			add_action( 'init', Kanban_Db::add_defaults() );
		}



		// redirect to welcome page
		// @link http://premium.wpmudev.org/blog/tabbed-interface/
		set_transient(
			sprintf( '_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename ),
			true,
			30
		);
	}


	/**
	 * placeholder
	 */
	static function on_deactivation()
	{

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
					__( 'The %s plugin requires at least PHP 5.3. You have %s. Please upgrade and then re-install the plugin.' ),
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
