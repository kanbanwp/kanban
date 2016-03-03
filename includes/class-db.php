<?php

/**
 * @link http://mac-blog.org.ua/wordpress-custom-database-table-example-full/
 * @link https://deliciousbrains.com/managing-custom-tables-wordpress/
 */



Kanban_Db::init();



abstract class Kanban_Db
{
	static $installed_ver;



	static function init()
	{
		add_action( 'plugins_loaded', array( __CLASS__, 'check_for_updates' ) );

		add_action( 'wp_ajax_kanban_migrate_db', array( __CLASS__, 'ajax_migrate_records' ) );
	}



	private static function _fetch_sql( $key, $value )
	{
		global $wpdb;
		$sql = sprintf(
			'SELECT * FROM %s WHERE %s = %%s',
			self::table_name(),
			$key,
			$value
		);

		return $wpdb->prepare( $sql, $value );
	}



	protected static function get_row( $key, $value )
	{
		global $wpdb;
		return $wpdb->get_row( self::_fetch_sql( $key, $value ) );
	}




	protected static function get_all( $sql )
	{
		global $wpdb;
		$records = $wpdb->get_results( $sql, OBJECT );

		return $records;
	}




	protected static function _replace( $data )
	{
		$data = self::sanitize_data( $data );

		if ( isset( $data->data['id'] ) && (int) $data->data['id'] > 0 )
		{
			$success = (bool) self::_update(
						$data->data,
						array( 'id' => $data->data['id'] )
					);
		}
		else
		{
			$success = (bool) self::_insert( $data->data );
		}

		return $success;
	}



	protected static function _insert( $data )
	{
		$data = self::sanitize_data( $data );

		global $wpdb;
		$success = (bool) $wpdb->insert( static::table_name(), $data->data, $data->format );

		return $success;
	}



	protected static function _update( $data, $where )
	{
		$data = self::sanitize_data( $data );

		global $wpdb;
		$success = (bool) $wpdb->update( static::table_name(), $data->data, $where, $data->format );

		return $success;
	}



	protected static function _delete( $where )
	{
		global $wpdb;
		$success = $wpdb->delete( static::table_name(), $where );
	}



	// get last inserted id
	protected static function _insert_id()
	{
		global $wpdb;
		return $wpdb->insert_id;
	}



	static function time_to_date( $time )
	{
		return gmdate( 'Y-m-d H:i:s', $time );
	}



	static function now()
	{
		return self::time_to_date( time() );
	}



	static function date_to_time( $date )
	{
		return strtotime( $date . ' GMT' );
	}



	public static function table_name()
	{
		return Kanban_Db::format_table_name( static::$table_name );
	}



	static function sanitize_data( $data )
	{
		$good_data = array();
		$format = array();
		foreach ( $data as $key => $value )
		{
			if ( $key == 'id' )
			{
				if ( ! is_numeric( $value ) ) continue;

				$value = intval( $value );
				if ( empty( $value ) ) continue;

				$good_data[$key] = $value;
				$format[] = '%d';

				continue;
			}

			if ( ! isset( static::$table_columns[$key] ) ) continue;

			switch ( static::$table_columns[$key] )
			{
				case 'bool':
					$value = (bool) $value;

					$good_data[$key] = $value;
					$format[] = '%d';

					break;



				case 'float':
					if ( ! is_numeric( $value ) ) continue;

					$value = floatval( $value );
					if ( empty( $value ) ) continue;

					$good_data[$key] = $value;
					$format[] = '%f';

					break;



				case 'int':
					if ( ! is_numeric( $value ) ) continue;

					$value = intval( $value );

					if ( ! is_int( $value ) ) continue;

					$good_data[$key] = $value;
					$format[] = '%d';

					break;



				case 'text':
					$good_data[$key] = sanitize_text_field( $value );
					$format[] = '%s';

					break;



				case 'datetime':
					if ( is_a( $value, 'DateTime' ) )
					{
						$good_data[$key] = $value->format( 'Y-m-d H:i:s' );
						$format[] = '%s';
					}
					elseif ( ($timestamp = strtotime( $value )) !== FALSE )
					{
						$dt = new DateTime( $value );
						$good_data[$key] = $dt->format( 'Y-m-d H:i:s' );
						$format[] = '%s';
					}

					break;
			} // switch


		}


		return (object) array(
			'data'   => $good_data,
			'format' => $format
		);


	}



