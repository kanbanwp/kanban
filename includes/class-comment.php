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
		'log_type' => 'text',
		'description' => 'text',
		'user_id_author' => 'int'
	);


	static function init()
	{
	}



	static function add ($comment, $type, $task_id = 0, $user_id_author = NULL)
	{
		if ( !$user_id_author )
		{
			$user_id_author = get_current_user_id();
		}



		$data = array(
			'description' => $comment,
			'log_type' => $type,
			'task_id' => $task_id,
			'user_id_author' => $user_id_author
		);

		$id = self::insert($data);

		// $post_data = self::get_row('id', $id);
	}



	static function db_table ()
	{
		return "CREATE TABLE " . parent::table_name() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					task_id bigint(20) NOT NULL,
					log_type varchar(64) NOT NULL,
					description text NOT NULL,
					user_id_author bigint(20) NOT NULL,
					PRIMARY KEY  (id)
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


