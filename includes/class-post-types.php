<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Post_Types::init();



class Kanban_Post_Types
{
	static $post_types = array(
		'task' => array(
			'taxonomies' => array(
				'status'=> array(
					'backlog' => 'Backlog',
					'ready' => 'Ready',
					'in-progress' => 'In progress',
					'qa' => 'QA',
					'done' => 'Done',
					'archive' => 'Archive'
				),
				'estimate' => array(
					'2' => '2h',
					'4' => '4h',
					'8' => '1d',
					'16' => '2d',
					'32' => '4d'
				)
			),
			'postmeta_fields' => array(
				'project_id' => 0,
				'user_id_assigned' => 0,
				'work_hour_count' => 0,
			),
		),
		'project' => array(
			'taxonomies' => array(),
			'postmeta_fields' => array(
				'color' => 'lightblue'
			),
		),
		'work_hour' => array(
			'taxonomies' => array(),
			'postmeta_fields' => array(
				'task_id' => 0,
				'task_status_id' => 0,
				'project_id' => 0,
				'user_id_logged' => 0,
				'operator' => '+1',
			),
		)
	); // post_types



	static function init()
	{
		self::$post_types = apply_filters(
			sprintf('%s_Post_Types_init', Kanban::$instance->settings->basename),
			self::$post_types
		);



		add_action('init', array(__CLASS__, 'custom_post_types'), 0);
		
		// Hook into gettext ASAP to set proper translation string for estimate
		add_action( 'gettext', array( __CLASS__, 'estimate_slug' ), 0, 3 );



		// build list of post types, fields and taxonomies
		foreach (Kanban_Post_Types::$post_types as $post_type_slug => $post_type_data)
		{
			$post_type_key = Kanban_Post_Types::format_post_type($post_type_slug);

			if ( !isset(Kanban::$instance->post_type_keys) )
			{
				Kanban::$instance->post_type_keys = array();
			}

			Kanban::$instance->post_type_keys[$post_type_slug] = $post_type_key;

			foreach ($post_type_data as $key_type => $key_type_data )
			{
				foreach ( $key_type_data as $key_slug => $values)
				{
					$variable = sprintf('%s_list', $key_type);

					if ( !isset(Kanban::$instance->$variable) )
					{
						Kanban::$instance->$variable = array();
					}

					// get variable by reference
					$array = &Kanban::$instance->$variable;
					$array[] = Kanban_Utils::format_key ($post_type_slug, $key_slug);

					if ( !is_array($values) )
					{
						Kanban::$instance->field_defaults[Kanban_Utils::format_key ($post_type_slug, $key_slug)] = $values;
					}
				}
			}
		}
	}
	
	/**
	 * Over-ride the slug default translation string for estimates taxonomy.
	 *
	 * @param string $translation Current translated string of $text.
	 * @param string $text Original string to be translated.
	 * @param string $domain Domain originating the string.
	 */
	public static function estimate_slug( $translation, $text, $domain ) {
		
		if (
			'default' !== $domain ||
			!isset( $_GET['post_type'] ) ||
			( isset( $_GET['post_type'] ) && Kanban_Post_Types::format_post_type( 'task' ) !== $_GET['post_type'] ) ||
			!isset( $_GET['taxonomy'] ) ||
			( isset( $_GET['taxonomy'] ) && Kanban_Utils::format_key( 'task', 'estimate' ) !== $_GET['taxonomy'] )
		) {
			return $translation;
		}
		
		$kanban_translation = __( 'The &#8220;slug&#8221; is the URL-friendly version of the name. For estimates, it should be the number of working hours.', Kanban::get_text_domain() );
		switch( $text ) {
			case 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.':
				return $kanban_translation;
				break;
			case '<strong>Slug</strong> &mdash; The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.':
				return sprintf( '<strong>%s</strong> &mdash; %s', __( 'Slug', Kanban::get_text_domain() ), $kanban_translation );
				break;
			default:
				break;
		}
		
		return $translation;
	}



