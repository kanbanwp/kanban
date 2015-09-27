<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Comment::init();



class Kanban_Comment
{
	static $instance = false;
	static $slug = 'comment';



	static function init()
	{
		self::$instance = self::get_instance();

		add_action( sprintf('wp_ajax_save_%s', self::$slug), array(__CLASS__, 'ajax_save') );

		//Before getting the comments, on the WP_Comment_Query object for each comment
		add_action('pre_get_comments', array(__CLASS__, 'wpse56652_filt_comm'));

		//Applied on the comments SQL Query, you can modify the 'Where' part of the query
		add_filter('comments_clauses', array(__CLASS__, 'wpse56652_filt_comm'));

		//After the comments are fetched, you can modify the comments array
		add_filter('the_comments', array(__CLASS__, 'wpse56652_filt_comm'));

	}



	static function ajax_save ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::$instance->settings->basename)) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		$current_user_id = get_current_user_id();



		$data = array(
			'comment_type' => Kanban_Utils::format_key ($_POST['post_type'], 'comment'),
			'comment_author' => Kanban::$instance->settings->pretty_name,
			'comment_post_ID' => $_POST['id'],
			'comment_content' => sanitize_text_field(str_replace("\n", '', $_POST['comment_content'])),
			'user_id' => $current_user_id,
			'comment_approved' => 1
		);

		$comment_id = wp_insert_comment($data);



		// $comment_type = Kanban_Utils::format_key ($_POST['post_type'], 'comment');

		// update_comment_meta( $comment_id, 'comment_type', $comment_type);



		do_action( sprintf('%s_after_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		wp_send_json_success(array(
			'message' => sprintf('%s saved', $comment_type)
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



