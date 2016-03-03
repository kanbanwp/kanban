<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Task::init();



class Kanban_Task extends Kanban_Db
{
	// the instance of this object
	private static $instance;

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
		'user_id_author'   => 'int',
		'user_id_assigned' => 'int',
		'status_id'        => 'int',
		'project_id'       => 'int',
		'estimate_id'      => 'int',
		'board_id'         => 'int',
		'is_active'        => 'bool'
	);



	static function init()
	{
		add_action( sprintf( 'wp_ajax_save_%s', self::$slug ), array( __CLASS__, 'ajax_save' ) );
		add_action( sprintf( 'wp_ajax_delete_%s', self::$slug ), array( __CLASS__, 'ajax_delete' ) );
	}



	static function format_hours( $hours )
	{
		if ( $hours < 0 )
		{
			$hours = 0;
		}

		if ( $hours < 1 )
		{
			$label = sprintf( '%sm', ceil( $hours*60/100));
		}
		elseif ( $hours < 8 )
		{
			$label = sprintf( '%sh', $hours );
		}
		else
		{
			$label = sprintf( '%sd %sh', floor( $hours/8 ), $hours % 8 );
		}

		return $label;
	}



	static function ajax_save()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-save' ) || ! is_user_logged_in() ) wp_send_json_error();



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



		$is_successful = self::_replace( $_POST['task'] );



		$task_id = ! empty( $_POST['task']['id'] ) ? $_POST['task']['id'] : self::_insert_id();



		$post_data = self::get_row( 'id', $task_id );

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



		if ( isset( $_POST['status_id_old'] ) )
		{
			do_action( 'kanban_task_ajax_save_before_status_change' );

			Kanban_Status_Change::add(
				$task_id,
				$post_data->status_id,
				$_POST['status_id_old']
			);

			do_action( 'kanban_task_ajax_save_after_status_change' );
		}



		if ( $is_successful )
		{
			wp_send_json_success( array(
				'message' => sprintf( '%s saved', self::$slug ),
				self::$slug => $post_data
			) );
		}
		else
		{
			wp_send_json_error( array(
				'message' => sprintf( 'Error saving %s', self::$slug )
			) );
		}
	}



	static function ajax_delete()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-save' ) || ! is_user_logged_in() ) wp_send_json_error();



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
				'message' => sprintf( '%s deleted', self::$slug )
			) );
		}
		else
		{
			wp_send_json_error( array(
				'message' => sprintf( 'Error deleting %s', self::$slug )
			) );
		}
	}



	// extend parent, so it's accessible from other classes
	static function replace( $data )
	{
		return self::_replace( $data );
	}



	// extend parent, so it's accessible from other classes
	protected static function delete( $id )
	{
		return self::_update(
			array( 'is_active' => 0 ),
			array( 'id' => $id )
		);
	}


	static function get_one( $task_id )
	{
		$record = parent::get_row( 'ID', $task_id );
		$record->title = Kanban_Utils::str_for_frontend( $record->title );
		$record->description = Kanban_Utils::str_for_frontend( $record->description );

		return $record;
	}



	static function get_all( $sql = NULL )
	{
		$table_name = self::table_name();

		$worked_table_name = Kanban_Task_Hour::table_name();




		$sql = "SELECT tasks.*,
				COALESCE(SUM(worked.hours), 0) 'hour_count'
				FROM {$table_name} tasks
				LEFT JOIN {$worked_table_name} worked
				ON tasks.id = worked.task_id
				WHERE tasks.is_active = 1
				GROUP BY tasks.id
		;";

		$sql = apply_filters( 'kanban_task_get_all_sql', $sql );

		$records = parent::get_all( $sql );



		foreach ( $records as $key => $record )
		{
			$records[$key]->title = Kanban_Utils::str_for_frontend( $records[$key]->title );
			$records[$key]->description = Kanban_Utils::str_for_frontend( $records[$key]->description );
		}



		return apply_filters(
			'kanban_task_get_all_return',
			Kanban_Utils::build_array_with_id_keys ( $records, 'id' )
		);
	}



	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					title text NOT NULL,
					description text NOT NULL,
					created_dt_gmt datetime NOT NULL,
					modified_dt_gmt datetime NOT NULL,
					user_id_author bigint(20) NOT NULL,
					user_id_assigned bigint(20) NOT NULL,
					status_id bigint(20) NOT NULL,
					project_id bigint(20) NOT NULL,
					estimate_id bigint(20) NOT NULL,
					board_id bigint(20) NOT NULL,
					is_active BOOLEAN NOT NULL DEFAULT TRUE,
					UNIQUE KEY  (id),
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
