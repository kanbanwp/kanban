<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Task_Hour::init();



class Kanban_Task_Hour extends Kanban_Db
{
	private static $instance;
	static $slug = 'task_hour';
	protected static $table_name = 'task_hours';
	protected static $table_columns = array(
		'task_id' => 'int',
		'created_dt_gmt' => 'datetime',
		'hours' => 'float',
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

		eval(sprintf('$hours = 0%s;', $_POST['operator']));

		$data = array(
			'task_id' => $_POST['task']['id'],
			'worked_dt_gmt' => gmdate('Y-m-d H:i:s'),
			'hours' => $hours,
			'user_id_author' => $user_id_author,
			'user_id_worked' => $_POST['user_id_worked']
		);

		$id = self::insert($data);



		if ( !$id ) wp_send_json_error();



		do_action( sprintf('%s_after_%s_ajax_save', Kanban::get_instance()->settings->basename, self::$slug) );



		wp_send_json_success(array(
			'message' => sprintf('%s saved', self::$slug)
		));
	}




	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					task_id bigint(20) NOT NULL,
					created_dt_gmt datetime NOT NULL,
					hours decimal(6, 4) NOT NULL,
					user_id_author bigint(20) NOT NULL,
					user_id_worked bigint(20) NOT NULL,
					PRIMARY KEY  (id)
				)";
	} // db_table



	static function table_name()
	{
		return Kanban_Db::format_table_name(self::$table_name);
	}



	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

}


