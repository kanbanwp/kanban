<?php

/**
 * class for handling stored comments, both system and user
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Kanban_Comment::init();



class Kanban_Comment extends Kanban_Db
{
	// the instance of this object
	private static $instance;

	// the common name for this class
	static $slug = 'comment';

	// the table name of this class
	protected static $table_name = 'log_comments';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'task_id'         => 'int',
		'created_dt_gmt'  => 'datetime',
		'modified_dt_gmt' => 'datetime',
		'comment_type'    => 'text',
		'description'     => 'text',
		'user_id_author'  => 'int'
	);



	static function init()
	{
	}



	static function add( $comment, $type = 'system', $task_id = 0, $user_id_author = NULL )
	{


		do_action( 'kanban_comment_add_before' );




		if ( ! $user_id_author )
		{
			$user_id_author = get_current_user_id();
		}



		$data = array(
			'description'     => $comment,
			'comment_type'    => $type,
			'task_id'         => $task_id,
			'user_id_author'  => $user_id_author,
			'created_dt_gmt'  => Kanban_Utils::mysql_now_gmt(),
			'modified_dt_gmt' => Kanban_Utils::mysql_now_gmt()
		);

		$success = self::_insert( $data );



		do_action( 'kanban_comment_add_after', $success, $data );




		return $success;
	}



	// extend parent, so it's accessible from other classes
	static function insert( $data )
	{
		return self::_insert( $data );
	}



	static function get_all( $sql = NULL )
	{
		$table_name = self::table_name();

		$sql = "SELECT *
				FROM `{$table_name}`
		;";

		$sql = apply_filters( 'kanban_comment_get_all_sql', $sql );

		$records = parent::get_all( $sql );

		return apply_filters(
			'kanban_comment_get_all_return',
			Kanban_Utils::build_array_with_id_keys ( $records, 'id' )
		);
	}




	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					created_dt_gmt datetime NOT NULL,
					modified_dt_gmt datetime NOT NULL,
					task_id bigint(20) NOT NULL,
					comment_type varchar(64) NOT NULL,
					description text NOT NULL,
					user_id_author bigint(20) NOT NULL,
					UNIQUE KEY  (id)
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

}
