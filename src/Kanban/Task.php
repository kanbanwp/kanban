<?php



// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Class Kanban_Task
 */
class Kanban_Task extends Kanban_Db {

	/**
	 * The common name for this class.
	 *
	 * @var string
	 */
	static $slug = 'task';

	/**
	 * The table name of this class.
	 *
	 * @var string
	 */
	protected static $table_name = 'tasks';

	/**
	 * Define db table columns and their validation type.
	 *
	 * @var array
	 */
	protected static $table_columns = array(
		'title'            => 'text',
		// 'description'      => 'text',
		'created_dt_gmt'   => 'datetime',
		'modified_dt_gmt'  => 'datetime',
		'modified_user_id' => 'int',
		'position'         => 'int',
		'user_id_author'   => 'int',
		'user_id_assigned' => 'int',
		'status_id'        => 'int',
		'project_id'       => 'int',
		'estimate_id'      => 'int',
		'board_id'         => 'int',
		'is_active'        => 'bool',
	);

	/**
	 * Raw db records.
	 *
	 * @var array
	 */
	protected static $records = array();

	/**
	 * Db records by board.
	 *
	 * @var array
	 */
	protected static $records_by_board = array();



	/**
	 * Set up this class.
	 */
	static function init() {
		add_action( sprintf( 'wp_ajax_save_%s', self::$slug ), array( __CLASS__, 'ajax_save' ) );
		add_action( sprintf( 'wp_ajax_copy_%s', self::$slug ), array( __CLASS__, 'ajax_copy' ) );
		add_action( sprintf( 'wp_ajax_delete_%s', self::$slug ), array( __CLASS__, 'ajax_delete' ) );
		add_action( sprintf( 'wp_ajax_undelete_%s', self::$slug ), array( __CLASS__, 'ajax_undelete' ) );
		add_action( sprintf( 'wp_ajax_updates_%s', self::$slug ), array( __CLASS__, 'ajax_get_updates' ) );

		add_action( 'kanban_task_auto_archive', array( __CLASS__, 'auto_archive' ), 10, 1 );
	}



	/**
	 * @param int $hours Number of hours.
	 *
	 * @return string Hours in friendly english.
	 */
	static function format_hours( $hours ) {
		if ( $hours < 0 ) {
			$hours = 0;
		}

		if ( $hours < 1 ) {
			$label = sprintf( __( '%sm', 'kanban' ), ceil( $hours * 60 / 100 ) );
		} elseif ( $hours < 8 ) {
			$label = sprintf( __( '%sh', 'kanban' ), $hours );
		} else {
			$label = sprintf( __( '%sd %sh', 'kanban' ), floor( $hours / 8 ), $hours % 8 );
		}

		return apply_filters(
			'kanban_board_format_hours_return',
			$label,
			$hours
		);
	}



