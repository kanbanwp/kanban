<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



class Kanban_Post
{


	// save a post
	static function save ($post_data)
	{
		do_action( sprintf('%s_before_post_save', Kanban::$instance->settings->basename) );



		global $wpdb;


		$post_template = array(
			'post_content'   => '',
			'post_title'     => '',
			'post_status'    => 'publish',
			'post_type'      => '',
			'post_author'    => 0,
			'is_new' => FALSE
		);



		$post_data = array_merge($post_template, $post_data);

		$post_data = (object) $post_data;



		// make sure the post data has a post type
		if ( empty($post_data->post_type) ) return false;

		$post_type = $post_data->post_type;



		// load it if it already exists
		$orig_post;
		if ( isset($post_data->ID) )
		{
			$orig_post = get_post($post_data->ID);
		}



		// set current user as author if not defined
		if ( !isset($post_data->post_author) )
		{
			$post_data->post_author = get_current_user_id();
		}



		// insert
		if ( empty($orig_post->ID) )
		{
			$post = array(
				'post_content'   => sanitize_text_field($post_data->post_content),
				'post_title'     => sanitize_text_field($post_data->post_title),
				'post_status'    => 'publish',
				'post_type'      => $post_type,
				'post_author'    => $post_data->post_author
			);

			$post_id = wp_insert_post($post);
			$post_data->is_new = TRUE;
		}
		else
		{
			$orig_post->postmeta = (array) Kanban_Post::get_postmeta_for_posts(array($orig_post->ID), $post_type)[$orig_post->ID];
			$orig_post->terms = (array) Kanban_Terms::get_terms_for_posts(array($orig_post->ID), $post_type)[$orig_post->ID];

			// only save if changed
			if ( $orig_post->post_title != $post_data->post_title || $orig_post->post_content != $post_data->post_content )
			{
				$my_post = array(
					'ID'           => $post_data->ID,
					'post_content'   => sanitize_text_field($post_data->post_content),
					'post_title'     => sanitize_text_field($post_data->post_title)
				);

				wp_update_post( $my_post );
			}

			$post_id = $post_data->ID;
		}



		// save fields
		foreach (Kanban::$instance->postmeta_fields_list as $field_name)
		{
			if ( isset($post_data->postmeta[$field_name]) && strpos($field_name, $post_type) !== FALSE )
			{
				if ( $post_data->postmeta[$field_name] !== $orig_post->postmeta[$field_name] )
				{
					update_post_meta($post_id, $field_name, $post_data->postmeta[$field_name]);
				}
			}
		}



		// save taxonomies
		foreach (Kanban::$instance->taxonomies_list as $taxonomy_name)
		{
			if ( isset($post_data->terms[$taxonomy_name]) && strpos($taxonomy_name, $post_type) !== FALSE )
			{
				if ( $post_data->terms[$taxonomy_name] !== $orig_post->terms[$taxonomy_name] )
				{

					// delete records
					// @link http://stackoverflow.com/a/4192849/38241
					// why do I need to do this?!
					$sql = "DELETE `{$wpdb->prefix}term_relationships`
							FROM `{$wpdb->prefix}term_relationships`
							JOIN `{$wpdb->prefix}term_taxonomy`
							on `{$wpdb->prefix}term_relationships`.`term_taxonomy_id` = `{$wpdb->prefix}term_taxonomy`.`term_taxonomy_id`
							WHERE `{$wpdb->prefix}term_relationships`.`object_id` = $post_id
							AND `{$wpdb->prefix}term_taxonomy`.`taxonomy` = '$taxonomy_name'
					";
					$wpdb->query($sql);

					// make sure all values are ints, so they'll be used as id's
					$post_data->terms[$taxonomy_name] = array_map('intval', $post_data->terms[$taxonomy_name]);
					$post_data->terms[$taxonomy_name] = array_unique( $post_data->terms[$taxonomy_name] );

					wp_set_object_terms($post_id, $post_data->terms[$taxonomy_name], $taxonomy_name, FALSE);
				}
			}
		}



		// reload it, just in case
		$post = get_post($post_id);
		$post->postmeta = Kanban_Post::get_postmeta_for_posts(array($post_id), $post_type)[$post_id];
		$post->terms = Kanban_Terms::get_terms_for_posts(array($post_id), $post_type)[$post_id];



		do_action( sprintf('%s_after_post_save', Kanban::$instance->settings->basename) );



		return apply_filters(
			sprintf('%s_after_post_save', Kanban::$instance->settings->basename),
			$post
		);

	} // save



