<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Admin::init();



class Kanban_Admin
{
	static function init ()
	{
		add_filter('show_admin_bar', array(__CLASS__, 'remove_admin_bar'));

		// // add settings link
		add_filter(
			'plugin_action_links_' . Kanban::$instance->settings->file,
			array(__CLASS__, 'add_plugin_settings_link')
		);


		add_action( 'admin_init',array(__CLASS__, 'welcome_screen_do_activation_redirect') );
	}



	// redirect to welcome page
	// @link http://premium.wpmudev.org/blog/tabbed-interface/
	static function welcome_screen_do_activation_redirect()
	{
		// Bail if no activation redirect
		if ( ! get_transient( sprintf('_%s_welcome_screen_activation_redirect', Kanban::$instance->settings->basename) ) )
		{
			return;
		}

		// Delete the redirect transient
		delete_transient( sprintf('_%s_welcome_screen_activation_redirect', Kanban::$instance->settings->basename) );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
		{
			return;
		}

		// Redirect to about page
		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => Kanban::$instance->settings->basename,
					'activation' => '1'
				),
				admin_url( 'admin.php' )
			)
		);
	}



	// Remove Admin bar
	static function remove_admin_bar()
	{
		if ( strpos($_SERVER['REQUEST_URI'], sprintf('/%s/', Kanban::$slug)) === FALSE ) return;

	    return false;
	}



	static function add_plugin_settings_link($links)
	{
		$settings_link = sprintf('<a href="%s?page=%s">Settings</a>', self::get_instance()->page['path'], self::get_instance()->page['basename']);
		array_unshift($links, $settings_link);
		return $links;
	}
}



