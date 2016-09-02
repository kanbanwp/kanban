<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



//Kanban_Task::init();



class Kanban_Task extends Kanban_Db
{
	// the instance of this object
	// private static $instance;

	// the common name for this class
	static $slug = 'task';

	// the table name of this class
	protected static $table_name = 'tasks';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'title'            => 'text',
		'description'      => 'text',
		'created_dt_gmt'   => 'datetime',
		'modified_dt_gmt'  => 'datetime',
		'position'	       => 'int',
		'user_id_author'   => 'int',
		'user_id_assigned' => 'int',
		'status_id'        => 'int',
		'project_id'       => 'int',
		'estimate_id'      => 'int',
		'board_id'         => 'int',
		'is_active'        => 'bool'
	);

	protected static $records = array();
	protected static $records_by_board = array();



	static function init()
	{
		add_action( sprintf( 'wp_ajax_save_%s', self::$slug ), array( __CLASS__, 'ajax_save' ) );
		add_action( sprintf( 'wp_ajax_delete_%s', self::$slug ), array( __CLASS__, 'ajax_delete' ) );
		add_action( sprintf( 'wp_ajax_updates_%s', self::$slug ), array( __CLASS__, 'ajax_get_updates' ) );

		add_action( 'kanban_task_auto_archive', array( __CLASS__, 'auto_archive'), 10, 1 );
	}



	static function format_hours( $hours )
	{
		if ( $hours < 0 )
		{
			$hours = 0;
		}

		if ( $hours < 1 )
		{
			$label = sprintf( __('%sm', 'kanban'), ceil( $hours*60/100));
		}
		elseif ( $hours < 8 )
		{
			$label = sprintf( __('%sh', 'kanban'), $hours );
		}
		else
		{
			$label = sprintf( __('%sd %sh', 'kanban'), floor( $hours/8 ), $hours % 8 );
		}

		return $label;
	}



	static function ajax_save()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-save' ) || ! is_user_logged_in() ) wp_send_json_error();



		if ( !Kanban_User::current_user_has_cap ('write') )
		{
			wp_send_json_error();
		}



		if ( ! isset( $_POST['task']['id'] ) )
		{
			$_POST['task']['id'] = '';
			$_POST['task']['created_dt_gmt'] = Kanban_Utils::mysql_now_gmt();
		}



		do_action( 'kanban_task_ajax_save_before', $_POST['task']['id'] );



		$_POST['task']['modified_dt_gmt'] = Kanban_Utils::mysql_now_gmt();



		if ( ! isset( $_POST['task']['user_id_author'] ) )
		{
			$_POST['task']['user_id_author'] = get_current_user_id();
		}



		// store status change
		if ( isset($_POST['task']['id']) && !empty($_POST['task']['id']) && $_POST['task']['status_id'] != self::get_one($_POST['task']['id'])->status_id )
		{
			do_action( 'kanban_task_ajax_save_before_status_change' );

			Kanban_Status_Change::add(
				$_POST['task']['id'],
				$_POST['task']['status_id'],
				self::get_one($_POST['task']['id'])->status_id
			);

			do_action( 'kanban_task_ajax_save_after_status_change' );
		}


		// save the changes!
		$is_successful = self::_replace( $_POST['task'] );



		$task_id = ! empty( $_POST['task']['id'] ) ? $_POST['task']['id'] : self::_insert_id();



		$post_data = self::get_one( $task_id );

		if ( ! $post_data ) wp_send_json_error();



		do_action( 'kanban_task_ajax_save_after', $post_data );



		if ( ! empty( $_POST['comment'] ) )
		{
			do_action( 'kanban_task_ajax_save_before_comment' );

			Kanban_Comment::add(
				$_POST['comment'],
				'system',
				$task_id
			);

			do_action( 'kanban_task_ajax_save_after_comment' );
		}



		$do_message = $_POST['message'] == 'true' ? TRUE : FALSE;



		if ( $is_successful )
		{
			wp_send_json_success( array(
				'message' => $do_message ? sprintf( __('%s saved', 'kanban'), self::$slug ) : '',
				self::$slug => $post_data
			) );
		}
		else
		{
			wp_send_json_error( array(
				'message' => sprintf( __('Error saving %s', 'kanban'), self::$slug )
			) );
		}
	}



	static function ajax_delete()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-save' ) || ! is_user_logged_in() ) wp_send_json_error();



		if ( !Kanban_User::current_user_has_cap ('write') )
		{
			wp_send_json_error();
		}



		do_action( 'kanban_task_ajax_delete_before', $_POST['task']['id'] );



		// $is_successful = Kanban_Post::delete($_POST);
		$is_successful = self::delete( $_POST['task']['id'] );



		do_action( 'kanban_task_ajax_delete_after', $_POST['task']['id'] );



		if ( ! empty( $_POST['comment'] ) )
		{
			do_action( 'kanban_task_ajax_delete_before_comment' );

			Kanban_Comment::add(
				$_POST['comment'],
				'system',
				$_POST['task']['id']
			);

			do_action( 'kanban_task_ajax_delete_after_comment' );
		}



		if ( $is_successful )
		{
			wp_send_json_success( array(
				'message' => sprintf( __('%s deleted', 'kanban'), self::$slug )
			) );
		}
		else
		{
			wp_send_json_error( array(
				'message' => sprintf( __('Error deleting %s', 'kanban'), self::$slug )
			) );
		}
	}



	static function ajax_get_updates ()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-save' ) || ! is_user_logged_in() ) wp_send_json_error();

		global $wpdb;

		$datetime = new DateTime($_POST['datetime']);
		$datetime_str = $datetime->format('Y-m-d H:i:s');


		// getting active or inactive, in case they were deleted 
		$sql = sprintf(
				"SELECT tasks.*,
			  	LPAD(position, 5, '0') as position,
				COALESCE(SUM(worked.hours), 0) 'hour_count'
				FROM `%s` tasks
				LEFT JOIN `%s` worked
				ON tasks.id = worked.task_id
				WHERE TIMESTAMP(tasks.modified_dt_gmt) > TIMESTAMP('$datetime_str')
				AND tasks.title != ''
				GROUP BY tasks.id
				ORDER BY position
				;",
				self::table_name(),
				Kanban_Task_Hour::table_name()
			);

		$sql = apply_filters( 'kanban_task_get_updates_sql', $sql );

		$tasks = $wpdb->get_results($sql);
		$tasks = Kanban_Utils::build_array_with_id_keys( $tasks, 'id' );

		$tasks = apply_filters(
			'kanban_task_get_updates_tasks',
			$tasks
		);



		add_filter(
			'kanban_project_get_all_sql',
			function($sql) use ($datetime_str)
			{
				return sprintf(
					"SELECT `projects`.*
					FROM `%s` projects
					WHERE TIMESTAMP(projects.modified_dt_gmt) > TIMESTAMP('%s')
					;",
					Kanban_Project::table_name(),
					$datetime_str
				);
			}
		);

		$projects = Kanban_Project::get_all();

		$projects = apply_filters(
			'kanban_task_get_updates_projects',
			$projects
		);



		// use slashes to ensure there are no multiple slashes
		wp_send_json_success( array(
			'projects' => json_decode(Kanban_Utils::slashes(json_encode($projects))),
			'tasks' => json_decode(Kanban_Utils::slashes(json_encode($tasks)))
		) );
	}



	// extend parent, so it's accessible from other classes
	static function replace( $data )
	{
		return self::_replace( $data );
	}



	// extend parent, so it's accessible from other classes
	static function delete( $id )
	{
		return self::_update(
			array(
				'is_active' => 0,
				'modified_dt_gmt' => Kanban_Utils::mysql_now_gmt()
			),
			array( 'id' => $id )
		);
	}


	
	static function update( $id, $data )
	{
		return self::_update(
			$data,
			array( 'id' => $id )
		);
	}

	
	
	
	static function get_one( $task_id )
	{
//		$record = self::_get_row( 'ID', $task_id );
//		$record->title = Kanban_Utils::str_for_frontend( $record->title );
//		$record->description = Kanban_Utils::str_for_frontend( $record->description );



		$records = self::get_all();

		$record = isset($records[$task_id]) ? $records[$task_id] : NULL;



		return apply_filters( 'kanban_task_get_one_return', $record );
	}



	static function get_all($board_id = NULL)
	{
		if ( empty( self::$records ) )
		{
			$sql = sprintf(
				"SELECT tasks.*,
			  	LPAD(position, 5, '0') as position,
				COALESCE(SUM(worked.hours), 0) 'hour_count'
				FROM `%s` tasks
				LEFT JOIN `%s` worked
				ON tasks.id = worked.task_id
				WHERE tasks.is_active = 1
				GROUP BY tasks.id
				ORDER BY position
				;",
				self::table_name(),
				Kanban_Task_Hour::table_name()
			);

			$sql = apply_filters( 'kanban_task_get_all_sql', $sql );

			global $wpdb;
			self::$records = $wpdb->get_results($sql);

			self::$records = Kanban_Utils::build_array_with_id_keys( self::$records, 'id' );

			self::$records = apply_filters(
				'kanban_task_get_all_records',
				self::$records
			);

			$boards = Kanban_Board::get_all();
			self::$records_by_board = array_fill_keys(array_keys($boards), array());

			foreach ( self::$records as $task_id => $record )
			{
				if ( !isset(self::$records_by_board[$record->board_id]) ) continue;

				self::$records_by_board[$record->board_id][$task_id] = $record;
			}


			self::$records_by_board = apply_filters(
				'kanban_task_get_all_records_by_board',
				self::$records_by_board
			);
		}

		if ( is_null($board_id) )
		{
			return apply_filters(
				'kanban_task_get_all_return',
				self::$records
			);
		}

		return apply_filters(
			'kanban_task_get_all_return',
			isset(self::$records_by_board[$board_id]) ? self::$records_by_board[$board_id] : array()
		);
	}



	function duplicate ($task_id, $data = array())
    {
		// reset
		unset($data['id']);
		$data['created_dt_gmt'] = Kanban_Utils::mysql_now_gmt();
		$data['modified_dt_gmt'] = Kanban_Utils::mysql_now_gmt();



		// insert new task
		$is_success = self::_duplicate( $task_id, $data );

		if ( !$is_success ) return FALSE;



		// get new id to set in meta
		$new_task_id = self::_insert_id();



		$taskmeta_table = Kanban_Taskmeta::table_name();

		global $wpdb;

		// get metarecords for original task to dupe
		$meta = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM $taskmeta_table WHERE task_id = %d",
				$task_id
			)
		);



		// build data for meta, set the new task id
		$data = array(
			'task_id' => $new_task_id,
			'created_dt_gmt' => Kanban_Utils::mysql_now_gmt()
		);

		// dupe meta
		foreach ( $meta as $meta_id )
		{
			Kanban_Taskmeta::duplicate($meta_id, $data);
		}
    }



	function auto_archive ( $id )
	{
		// no idea why i can't link to delete directly
		self::delete( $id );
	}



	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					title text NOT NULL,
					description text NOT NULL,
					position bigint(20) NOT NULL,
					created_dt_gmt datetime NOT NULL,
					modified_dt_gmt datetime NOT NULL,
					user_id_author bigint(20) NOT NULL,
					user_id_assigned bigint(20) NOT NULL,
					status_id bigint(20) NOT NULL,
					project_id bigint(20) NOT NULL,
					estimate_id bigint(20) NOT NULL,
					board_id bigint(20) NOT NULL DEFAULT 1,
					is_active BOOLEAN NOT NULL DEFAULT TRUE,
					UNIQUE KEY id (id),
					KEY is_active (is_active),
					KEY board_id (board_id)
				)';
	} // db_table



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

} // Kanban_Task
