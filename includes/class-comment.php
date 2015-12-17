<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Kanban_Comment::init();



class Kanban_Comment extends Kanban_Db
{
	private static $instance;
	protected static $table_name = 'log_comments';
	protected static $table_columns = array(
		'task_id' => 'int',
		'created_dt_gmt' => 'datetime',
		'modified_dt_gmt' => 'datetime',
		'comment_type' => 'text',
		'description' => 'text',
		'user_id_author' => 'int'
	);


	static function init()
	{
	}



	static function add ($comment, $type = 'system', $task_id = 0, $user_id_author = NULL)
	{
		if ( !$user_id_author )
		{
			$user_id_author = get_current_user_id();
		}



		$data = array(
			'description' => $comment,
			'comment_type' => $type,
			'task_id' => $task_id,
			'user_id_author' => $user_id_author
		);

		$success = self::_insert($data);

		return $success;
	}



	static function insert ($data)
	{
		return self::_insert($data);
	}



	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					created_dt_gmt datetime NOT NULL,
					modified_dt_gmt datetime NOT NULL,
					task_id bigint(20) NOT NULL,
					comment_type varchar(64) NOT NULL,
					description text NOT NULL,
					user_id_author bigint(20) NOT NULL,
					UNIQUE KEY  (id)
				)";
	} // db_table



	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

}


