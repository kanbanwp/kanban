<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Work_Hour::init();



class Kanban_Work_Hour
{
	static $instance = false;
	static $slug = 'work_hour';



	static function init()
	{
		self::$instance = self::get_instance();

		add_action( sprintf('wp_ajax_add_%s', self::$slug), array(__CLASS__, 'ajax_save') );
		add_action( sprintf('wp_ajax_remove_%s', self::$slug), array(__CLASS__, 'ajax_remove') );
	}



	static function ajax_save ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::$instance->settings->basename)) || !isset($_POST[Kanban_Task::$slug]) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		// build post data
		$post_data = array(
			'post_type' => Kanban_Post_Types::format_post_type ('work_hour'),
			'post_title' => sanitize_text_field($_POST[Kanban_Task::$slug]['post_title']),
			'postmeta' => array(),
			'terms' => array()
		);



		$hour_operator = Kanban_Utils::format_key ('work_hour', 'operator');
		$post_data['postmeta'][$hour_operator] = $_POST['operator'];



		// set assignee as author of work hour
		$task_user_id_assigned_to = Kanban_Utils::format_key ('task', 'user_id_assigned');

		if ( $_POST[Kanban_Task::$slug]['postmeta'][$task_user_id_assigned_to] > 0 )
		{
			$post_data['post_author'] = $_POST[Kanban_Task::$slug]['postmeta'][$task_user_id_assigned_to];
		}



		// link task to hour
		$hour_task_id = Kanban_Utils::format_key ('work_hour', 'project_id');
		$post_data['postmeta'][$hour_task_id] = $_POST[Kanban_Task::$slug]['ID'];



		// link current user to hour
		$hour_user_id_logged = Kanban_Utils::format_key ('work_hour', 'user_id_logged');
		$post_data['postmeta'][$hour_user_id_logged] = get_current_user_id();



		// set task project as work project
		$task_project_id = Kanban_Utils::format_key ('task', 'project_id');
		$hour_project_id = Kanban_Utils::format_key ('work_hour', 'project_id');
		$post_data['postmeta'][$hour_project_id] = $_POST[Kanban_Task::$slug]['postmeta'][$task_project_id];



		// set current task status for work hour
		$task_status = Kanban_Utils::format_key ('task', 'status');
		$hour_status_id = Kanban_Utils::format_key ('work_hour', 'task_status_id');
		$post_data['postmeta'][$hour_status_id] = $_POST[Kanban_Task::$slug]['terms'][$task_status][0];



		// save our work_hour
		$post_data = Kanban_Post::save($post_data);



		if ( !$post_data ) wp_send_json_error();



		do_action( sprintf('%s_after_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		wp_send_json_success(array(
			'message' => sprintf('%s saved', self::$slug),
			self::$slug => $post_data
		));
	}



	static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

}


