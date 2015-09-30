<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Terms::init();



class Kanban_Terms
{
	static $instance = false;

	static function init()
	{
		self::$instance = self::get_instance();
	}



	static function get_terms_for_posts ($post_ids_arr, $post_type)
	{
		global $wpdb;

		$post_ids_str = implode (',', $post_ids_arr);

		// build look up of post taxonomies by post id
		$sql = "SELECT `{$wpdb->prefix}term_relationships`.`object_id` AS 'post_id',
				`{$wpdb->prefix}term_relationships`.`term_taxonomy_id`,
				`{$wpdb->prefix}term_taxonomy`.`taxonomy`
				FROM `{$wpdb->prefix}term_relationships`
				JOIN `{$wpdb->prefix}term_taxonomy`
				ON `{$wpdb->prefix}term_taxonomy`.`term_taxonomy_id` = `{$wpdb->prefix}term_relationships`.`term_taxonomy_id`
				WHERE  `object_id` IN ($post_ids_str)
		";

		$sql = apply_filters(
			sprintf('%s_sql_get_terms_for_posts', Kanban::$instance->settings->basename),
			$sql
		);

		$records = $wpdb->get_results($sql);



		$terms = array();
		foreach ($records as $record)
		{
			if ( !isset($terms[$record->post_id]) )
			{
				$terms[$record->post_id] = (object) array();
			}

			if ( !isset($terms[$record->post_id]->{$record->taxonomy}) )
			{
				$terms[$record->post_id]->{$record->taxonomy} = array();
			}

			$terms[$record->post_id]->{$record->taxonomy}[] = $record->term_taxonomy_id;
		}



		// add additional blank fields
		foreach (Kanban::$instance->taxonomies_list as $taxonomy_name)
		{
			if ( strpos($taxonomy_name, $post_type) === FALSE ) continue;

			foreach ($post_ids_arr as $post_id)
			{
				if ( !isset($terms[$post_id]) )
				{
					$terms[$post_id] = (object) array();
				}

				if ( !isset($terms[$post_id]->$taxonomy_name) )
				{
					$terms[$post_id]->$taxonomy_name = array(0);
				}
			}
		}



		return $terms;
	}



	static function get_all_terms ()
	{
		if ( !isset(Kanban_Terms::$instance->all_terms) )
		{
			// get all terms for displaying
			$args = array(
				'hide_empty' => 0,
				'orderby' => 'term_order'
			);
			$all_terms = get_terms(Kanban::$instance->taxonomies_list, $args);

			Kanban_Terms::$instance->all_terms = Kanban_Utils::build_array_with_id_keys($all_terms, 'term_id');
		}

		return Kanban_Terms::$instance->all_terms;
	}



	static function terms_in_order($post_type, $key)
	{
		$tax_key = Kanban_Utils::format_key ($post_type, $key);
		$field_name = sprintf('%s_order', $tax_key);

		$order = Kanban_Settings::get_option($field_name, $field_name, array());

		asort($order);



		// get all terms for displaying
		$args = array(
			'hide_empty' => 0
		);
		$terms = get_terms($tax_key, $args);

		$terms = Kanban_Utils::build_array_with_id_keys($terms, 'term_id');



		$terms_in_order = array();

		if ( !empty($order) )
		{
			foreach ($order as $status_id => $order)
			{
				$terms_in_order[] = $terms[$status_id];
				unset($terms[$status_id]);
			}
		}

		return array_filter(array_merge($terms_in_order, $terms));
	}

	public static function get_slug() {
		return self::$slug;
	}

	static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}



	private function __construct() { }
}


