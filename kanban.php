<?php
/*
Plugin Name:	Kanban for WordPress
Plugin URI:		http://kanbanwp.com/
Description:	A complete kanban board for WordPress. Use agile project management to get more done, right inside your WordPress site!
Version:		1.1.1
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



#region Freemius

// Create a helper function for easy SDK access.
function kan_fs() {
    global $kan_fs;
    if ( ! isset( $kan_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $kan_fs = fs_dynamic_init( array(
            'id'                => '70',
            'slug'              => 'kanban',
            'menu_slug'         => 'kanban_welcome',
            'public_key'        => 'pk_79c5063358baad9d6247046db9a6b',
            'is_live'           => true,
            'is_premium'        => false,
            'has_paid_plans'    => false,
        ) );
    }

    return $kan_fs;
}

// Init Freemius.
kan_fs();

#endregion Freemius



Kanban::init();



class Kanban
{
	static $instance = false;
	static $slug = 'kanban';
	private static $text_domain = 'kanban';



	static function init ()
	{
		self::$instance = self::get_instance();

		Kanban::get_instance()->settings = (object) array();
		Kanban::get_instance()->settings->path = dirname(__FILE__);
		Kanban::get_instance()->settings->file = basename(__FILE__, '.php');
		Kanban::get_instance()->settings->plugin_data = get_plugin_data(__FILE__);
		Kanban::get_instance()->settings->basename = strtolower(__CLASS__);
		Kanban::get_instance()->settings->plugin_basename = plugin_basename(__FILE__);
		Kanban::get_instance()->settings->uri = plugin_dir_url(__FILE__);
		Kanban::get_instance()->settings->pretty_name = __('Kanban', Kanban::get_instance()->settings->file);
		Kanban::get_instance()->settings->db_version = '1.0';



		if (version_compare(PHP_VERSION, '5.3', '<'))
		{
			add_action('admin_notices', array(__CLASS__, 'notify_php_version') );
			return;
		}



		// needs to come first
		include_once Kanban::get_instance()->settings->path . '/includes/class-utils.php';
		include_once Kanban::get_instance()->settings->path . '/includes/class-db.php';

		// Automatically load classes
		$files = glob(Kanban::get_instance()->settings->path . '/includes/class-*.php');
		foreach ($files as $file)
		{
		    include_once $file;
		}



		// check for old records
		Kanban::get_instance()->settings->records_to_move = Kanban_Db::migrate_records_remaining();



		register_activation_hook( __FILE__, array( __CLASS__, 'on_activation' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'on_deactivation' ) );
	}



	static function on_activation()
	{
		Kanban_Db::check_for_updates();



		// flush_rewrite_rules();



		// populate defaults
		if ( Kanban::get_instance()->settings->records_to_move == 0 )
		{
			add_action('init', Kanban_Db::add_defaults());
		}



		// redirect to welcome page
		// @link http://premium.wpmudev.org/blog/tabbed-interface/
		set_transient(
			sprintf('_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename),
			true,
			30
		);
	}



	static function on_deactivation()
	{
		// delete db version, in case of reinstallation
		delete_option(
			sprintf(
				'%s_db_version',
				Kanban::get_instance()->settings->basename
			)
		);
	}



	static function notify_php_version()
	{
		if( !is_admin() ) return;
		?>
			<div class="error below-h2">
				<p>
				<?php
				echo sprintf(
					__('The %s plugin requires at least PHP 5.3. You have %s. Please upgrade and then re-install the plugin.'),
					Kanban::get_instance()->settings->pretty_name,
					PHP_VERSION
				);
				?>
				</p>
			</div>
	<?php
	}



	public static function get_text_domain()
	{
		return Kanban::$text_domain;
	}



	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}



	private function __construct() { }
} // Kanban