	// Register Custom Post Type
	static function custom_post_types()
	{
		foreach (Kanban_Post_Types::$post_types as $post_type_slug => $post_type_data)
		{
			$post_type_label = ucfirst($post_type_slug);

			$labels = array(
				'name'                => str_replace('_', ' ', sprintf('%ss', $post_type_label)),
				'singular_name'       => str_replace('_', ' ', $post_type_label),
				'menu_name'           => str_replace('_', ' ', sprintf('%ss', $post_type_label)),
				'name_admin_bar'      => str_replace('_', ' ', $post_type_label),
				'parent_item_colon'   => str_replace('_', ' ', sprintf('Parent %s:', $post_type_label)),
				'all_items'           => str_replace('_', ' ', sprintf('All %ss', $post_type_label)),
				'add_new_item'        => str_replace('_', ' ', sprintf('Add New %s', $post_type_label)),
				'add_new'             => 'Add New',
				'new_item'            => str_replace('_', ' ', sprintf('New %s', $post_type_label)),
				'edit_item'           => str_replace('_', ' ', sprintf('Edit %s', $post_type_label)),
				'update_item'         => str_replace('_', ' ', sprintf('Update %s', $post_type_label)),
				'view_item'           => str_replace('_', ' ', sprintf('View %s', $post_type_label)),
				'search_items'        => str_replace('_', ' ', sprintf('Search %s', $post_type_label)),
				'not_found'           => 'Not found',
				'not_found_in_trash'  => 'Not found in Trash',
			);
			$rewrite = array(
				'slug'                => NULL, // sprintf('%s/%s', Kanban::$slug, Kanban_Utils::make_word_plural($post_type_slug)),
				'with_front'          => true,
				'pages'               => true,
				'feeds'               => true,
			);
			$args = array(
				'label'               => $post_type_label,
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'custom-fields', ),
				'hierarchical'        => true,
				'public'              => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'menu_position'       => 5,
				'show_in_admin_bar'   => false,
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'has_archive'         => false, //sprintf('%s/%s', Kanban::$slug, Kanban_Utils::make_word_plural($post_type_slug)),
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'rewrite'             => false, // $rewrite,
				'capability_type'     => 'page',
			);

			register_post_type(
				Kanban_Post_Types::format_post_type ($post_type_slug),
				$args
			);

			// register taxonomies associated with each post type
			foreach ($post_type_data['taxonomies'] as $taxonomy_slug => $values)
			{
				$taxonomy_key = Kanban_Utils::format_key($post_type_slug, $taxonomy_slug);
				$taxonomy_label = ucwords(sprintf('%s %s', $post_type_slug, $taxonomy_slug));

				$labels = array(
					'name'                       => $taxonomy_label,
					'singular_name'              => $taxonomy_label,
					'menu_name'                  => $taxonomy_label,
					'all_items'                  => sprintf('All %ss', $taxonomy_label),
					'parent_item'                => sprintf('Parent %s', $taxonomy_label),
					'parent_item_colon'          => sprintf('Parent %s:', $taxonomy_label),
					'new_item_name'              => sprintf('New %s Name', $taxonomy_label),
					'add_new_item'               => sprintf('Add New %s', $taxonomy_label),
					'edit_item'                  => sprintf('Edit %s', $taxonomy_label),
					'update_item'                => sprintf('Update %s', $taxonomy_label),
					'view_item'                  => sprintf('View %s', $taxonomy_label),
					'separate_items_with_commas' => sprintf('Separate %ss with commas', $taxonomy_label),
					'add_or_remove_items'        => sprintf('Add or remove %ss', $taxonomy_label),
					'choose_from_most_used'      => 'Choose from the most used',
					'popular_items'              => sprintf('Popular %ss', $taxonomy_label),
					'search_items'               => sprintf('Search %ss', $taxonomy_label),
					'not_found'                  => 'Not Found',
				);
				$rewrite = array(
					'slug'                       => sprintf('%s/%s/%s', Kanban::$slug, Kanban_Utils::make_word_plural($post_type_slug), Kanban_Utils::make_word_plural($taxonomy_slug)),
					'with_front'                 => true,
					'hierarchical'               => false,
				);
				$args = array(
					'labels'                     => $labels,
					'hierarchical'               => false,
					'public'                     => true,
					'show_ui'                    => true,
					'show_admin_column'          => true,
					'show_in_nav_menus'          => false,
					'show_tagcloud'              => false,
					'rewrite'                    => false, // $rewrite,
				);

				register_taxonomy(
					$taxonomy_key,
					Kanban_Post_Types::format_post_type ($post_type_slug),
					$args
				);
			} // taxonomies
		} // post_types




	} // post_types




	// utility function to build post type name
	static function format_post_type ($post_type)
	{
		return sprintf('%s_%s', Kanban::$instance->settings->basename, $post_type);
	}



	// retrieve list of registered post types
	static function get_post_types_list($allowed_post_types = array())
	{
		$return = array();
		foreach (Kanban_Post_Types::$post_types as $post_type_slug => $post_type_data)
		{
			if ( !empty($allowed_post_types) )
			{
				if ( !in_array($post_type_slug, $allowed_post_types) )
				{
					continue;
				}
			}
			$return[] = Kanban_Post_Types::format_post_type ($post_type_slug);
		}

		return $return;
	}



}


