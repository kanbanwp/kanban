<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Status_Change::init();



class Kanban_Status_Change
{
	static $instance = false;
	static $slug = 'status_change';



	static function init()
	{
		self::$instance = self::get_instance();

		add_action( sprintf('wp_ajax_add_%s', self::$slug), array(__CLASS__, 'ajax_save') );
	}



	static function ajax_save ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::$instance->settings->basename)) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		$status_id_old = Kanban_Utils::format_key (self::$slug, 'status_id_old');
		$status_id_new = Kanban_Utils::format_key (self::$slug, 'status_id_new');

		// build post data
		$post_data = array(
			'post_type' => Kanban_Post_Types::format_post_type (self::$slug),
			'post_title' => sprintf('changed from %s to %s', $_POST['status_id_old'], $_POST['status_id_new']),
			'postmeta' => array(
				$status_id_old => $_POST['status_id_old'],
				$status_id_new => $_POST['status_id_new']
			),
			'terms' => array()
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



	static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

}