	/**
	 * Handle the Ajax call for creating or saving a task.
	 */
	static function ajax_save() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-save' ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		if ( ! Kanban_User::current_user_has_cap( 'write' ) ) {
			wp_send_json_error();
		}

		if ( ! isset( $_POST['task']['id'] ) ) {
			$_POST['task']['id']             = '';
			$_POST['task']['created_dt_gmt'] = Kanban_Utils::mysql_now_gmt();
		}

		do_action( 'kanban_task_ajax_save_before', $_POST['task']['id'] );

		$_POST['task']['modified_dt_gmt']  = Kanban_Utils::mysql_now_gmt();
		$_POST['task']['modified_user_id'] = get_current_user_id();

		if ( ! isset( $_POST['task']['user_id_author'] ) ) {
			$_POST['task']['user_id_author'] = get_current_user_id();
		}

		// Store status change.
		if ( isset( $_POST['task']['id'] ) && ! empty( $_POST['task']['id'] ) && $_POST['task']['status_id'] != self::get_one( $_POST['task']['id'] )->status_id ) {
			do_action( 'kanban_task_ajax_save_before_status_change' );

			Kanban_Status_Change::add(
				$_POST['task']['id'],
				$_POST['task']['status_id'],
				self::get_one( $_POST['task']['id'] )->status_id
			);

			do_action( 'kanban_task_ajax_save_after_status_change' );
		}

		// Save the changes.
		$is_successful = self::_replace( $_POST['task'] );

		$task_id = ! empty( $_POST['task']['id'] ) ? $_POST['task']['id'] : self::_insert_id();

		$post_data = self::get_one( $task_id );

		if ( ! $post_data ) {
			wp_send_json_error();
		}

		do_action( 'kanban_task_ajax_save_after', $post_data );

		if ( ! empty( $_POST['comment'] ) ) {
			do_action( 'kanban_task_ajax_save_before_comment' );

			Kanban_Comment::add(
				$_POST['comment'],
				'system',
				$task_id
			);

			do_action( 'kanban_task_ajax_save_after_comment' );
		}

		$do_message = isset( $_POST['message'] ) && $_POST['message'] == 'true' ? true : false;

		if ( $is_successful ) {
			wp_send_json_success( array(
				'message'   => $do_message ? sprintf( __( '%s saved', 'kanban' ), self::$slug ) : '',
				self::$slug => $post_data,
			) );
		} else {
			wp_send_json_error( array(
				'message' => sprintf( __( 'Error saving %s', 'kanban' ), self::$slug ),
			) );
		}
	}


	/**
	 * Handle the Ajax call for copying a task.
	 */
	static function ajax_copy() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-save' ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		if ( ! Kanban_User::current_user_has_cap( 'write' ) ) {
			wp_send_json_error();
		}

		do_action( 'kanban_task_ajax_copy_before', $_POST['task']['id'] );

		$record = self::get_one($_POST['task']['id']);

		if ( !$record ) {
			wp_send_json_error( array(
				'message' => sprintf( __( 'Error copying %s', 'kanban' ), self::$slug ),
			) );
		}

		$record->title .= ' COPY';

		$new_record = self::duplicate(
			$_POST['task']['id'],
			(array) $record
		);

		do_action( 'kanban_task_ajax_copy_after', $new_record );

		if ( ! empty( $_POST['comment'] ) ) {
			do_action( 'kanban_task_ajax_copy_before_comment' );

			Kanban_Comment::add(
				$_POST['comment'],
				'system',
				$_POST['task']['id']
			);

			do_action( 'kanban_task_ajax_copy_after_comment' );
		}

		if ( $new_record ) {
			wp_send_json_success( array(
				'message' => sprintf(
					__( '%s copied', 'kanban' ),
					self::$slug
				),
				'task' => $new_record
			) );
		} else {
			wp_send_json_error( array(
				'message' => sprintf( __( 'Error copying %s', 'kanban' ), self::$slug ),
			) );
		}
	}


	/**
	 * Handle the Ajax call for deleting a task.
	 */
	static function ajax_delete() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-save' ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		if ( ! Kanban_User::current_user_has_cap( 'write' ) ) {
			wp_send_json_error();
		}

		do_action( 'kanban_task_ajax_delete_before', $_POST['task']['id'] );

		$is_successful = self::delete(
			$_POST['task']['id']
		);

		do_action( 'kanban_task_ajax_delete_after', $_POST['task']['id'] );

		if ( ! empty( $_POST['comment'] ) ) {
			do_action( 'kanban_task_ajax_delete_before_comment' );

			Kanban_Comment::add(
				$_POST['comment'],
				'system',
				$_POST['task']['id']
			);

			do_action( 'kanban_task_ajax_delete_after_comment' );
		}

		if ( $is_successful ) {
			wp_send_json_success( array(
				'message' => sprintf(
					__( '%s deleted', 'kanban' ),
					self::$slug
				),
			) );
		} else {
			wp_send_json_error( array(
				'message' => sprintf( __( 'Error deleting %s', 'kanban' ), self::$slug ),
			) );
		}
	}



	/**
	 * Handle the Ajax call for undeleting tasks.
	 */
	static function ajax_undelete() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-save' ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		if ( ! Kanban_User::current_user_has_cap( 'write' ) ) {
			wp_send_json_error();
		}

		do_action( 'kanban_task_ajax_undelete_before', $_POST['task']['id'] );

		$is_successful = self::delete(
			$_POST['task']['id'],
			1 // Undelete.
		);

		do_action( 'kanban_task_ajax_undelete_after', $_POST['task']['id'] );

		if ( ! empty( $_POST['comment'] ) ) {
			do_action( 'kanban_task_ajax_undelete_before_comment' );

			Kanban_Comment::add(
				$_POST['comment'],
				'system',
				$_POST['task']['id']
			);

			do_action( 'kanban_task_ajax_undelete_after_comment' );
		}

		if ( $is_successful ) {
			wp_send_json_success( array(
				'message' => sprintf(
					__( '%s restored', 'kanban' ),
					self::$slug
				),
			) );
		} else {
			wp_send_json_error( array(
				'message' => sprintf( __( 'Error restoring %s', 'kanban' ), self::$slug ),
			) );
		}
	}



	/**
	 * Handle the Ajax call for task updates.
	 */
	static function ajax_get_updates() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-save' ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		global $wpdb;

		$datetime     = new DateTime( $_POST['datetime'] );
		$datetime_str = $datetime->format( 'Y-m-d H:i:s' );

		// Getting active or inactive, in case they were deleted.
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

		$tasks = $wpdb->get_results( $sql );
		$tasks = Kanban_Utils::build_array_with_id_keys( $tasks, 'id' );

		$tasks = apply_filters(
			'kanban_task_get_updates_tasks',
			$tasks
		);

		add_filter(
			'kanban_project_get_all_sql',
			function ( $sql ) use ( $datetime_str ) {
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

		// Use slashes to ensure there are no multiple slashes.
		wp_send_json_success( array(
			'projects' => json_decode( Kanban_Utils::slashes( wp_json_encode( $projects ) ) ),
			'tasks'    => json_decode( Kanban_Utils::slashes( wp_json_encode( $tasks ) ) ),
		) );
		exit;
	}



	/**
	 * Simple replace - create or update a task.
	 *
	 * @param array $data Task data.
	 *
	 * @return bool If the task was updated.
	 */
	static function replace( $data ) {
		return self::_replace( $data );
	}



	/**
	 * Delete, or undelete, a task.
	 *
	 * @param int  $task_id The id of the task to delete.
	 * @param int  $is_active Whether to delet eor undelete.
	 * @param null $modified_user_id Who deleted it.
	 *
	 * @return bool If the task was updated.
	 */
	static function delete( $task_id, $is_active = 0, $modified_user_id = null ) {

		if ( is_null( $modified_user_id ) ) {
			$modified_user_id = get_current_user_id();
		}

		// When restoring a task, set modified user to 0, so user who restored it will get the live/sync'd update.
		if ( 1 == $is_active  ) {
			$modified_user_id = 0;
		}

		return self::_update(
			array(
				'is_active'        => $is_active,
				'modified_dt_gmt'  => Kanban_Utils::mysql_now_gmt(),
				'modified_user_id' => $modified_user_id,
			),
			array( 'id' => $task_id )
		);
	}



	/**
	 * Update an existing task.
	 *
	 * @param int   $task_id The id of the task to update.
	 * @param array $data The new task data.
	 *
	 * @return bool If the task was updated.
	 */
	static function update( $task_id, $data ) {
		return self::_update(
			$data,
			array( 'id' => $task_id )
		);
	}



	/**
	 * Get one task.
	 *
	 * @param int $task_id The task to get.
	 *
	 * @return mixed Task record.
	 */
	static function get_one( $task_id ) {
		$records = self::get_all();

		$record = isset( $records[ $task_id ] ) ? $records[ $task_id ] : null;

		return apply_filters( 'kanban_task_get_one_return', $record );
	}



	/**
	 * Get all tasks, or tasks by board.
	 *
	 * @param int $board_id The board to get the tasks for.
	 *
	 * @return mixed Task records.
	 */
	static function get_all( $board_id = null ) {
		if ( empty( self::$records ) ) {
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
			self::$records = $wpdb->get_results( $sql );

			self::$records = Kanban_Utils::build_array_with_id_keys( self::$records, 'id' );

			self::$records = apply_filters(
				'kanban_task_get_all_records',
				self::$records
			);

			$boards                 = Kanban_Board::get_all();
			self::$records_by_board = array_fill_keys( array_keys( $boards ), array() );

			foreach ( self::$records as $task_id => $record ) {
				if ( ! isset( self::$records_by_board[ $record->board_id ] ) ) {
					continue;
				}

				self::$records_by_board[ $record->board_id ][ $task_id ] = $record;
			}

			self::$records_by_board = apply_filters(
				'kanban_task_get_all_records_by_board',
				self::$records_by_board
			);
		}

		if ( is_null( $board_id ) ) {
			return apply_filters(
				'kanban_task_get_all_return',
				self::$records
			);
		}

		return apply_filters(
			'kanban_task_get_all_return',
			isset( self::$records_by_board[ $board_id ] ) ? self::$records_by_board[ $board_id ] : array()
		);
	}



	/**
	 * Duplicate an existing task.
	 *
	 * @param int   $task_id The task to dupe.
	 * @param array $data Any data that should differ for the duplicated task.
	 *
	 * @return bool Success.
	 */
	static function duplicate( $task_id, $data = array() ) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
		// Reset.
		unset( $data['id'] );
		$data['created_dt_gmt']  = Kanban_Utils::mysql_now_gmt();
		$data['modified_dt_gmt'] = Kanban_Utils::mysql_now_gmt();
		$data['modified_user_id'] = 0; // So task is shown on sync.

		// Insert new task.
		$is_success = self::_duplicate( $task_id, $data );

		if ( ! $is_success ) {
			return false;
		}

		// Get new id to set in meta.
		$new_task_id = self::_insert_id();

		$taskmeta_table = Kanban_Taskmeta::table_name();

		global $wpdb;

		// Get metarecords for original task to dupe.
		$meta = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM {$taskmeta_table} WHERE task_id = %d",
				$task_id
			)
		);

		// Build data for meta, set the new task id.
		$data = array(
			'task_id'        => $new_task_id,
			'created_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
		);

		// Dupe the meta.
		foreach ( $meta as $meta_id ) {
			Kanban_Taskmeta::duplicate( $meta_id, $data );
		}

		self::$records = array();
		self::get_all();

		$new_record = self::get_one($new_task_id);

		return $new_record;
	}



	/**
	 * Schedule task to auto delete using cron.
	 *
	 * @param int $id The task Id.
	 */
	function auto_archive( $id ) {
		// No idea why i can't link to delete directly.
		self::delete( $id );
	}



	/**
	 * Define the db schema.
	 *
	 * @return string Dbdelta SQL statement.
	 */
	static function db_table() {
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					title text NOT NULL,
					description text NOT NULL,
					position bigint(20) NOT NULL,
					created_dt_gmt datetime NOT NULL,
					modified_dt_gmt datetime NOT NULL,
					modified_user_id bigint(20) NOT NULL,
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



} // Kanban_Task
