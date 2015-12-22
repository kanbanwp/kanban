<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Task_Hour::init();



class Kanban_Task_Hour extends Kanban_Db
{
	// the instance of this object
	private static $instance;

	// the common name for this class
	static $slug = 'task_hour';

	// the table name of this class
	protected static $table_name = 'task_hours';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'task_id' => 'int',
		'created_dt_gmt' => 'datetime',
		'hours' => 'float',
		'status_id' => 'int',
		'user_id_author' => 'int',
		'user_id_worked' => 'int'
	);



	static function init()
	{
		add_action( sprintf('wp_ajax_add_%s', self::$slug), array(__CLASS__, 'ajax_save') );
	}



	static function ajax_save ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::get_instance()->settings->basename)) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_save', Kanban::get_instance()->settings->basename, self::$slug) );



		$user_id_author = isset($_POST['user_id_author']) ? $_POST['user_id_author'] : get_current_user_id();



		if ( empty($_POST['user_id_worked']) )
		{
			$_POST['user_id_worked'] = $user_id_author;
		}



		try
		{
			$operator = substr($_POST['operator'], 0, 1) == '-' ? '-' : '+';
			$val = sprintf('%s%s', $operator, abs(floatval($_POST['operator'])));
		}
		catch (Exception $e)
		{
			wp_send_json_error(array(
				'message' => sprintf('Error saving %s', str_replace('_', ' ', self::$slug))
			));
		}



		eval(sprintf('$hours = 0%s;', $val));



		$data = array(
			'task_id' => $_POST['task']['id'],
			'worked_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
			'hours' => $hours,
			'status_is' => $_POST['task']['status_id'],
			'user_id_author' => $user_id_author,
			'user_id_worked' => $_POST['user_id_worked']
		);

		$is_successful = self::_insert($data);



		do_action( sprintf('%s_after_%s_ajax_save', Kanban::get_instance()->settings->basename, self::$slug) );



		if ( !empty($_POST['comment']) )
		{
			do_action( sprintf('%s_before_%s_ajax_comment_save', Kanban::get_instance()->settings->basename, self::$slug) );

			Kanban_Comment::add(
				$_POST['comment'],
				'system',
				$_POST['task']['id']
			);

			do_action( sprintf('%s_after_%s_ajax_comment_save', Kanban::get_instance()->settings->basename, self::$slug) );
		}



		if ( $is_successful )
		{
			wp_send_json_success(array(
				'message' => sprintf('%s saved', str_replace('_', ' ', self::$slug))
			));
		}
		else
		{
			wp_send_json_error(array(
				'message' => sprintf('Error saving %s', str_replace('_', ' ', self::$slug))
			));
		}
	}



	// extend parent, so it's accessible from other classes
	static function insert ($data)
	{
		return self::_insert($data);
	}



	// define the db schema
	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					task_id bigint(20) NOT NULL,
					created_dt_gmt datetime NOT NULL,
					hours decimal(6, 4) NOT NULL,
					status_id bigint(20) NOT NULL,
					user_id_author bigint(20) NOT NULL,
					user_id_worked bigint(20) NOT NULL,
					UNIQUE KEY  (id)
				)";
	} // db_table



	/**
	 * get the instance of this class
	 * @return	object	the instance
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