	/**
	 * [check_for_updates description]
	 * @link http://mac-blog.org.ua/wordpress-custom-database-table-example-full/
	 * @return   [type] [description]
	 */
	static function check_for_updates()
	{
		if ( self::installed_ver() == Kanban::get_instance()->settings->plugin_data['Version'] ) return FALSE;

		global $charset_collate, $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$classes_with_tables = array(
			'Kanban_Board',
			'Kanban_Comment',
			'Kanban_Estimate',
			'Kanban_Option',
			'Kanban_Project',
			'Kanban_Status_Change',
			'Kanban_Status',
			'Kanban_Task',
			'Kanban_Taskmeta',
			'Kanban_Task_Hour'
		);

		foreach ( $classes_with_tables as $class )
		{
			$sql = $class::db_table();

			$sql = sprintf( '%s %s;', $sql, $charset_collate );

			// save table
			dbDelta( $sql );
		}



		Kanban_Db::add_defaults();



		// make sure every task has a board
		$boards_table = Kanban_Board::table_name();

		$sql = "SELECT `id`
				FROM `{$boards_table}`
				LIMIT 1
		;";

		$board_id = $wpdb->get_var( $sql );



		$classes_with_board_id = array(
			'Kanban_Estimate',
			'Kanban_Status',
			'Kanban_Task',
		);

		foreach ( $classes_with_board_id as $class )
		{
			$table = $class::table_name();

			$sql = "UPDATE $table
				SET `board_id` = $board_id
				WHERE `board_id` IS NULL
				OR `board_id` = 0
			;";

			$wpdb->query( $sql );
		}




		// save db version to avoid updates
		update_option(
			sprintf(
				'%s_db_version',
				Kanban::get_instance()->settings->basename
			),
			Kanban::get_instance()->settings->plugin_data['Version']
		);
	}



	static function migrate_records_remaining()
	{
		global $wpdb;

		$count = $wpdb->get_var(
			"SELECT COUNT( *)
			FROM $wpdb->posts
			WHERE ( `post_type` = 'kanban_task'
				OR `post_type` = 'kanban_project'
				OR `post_type` = 'kanban_task_comment'
				)
				AND `post_status` = 'publish'
			;"
		);

		return $count;
	}