	/**
	 * delete a post
	 * @param  array $post_data
	 * @return bool
	 */
	static function delete ($post_data)
	{
		do_action( sprintf('%s_before_post_delete', Kanban::$instance->settings->basename) );

		$post_data = (object) $post_data;

		$is_success = wp_trash_post($post_data->ID);

		do_action( sprintf('%s_after_post_delete', Kanban::$instance->settings->basename) );

		return $is_success;
	}



	/**
	 * get postmeta or terms for array of posts
	 * @param  [type] $post_ids_arr [description]
	 * @param  [type] $post_type    [description]
	 * @return [type]               [description]
	 */
	static  function get_postmeta_for_posts ($post_ids_arr, $post_type)
	{
		global $wpdb;

		// build comma-separated string of post id's for sql
		$post_ids_str = implode (',', $post_ids_arr);

		// build look up of post meta data by post id
		$postmeta_fields_str = sprintf('"%s"', implode('","', Kanban::$instance->postmeta_fields_list));



		// get records
		$sql = "SELECT `{$wpdb->prefix}postmeta`.*,
				`{$wpdb->prefix}posts`.`post_type`
				FROM `{$wpdb->prefix}postmeta`
				JOIN `{$wpdb->prefix}posts`
				ON  `{$wpdb->prefix}posts`.`ID` = `{$wpdb->prefix}postmeta`.`post_id`
				WHERE `{$wpdb->prefix}postmeta`.`post_id` IN ($post_ids_str)
				AND `{$wpdb->prefix}postmeta`.`meta_key` IN ($postmeta_fields_str)
		";

		$sql = apply_filters(
			sprintf('%s_sql_get_postmeta_for_posts', Kanban::$instance->settings->basename),
			$sql
		);

		$records = $wpdb->get_results($sql);



		// build postmeta
		$postmeta = array();
		foreach ($records as $record)
		{
			if ( !isset($postmeta[$record->post_id]) )
			{
				$postmeta[$record->post_id] = (object) array();
			}

			$postmeta[$record->post_id]->{$record->meta_key} = $record->meta_value;
		}



		// add additional blank fields
		foreach (Kanban::$instance->postmeta_fields_list as $field_name)
		{
			if ( strpos($field_name, $post_type) === FALSE ) continue;

			foreach ($post_ids_arr as $post_id)
			{
				if ( !isset($postmeta[$post_id]) )
				{
					$postmeta[$post_id] = (object) array();
				}

				if ( !isset($postmeta[$post_id]->$field_name) )
				{
					$postmeta[$post_id]->$field_name = Kanban::$instance->field_defaults[$field_name];
				}
			}
		}



		$postmeta = apply_filters(
			sprintf('%s_postmeta_for_posts', Kanban::$instance->settings->basename),
			$postmeta
		);



		return $postmeta;
	}



	/**
	 * Apply all postmeta and terms to an array of posts
	 * @param  arr $posts array of post objects
	 * @return arr        array of posts, with ID's as keys, and all postmeta and terms applied
	 */
	static function apply_postmeta_and_terms_to_posts($posts)
	{
		// make sure the first post has a post type
		if ( empty($posts[0]->post_type) ) return $posts;



		// build array of id's
		$post_id_arr = array();

		foreach ($posts as $post)
		{
			$post_id_arr[] = $post->ID;
		}



		// get postmeta for all posts
		$postmeta = Kanban_Post::get_postmeta_for_posts($post_id_arr, $posts[0]->post_type);

		// get terms for all posts
		$terms = Kanban_Terms::get_terms_for_posts($post_id_arr, $posts[0]->post_type);



		// apply post meta and terms to projects
		foreach ($posts as $post)
		{
			$post->postmeta = $postmeta[$post->ID];
			$post->terms = $terms[$post->ID];
		}



		// put get array with post id's as keys
		return Kanban_Utils::build_array_with_id_keys($posts);
	}



	protected function __construct() {}
}


