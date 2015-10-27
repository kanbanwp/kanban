<?php
/*
Plugin Name:	Kanban for WordPress
Plugin URI:		http://kanbanwp.com/
Description:	A complete kanban board for WordPress. Use agile project management to get more done, right inside your WordPress site!
Version:		1.0.1
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
            'menu_slug'         => 'kanban',
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
		Kanban::get_instance()->settings->basename = strtolower(__CLASS__);
		Kanban::get_instance()->settings->uri = plugin_dir_url(__FILE__);
		Kanban::get_instance()->settings->pretty_name = __('Kanban', Kanban::get_instance()->settings->file);
		Kanban::get_instance()->settings->db_version = '1.0';



		// needs to come first
		include_once Kanban::get_instance()->settings->path . '/includes/class-utils.php';
		include_once Kanban::get_instance()->settings->path . '/includes/class-db.php';

		// Automatically load classes
		$files = glob(Kanban::get_instance()->settings->path . '/includes/class-*.php');
		foreach ($files as $file)
		{
		    include_once $file;
		}

		register_activation_hook( __FILE__, array( __CLASS__, 'on_activation' ) );
	}



	static function on_activation()
	{
		Kanban_Db::check_for_updates();



		// http://wordpress.stackexchange.com/questions/20043/inserting-taxonomy-terms-during-a-plugin-activation
		Kanban_Post_Types::custom_post_types();



		$is_installed_before = get_option( sprintf('_%s_is_installed_before', Kanban::get_instance()->settings->basename) );


		if ( !$is_installed_before )
		{

			foreach ( Kanban_Post_Types::$post_types as $post_type_slug => $post_type_data )
			{
				foreach ($post_type_data['taxonomies'] as $taxonomy_slug => $values)
				{
					$taxonomy_key = Kanban_Utils::format_key ($post_type_slug, $taxonomy_slug);

					foreach ($values as $slug => $value)
					{
						$term_id_arr = wp_insert_term(
							$value,
							$taxonomy_key,
							array(
								'slug' => $slug
							)
						);
					}
				}
			}



			// add current user to board
			$users_field_name = sprintf('%s_user', Kanban::$instance->settings->basename);
			$user_settings = Kanban_Settings::get_option($users_field_name, null, array());

			if ( empty($user_settings) )
			{
				$current_user_id = get_current_user_id();

				if ( !isset($user_settings['allowed_users']) )
				{
					$user_settings['allowed_users'] = array();
				}

				if ( !in_array($current_user_id, $user_settings['allowed_users']) )
				{
					$user_settings['allowed_users'][$current_user_id] = $current_user_id;
				}

				update_option($users_field_name, $user_settings);
			}



			// add status order
			$tax_key = Kanban_Utils::format_key ('task', 'status');
			$field_name = sprintf('%s_order', $tax_key);
			$settings = Kanban_Settings::get_option($field_name, null, array());

			if ( empty($settings) )
			{
				$slugs_in_order = array_keys(Kanban_Post_Types::$post_types['task']['taxonomies']['status']);

				$term_ids_in_order = array();
				foreach ($slugs_in_order as $order => $slug)
				{
					$term = get_term_by('slug', $slug, $tax_key);
					$term_ids_in_order[$term->term_id] = $order;
				}

				update_option( $field_name, $term_ids_in_order);
			}



			// add estimate order
			$tax_key = Kanban_Utils::format_key ('task', 'estimate');
			$field_name = sprintf('%s_order', $tax_key);
			$settings = Kanban_Settings::get_option($field_name, null, array());

			if ( empty($settings) )
			{
				$slugs_in_order = array_keys(Kanban_Post_Types::$post_types['task']['taxonomies']['estimate']);

				$term_ids_in_order = array();
				foreach ($slugs_in_order as $order => $slug)
				{
					$term = get_term_by('slug', $slug, $tax_key);
					$term_ids_in_order[$term->term_id] = $order;
				}

				update_option( $field_name, $term_ids_in_order);
			}



			$field_name = sprintf('%s_colors', Kanban_Utils::format_key ('task', 'status'));
			$settings = get_option('kanban_task_status_colors');

			if ( empty($settings) )
			{

				// add status colors
				$colors = array(
					'backlog' => '#8224e3',
					'ready' => '#eeee22',
					'in-progress' => '#81d742',
					'qa' => '#dd9933',
					'done' => '#1e73be',
					'archive' => '#545454'
				);

				// get status tax
				$taxonomies = array(
				    Kanban_Utils::format_key ('task', 'status')
				);

				$args = array(
				    'orderby'           => 'term_id',
				    'order'             => 'ASC',
				    'hide_empty'        => false,
				    'childless'         => false,
				);

				$terms = get_terms($taxonomies, $args);

				// build array of status id => color
				$option = array();
				foreach ($terms as $term)
				{
					if ( !isset($colors[$term->slug]) ) continue;
					$option[$term->term_id] = $colors[$term->slug];
				}

				// save it
				update_option(
					$field_name,
					$option
				);
			} // save colors



			// check for existing tasks
			$args = array(
				'post_type' => Kanban_Post_Types::format_post_type('task')
			);
			$posts_array = get_posts( $args );

			// if no tasks, add a sample
			if ( empty($posts_array) )
			{
				// add an example task
				$post = array(
					'post_status'    => 'publish',
					'post_type'      => Kanban_Post_Types::format_post_type('task'),
					'post_author'    => $current_user_id
				);

				$post_id = wp_insert_post($post);
			}



			// save that the plugin has been installed
			update_option( sprintf('_%s_is_installed_before', Kanban::get_instance()->settings->basename), TRUE );

		} // $is_installed_before



		flush_rewrite_rules();



		// redirect to welcome page
		// @link http://premium.wpmudev.org/blog/tabbed-interface/
		set_transient(
			sprintf('_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename),
			true,
			30
		);
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