	/**
	 * move users
	 * move terms (not deleted, as we need them for tasks)
	 * move projects
	 * move tasks
	 * move comments
	 * delete projects
	 * delete terms
	 * clean up
	 * @return   [type] [description]
	 */
	static function ajax_migrate_records()
	{
		global $wpdb;



		// build response
		$response = array(
			'posts_remaining' => Kanban::get_instance()->settings->records_to_move,
			'continue'        => FALSE,
			'done'            => FALSE
		);



		// check for users to move
		$is_users_moved = get_option( 'kanban_migrate_users_moved' );

		if ( ! $is_users_moved )
		{
			$sql = "SELECT
					{$wpdb->prefix}options.option_value
					FROM {$wpdb->prefix}options
					WHERE `option_name` = 'kanban_user'
			;";

			$users = $wpdb->get_var( $sql );

			if ( ! empty( $users ) )
			{
				$users = unserialize( $users );

				$data = array(
					'name'  => 'allowed_users',
					'value' => serialize( $users['allowed_users'] )
				);

				Kanban_Option::_replace( $data );

				delete_option( 'kanban_user' );
			}

			update_option( 'kanban_migrate_users_moved', TRUE );

			$response['posts_remaining'] = $response['posts_remaining']-1;
			$response['continue'] = TRUE;
			$response['message'] = 'Allowed users moved';
			wp_send_json_success( $response );
		}



		// check for terms to move
		$is_terms_moved = get_option( 'kanban_migrate_terms_moved' );

		if ( ! $is_terms_moved )
		{
			$sql = "SELECT
					{$wpdb->prefix}term_taxonomy.`taxonomy`
					, {$wpdb->prefix}term_taxonomy.`term_taxonomy_id`
					, {$wpdb->prefix}terms.`term_id`
					, {$wpdb->prefix}terms.`name`
					, {$wpdb->prefix}terms.`slug`
					FROM {$wpdb->prefix}terms
					JOIN {$wpdb->prefix}term_taxonomy
					ON {$wpdb->prefix}terms.`term_id` = {$wpdb->prefix}term_taxonomy.`term_id`
			;";

			$terms = $wpdb->get_results( $sql );



			// if we need to move terms
			if ( ! empty( $terms ) )
			{
				// get settings
				$kanban_task_status_order = get_option( 'kanban_task_status_order' );
				$kanban_task_status_colors = get_option( 'kanban_task_status_colors' );
				$kanban_task_estimate_order = get_option( 'kanban_task_estimate_order' );



				// get statuses for matching
				$status_table = Kanban_Status::table_name();
				$sql = "SELECT * FROM $status_table;";
				$statuses = $wpdb->get_results( $sql );

				$status_arr = array();
				foreach ( $statuses as $status )
				{
					$status_arr[$status->title] = $status->id;
				}



				// get estimates for matching
				$estimates_table = Kanban_Estimate::table_name();
				$sql = "SELECT * FROM $estimates_table;";
				$estimates = $wpdb->get_results( $sql );


				$estimate_arr = array();
				foreach ( $estimates as $estimate )
				{
					$estimate_arr[$estimate->title] = $estimate->id;
				}



				// add each term
				foreach ( $terms as $term )
				{
					switch ( $term->taxonomy )
					{
						case 'kanban_task_status':
							if ( isset( $status_arr[$term->name] ) ) continue;

							$data = array(
								'title'     => $term->name,
								'color_hex' => $kanban_task_status_colors[$term->term_id],
								'position'  => $kanban_task_status_order[$term->term_id],
							);

							$success = Kanban_Status::replace( $data );

							break;

						case 'kanban_task_estimate':
							if ( isset( $estimate_arr[$term->name] ) ) continue;

							$data = array(
								'title'    => $term->name,
								'hours'    => $term->slug,
								'position' => $kanban_task_estimate_order[$term->term_id],
							);

							$success = Kanban_Estimate::replace( $data );

							break;
					}
				}

			}

			update_option( 'kanban_migrate_terms_moved', TRUE );

			$response['posts_remaining'] = $response['posts_remaining']-2;
			$response['continue'] = TRUE;
			$response['message'] = 'statuses and estimates updated';
			wp_send_json_success( $response );

		} // is_terms_moved



		// move projects
		$sql = "SELECT posts.ID as id
			, posts.post_title as title
			, posts.post_modified_gmt as modified_dt_gmt
			, posts.post_date_gmt as created_dt_gmt
			, posts.post_author as user_id_author

			FROM {$wpdb->prefix}posts posts

			WHERE posts.`post_type` = 'kanban_project'
			AND posts.`post_status` = 'publish'
			LIMIT 1
		;";

		$projects = $wpdb->get_results( $sql );



		if ( ! empty( $projects ) )
		{
			foreach ( $projects as $post )
			{
				$data = array(
					'title'           => $post->title,
					'created_dt_gmt'  => $post->created_dt_gmt,
					'modified_dt_gmt' => $post->modified_dt_gmt,
					'user_id_author'  => $post->user_id_author,
					'is_active'       => 1
				);

				$success = Kanban_Project::replace( $data );

				if ( $success )
				{
					$wpdb->update (
						"{$wpdb->prefix}posts",
						array( 'post_status' => 'trash' ),
						array( 'ID' => $post->id )
					);
				}
			} // projects



			$response['posts_remaining'] = $response['posts_remaining']-3;
			$response['continue'] = TRUE;
			$response['message'] = sprintf( 'project %s moved', $post->id );
			wp_send_json_success( $response );
		} // projects




		// move tasks

		$sql = "SELECT posts.ID as id
			, posts.post_title as title
			, posts.post_modified_gmt as modified_dt_gmt
			, posts.post_date_gmt as created_dt_gmt
			, posts.post_author as user_id_author
			, p_project_name.post_title as project_name
			, pm_user_id_assigned.meta_value as user_id_assigned
			, pm_work_hour_count.meta_value as work_hour_count

			FROM {$wpdb->prefix}posts posts

			JOIN {$wpdb->prefix}postmeta pm_project_id
				ON pm_project_id.post_id = posts.ID
				AND pm_project_id.meta_key = 'kanban_task_project_id'

			JOIN {$wpdb->prefix}posts p_project_name
				ON pm_project_id.meta_value = p_project_name.ID

			JOIN {$wpdb->prefix}postmeta pm_user_id_assigned
				ON pm_user_id_assigned.post_id = posts.ID
				AND pm_user_id_assigned.meta_key = 'kanban_task_user_id_assigned'

			JOIN {$wpdb->prefix}postmeta pm_work_hour_count
				ON pm_work_hour_count.post_id = posts.ID
				AND pm_work_hour_count.meta_key = 'kanban_task_work_hour_count'

			WHERE posts.`post_type` = 'kanban_task'
			AND posts.`post_status` = 'publish'
			LIMIT 1
		;";

		$tasks = $wpdb->get_results( $sql );



		if ( ! empty( $tasks ) )
		{
			// get statuses for matching
			$status_table = Kanban_Status::table_name();
			$sql = "SELECT * FROM $status_table;";
			$statuses = $wpdb->get_results( $sql );

			$status_arr = array();
			foreach ( $statuses as $status )
			{
				$status_arr[$status->title] = $status->id;
			}



			// get estimates for matching
			$estimates_table = Kanban_Estimate::table_name();
			$sql = "SELECT * FROM $estimates_table;";
			$estimates = $wpdb->get_results( $sql );


			$estimate_arr = array();
			foreach ( $estimates as $estimate )
			{
				$estimate_arr[$estimate->title] = $estimate->id;
			}



			// get projects for matching
			$projects_table = Kanban_Project::table_name();
			$sql = "SELECT * FROM $projects_table;";
			$projects = $wpdb->get_results( $sql );

			// build look up array by name
			$projects_arr = array();

			if ( ! empty( $projects ) )
			{
				foreach ( $projects as $project )
				{
					$projects_arr[$project->title] = $project->id;
				}
			}



			$estimate_arr = array();
			foreach ( $estimates as $estimate )
			{
				$estimate_arr[$estimate->title] = $estimate->id;
			}



			foreach ( $tasks as $post )
			{
				// get terms for this task
				$sql = "SELECT
						{$wpdb->prefix}terms.name
						, {$wpdb->prefix}term_taxonomy.`taxonomy`
						FROM {$wpdb->prefix}terms
						JOIN {$wpdb->prefix}term_taxonomy
						ON {$wpdb->prefix}terms.term_id = {$wpdb->prefix}term_taxonomy.term_id
						JOIN {$wpdb->prefix}term_relationships
						ON {$wpdb->prefix}term_taxonomy.term_taxonomy_id = {$wpdb->prefix}term_relationships.term_taxonomy_id
						WHERE {$wpdb->prefix}term_relationships.object_id = %s
				;";

				$terms = $wpdb->get_results(
					$wpdb->prepare(
						$sql,
						$post->id
					)
				);

				$terms_arr = array();
				foreach ( $terms as $term_data )
				{
					$terms_arr[$term_data->taxonomy] = $term_data->name;
				}



				if ( isset( $terms_arr['kanban_task_status'] ) && isset( $status_arr[$terms_arr['kanban_task_status']] ) )
				{
					$status_id = $status_arr[$terms_arr['kanban_task_status']];
				}
				else
				{
					$status_id = 0;
				}



				if ( isset( $terms_arr['kanban_task_estimate'] ) && isset( $estimate_arr[$terms_arr['kanban_task_estimate']] ) )
				{
					$estimate_id = $estimate_arr[$terms_arr['kanban_task_estimate']];
				}
				else
				{
					$estimate_id = 0;
				}



				if ( isset( $projects_arr[$post->project_name] ) )
				{
					$project_id = $projects_arr[$post->project_name];
				}
				else
				{
					$project_id = 0;
				}



				// build task data to save
				$data = array(
					'title'            => $post->title,
					'created_dt_gmt'   => $post->created_dt_gmt,
					'modified_dt_gmt'  => $post->modified_dt_gmt,
					'user_id_author'   => $post->user_id_author,
					'user_id_assigned' => $post->user_id_assigned,
					'status_id'        => $status_id,
					'project_id'       => $project_id,
					'estimate_id'      => $estimate_id,
					'is_active'        => 1
				);

				$success = Kanban_Task::replace( $data );



				$task_id = self::_insert_id();



				if ( $success )
				{
					$wpdb->update (
						"{$wpdb->prefix}posts",
						array( 'post_status' => 'trash' ),
						array( 'ID' => $post->id )
					);
				}



				$response['message'] = sprintf( 'task %s moved', $post->id );



				// get comments for task
				$sql = "SELECT *
						FROM {$wpdb->prefix}posts posts
						WHERE posts.`post_type` = 'kanban_task_comment'
						AND posts.`post_parent` = {$post->id}
						AND posts.`post_status` = 'publish'
						;";

				$comments = $wpdb->get_results( $sql );



				if ( count( $comments ) > 0 )
				{
					foreach ( $comments as $comment )
					{
						$data = array(
							'description'     => $comment->post_content,
							'created_dt_gmt'  => $comment->post_date_gmt,
							'modified_dt_gmt' => $comment->post_modified_gmt,
							'comment_type'    => 'system',
							'task_id'         => $task_id,
							'user_id_author'  => $comment->post_author
						);

						$success = Kanban_Comment::insert( $data );

						// mark as trash
						if ( $success )
						{
							$wpdb->update (
								"{$wpdb->prefix}posts",
								array( 'post_status' => 'trash' ),
								array( 'ID' => $comment->ID )
							);
						}

						// add task hour
						if ( strpos( $comment->post_content, 'hour of work' ) !== FALSE )
						{
							$data = array(
								'task_id'        => $task_id,
								'created_dt_gmt' => $comment->post_date_gmt,
								'hours'          => 1,
								'status_id'      => $status_id,
								'user_id_author' => $comment->post_author,
								'user_id_worked' => $comment->post_author
							);

							$success = Kanban_Task_Hour::insert( $data );
						}

					}

					$response['message'] .= sprintf( '. %s comments moved', count( $comments ) );
				} // $comments



			} // $tasks



			$response['posts_remaining'] = Kanban::get_instance()->settings->records_to_move - 4;
			$response['continue'] = TRUE;
			wp_send_json_success( $response );

		} // task



		// $is_posts_deleted = get_option('kanban_migrate_posts_deleted');


		// if ( !$is_posts_deleted )
		// {
		// 	// delete any orphaned posts
		// 	$success = $wpdb->query("DELETE FROM $wpdb->posts
		// 		WHERE post_type LIKE 'kanban%'
		// 		OR post_type LIKE 'kbwp%'
		// 		;"
		// 	);

		// 	update_option('kanban_migrate_posts_deleted', TRUE);


		// 	$response['posts_remaining'] = Kanban::get_instance()->settings->records_to_move;
		// 	$response['continue'] = TRUE;
		// 	$response['message'] = sprintf('%s posts cleaned up', count($success));
		// 	wp_send_json_success($response);
		// }



		// // delete terms
		// $sql = "SELECT
		// 		{$wpdb->prefix}term_taxonomy.`taxonomy`
		// 		, {$wpdb->prefix}term_taxonomy.`term_taxonomy_id`
		// 		, {$wpdb->prefix}terms.`term_id`
		// 		, {$wpdb->prefix}terms.`name`
		// 		, {$wpdb->prefix}terms.`slug`
		// 		FROM {$wpdb->prefix}terms
		// 		JOIN {$wpdb->prefix}term_taxonomy
		// 		ON {$wpdb->prefix}terms.`term_id` = {$wpdb->prefix}term_taxonomy.`term_id`
		// ;";

		// $terms = $wpdb->get_results($sql);



		// // if we need to delete terms
		// if ( !empty($terms) )
		// {
		// 	// add each term
		// 	foreach ($terms as $term)
		// 	{
		// 		$wpdb->delete(
		// 			sprintf('%sterms', $wpdb->prefix),
		// 			array('term_id' => $term->term_id)
		// 		);

		// 		$wpdb->delete(
		// 			sprintf('%sterm_relationships', $wpdb->prefix),
		// 			array('term_taxonomy_id' => $term->term_taxonomy_id)
		// 		);

		// 		$wpdb->delete(
		// 			sprintf('%sterm_taxonomy', $wpdb->prefix),
		// 			array('term_taxonomy_id' => $term->term_taxonomy_id)
		// 		);
		// 	}

		// 	$response['continue'] = TRUE;
		// 	$response['message'] = 'statuses and estimates removed';
		// 	wp_send_json_success($response);
		// }



		// cleanup records
		$wpdb->update (
			"{$wpdb->prefix}posts",
			array( 'post_status' => 'trash' ),
			array(
				'post_status' => 'publish',
				'post_type'   => 'kanban_task'
			)
		);

		$wpdb->update (
			"{$wpdb->prefix}posts",
			array( 'post_status' => 'trash' ),
			array(
				'post_status' => 'publish',
				'post_type'   => 'kanban_project'
			)
		);

		$wpdb->update (
			"{$wpdb->prefix}posts",
			array( 'post_status' => 'trash' ),
			array(
				'post_status' => 'publish',
				'post_type'   => 'kanban_task_comment'
			)
		);



		// clean up temp values
		delete_option( 'kanban_migrate_users_moved' );
		delete_option( 'kanban_migrate_terms_moved' );
		delete_option( 'kanban_migrate_posts_deleted' );

		// clean up old data
		// delete_option('kanban_task_status_order');
		// delete_option('kanban_task_status_colors');
		// delete_option('kanban_task_estimate_order');



		Kanban_Db::add_defaults();



		$response['done'] = TRUE;
		wp_send_json_success( $response );
	}



