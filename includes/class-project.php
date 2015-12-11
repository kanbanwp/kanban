<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Project::init();



class Kanban_Project extends Kanban_Db
{
	private static $instance;
	static $slug = 'project';
	protected static $table_name = 'projects';
	protected static $table_columns = array(
		'title' => 'text',
		'description' => 'text',
		'user_id_author' => 'int',
		'created_dt_gmt' => 'datetime',
		'modified_dt_gmt' => 'datetime',
		'is_active' => 'bool'
	);



	static function init()
	{
		add_action( sprintf('wp_ajax_save_%s', self::$slug), array(__CLASS__, 'ajax_save') );
		add_action( sprintf('wp_ajax_delete_%s', self::$slug), array(__CLASS__, 'ajax_delete') );
	}



	static function ajax_save ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::get_instance()->settings->basename)) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_save', Kanban::get_instance()->settings->basename, self::$slug) );



		$_POST['project']['modified_dt_gmt'] = gmdate('Y-m-d H:i:s');
		$_POST['project']['user_id_author'] = get_current_user_id();



		$is_successful = self::_replace($_POST['project']);



		$project_id = isset($_POST['task']['id']) ? $_POST['task']['id'] : self::_insert_id();



		$post_data = self::get_row('id', $project_id);

		if ( !$post_data ) wp_send_json_error();



		do_action( sprintf('%s_after_%s_ajax_save', Kanban::get_instance()->settings->basename, self::$slug) );



		if ( $is_successful )
		{
			wp_send_json_success(array(
				'message' => sprintf('%s saved', self::$slug),
				self::$slug => $post_data
			));
		}
		else
		{
			wp_send_json_error(array(
				'message' => sprintf('Error saving %s', self::$slug)
			));
		}
	}



	static function ajax_delete ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::get_instance()->settings->basename)) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_delete', Kanban::get_instance()->settings->basename, self::$slug) );



		$is_successful = self::delete($_POST['id']);



		do_action( sprintf('%s_after_%s_ajax_delete', Kanban::get_instance()->settings->basename, self::$slug) );



		if ( $is_successful )
		{
			wp_send_json_success(array(
				'message' => sprintf('%s deleted', self::$slug)
			));
		}
		else
		{
			wp_send_json_error(array(
				'message' => sprintf('Error deleting %s', self::$slug)
			));
		}
	}



	static function replace ($data)
	{
		return self::_replace($data);
	}



	protected static function delete ($id)
	{
		return self::_update(
			array('is_active' => 0),
			array('id' => $id)
		);
	}



	static function get_all ($sql = NULL)
	{
		$table_name = self::table_name();
		$tasks_table_name = Kanban_Task::table_name();

		$sql = "SELECT `projects`.*,
				(
					SELECT COUNT(`id`)
					FROM `{$tasks_table_name}` tasks
					WHERE `tasks`.`project_id` = `projects`.`id`
					AND `tasks`.`is_active` = 1
				)
				AS 'task_count'
				FROM `{$table_name}` projects
				WHERE `projects`.`is_active` = 1
		;";

		$sql = apply_filters(
			sprintf('%s_sql_%s_get_all', Kanban::get_instance()->settings->basename, self::$slug),
			$sql
		);

		$records = parent::get_all($sql);

		return Kanban_Utils::build_array_with_id_keys ($records, 'id');;
	}



	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					title varchar(256) NOT NULL,
					description text NOT NULL,
					user_id_author bigint(20) NOT NULL,
					created_dt_gmt datetime NOT NULL,
					modified_dt_gmt datetime NOT NULL,
					is_active BOOLEAN NOT NULL DEFAULT TRUE,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
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



	private function __construct() { }

}


