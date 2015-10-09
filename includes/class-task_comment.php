<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Task_Comment::init();



class Kanban_Task_Comment
{
	static $instance = false;
	static $slug = 'task_comment';



	static function init()
	{
		// self::$instance = self::get_instance();

		add_action( sprintf('wp_ajax_save_%s', self::$slug), array(__CLASS__, 'ajax_save') );
	}



	static function ajax_save ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::$instance->settings->basename)) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		$current_user_id = get_current_user_id();



		$comment_type_field = Kanban_Utils::format_key (self::$slug, 'comment_type');


		// build post data
		$post_data = array(
			'post_type' => Kanban_Post_Types::format_post_type (self::$slug),
			'post_title' => sprintf('%s comment for task %s', $_POST['comment_type'], $_POST['id']),
			'post_content' => sanitize_text_field(str_replace("\n", '', $_POST['post_content'])),
			'post_parent' => $_POST['id'],
			'postmeta' => array(
				$comment_type_field => $_POST['comment_type']
			)
		);



		// save our work_hour
		$post_data = Kanban_Post::save($post_data);



		if ( !$post_data ) wp_send_json_error();



		do_action( sprintf('%s_after_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		wp_send_json_success(array(
			'message' => sprintf('%s saved', self::$slug),
			self::$slug => $post_data
		));

	}



	static function wpse56652_filt_comm($param)
	{
		if ( isset($param->query_vars) )
		{

			$comment_types = array(
				Kanban_Utils::format_key ('task', 'comment')
			);

			$param->query_vars['type__not_in'] = $comment_types;

		}

		return $param;
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