	static function add_defaults()
	{
		global $wpdb;

		$status_table = Kanban_Status::table_name();

		$sql = "SELECT count(`id`)
				FROM `{$status_table}`
		;";

		$status_count = $wpdb->get_var( $sql );



		if ( $status_count == 0 )
		{
			$statuses = array(
				'Backlog'     => '#8224e3',
				'Ready'       => '#eeee22',
				'In progress' => '#81d742',
				'QA'          => '#f7a738',
				'Done'        => '#1e73be',
				'Archive'     => '#333333'
			);

			$i = 0;
			foreach ( $statuses as $status => $color )
			{

				$data = array(
					'title'     => $status,
					'color_hex' => $color,
					'position'  => $i
				);

				Kanban_Status::replace( $data );

				$i++;
			}

		}



		$estimate_table = Kanban_Estimate::table_name();

		$sql = "SELECT count(`id`)
				FROM `{$estimate_table}`
		;";

		$estimate_count = $wpdb->get_var( $sql );



		if ( $estimate_count == 0 )
		{
			$estimates = array(
					'2'  => '2h',
					'4'  => '4h',
					'8'  => '1d',
					'16' => '2d',
					'32' => '4d'
				);

			$i = 0;
			foreach ( $estimates as $hours => $title )
			{
				$data = array(
					'title'    => $title,
					'hours'    => $hours,
					'position' => $i
				);

				Kanban_Estimate::replace( $data );

				$i++;
			}
		}



		$boards_table = Kanban_Board::table_name();

		$sql = "SELECT count(`id`)
				FROM `{$boards_table}`
		;";

		$boards_count = $wpdb->get_var( $sql );



		if ( $boards_count == 0 )
		{
			$data = array(
				'title'           => 'Your first kanban board',
				'created_dt_gmt'  => Kanban_Utils::mysql_now_gmt(),
				'modified_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
				'user_id_author'  => get_current_user_id(),
				'is_active'       => 1
			);

			Kanban_Board::replace( $data );
		}



		$tasks_table = Kanban_Board::table_name();

		$sql = "SELECT count(`id`)
				FROM `{$tasks_table}`
		;";

		$tasks_count = $wpdb->get_var( $sql );



		if ( $tasks_count == 0 )
		{
			$sql = "SELECT `id`
					FROM `{$boards_table}`
					LIMIT 1
			;";

			$board_id = $wpdb->get_var( $sql );

			$data = array(
				'title'           => 'Your first task',
				'board_id'        => $board_id,
				'created_dt_gmt'  => Kanban_Utils::mysql_now_gmt(),
				'modified_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
				'user_id_author'  => get_current_user_id(),
				'is_active'       => 1
			);

			Kanban_Board::replace( $data );
		}



		$options_table = Kanban_Option::table_name();

		$sql = "SELECT *
				FROM `{$options_table}`
		;";

		$options = $wpdb->get_results( $sql );

		$options_arr = array();
		foreach ( $options as $option )
		{
			$options_arr[$option->name] = $option->value;
		}



		$defaults = Kanban_Option::get_defaults();

		foreach ( $defaults as $name => $value )
		{
			if ( isset( $options_arr[$name] ) ) continue;

			$data = array(
				'name'  => $name,
				'value' => $value
			);

			Kanban_Option::replace( $data );
		}

		return true;
	}


	/**
	 * get the stored db version
	 * @return float the current stored version
	 */
	static function installed_ver()
	{
		// if it hasn't been loaded yet
		if ( ! isset( self::$installed_ver ) )
		{
			// get it from the db, and store it
			self::$installed_ver = get_option( 'kanban_db_version' );
		}

		// return the stored db version
		return (float) self::$installed_ver;
	}



	/**
	 * build the table name with "namespacing"
	 * @param  string $table the classname for the table
	 * @return string        the complete table name
	 */
	static function format_table_name( $table )
	{
		global $wpdb;

		return sprintf(
			'%s%s_%s',
			$wpdb->prefix,
			Kanban::get_instance()->settings->basename,
			$table
		);
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

}
