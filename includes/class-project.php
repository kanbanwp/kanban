<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Project::init();



class Kanban_Project
{
	private static $instance = false;
	static $slug = 'project';



	static function init()
	{
		self::$instance = self::get_instance();

		add_action( sprintf('wp_ajax_save_%s', self::$slug), array(__CLASS__, 'ajax_save') );
		add_action( sprintf('wp_ajax_delete_%s', self::$slug), array(__CLASS__, 'ajax_delete') );
	}



	static function ajax_save ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::$instance->settings->basename)) || $_POST['post_type'] !== Kanban_Post_Types::format_post_type(self::$slug) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		if ( !isset($_POST['post_type']) )
		{
			$post_type = Kanban_Post_Types::format_post_type (self::$slug);
			$_POST['post_type'] = $post_type;
		}

		$post_data = Kanban_Post::save($_POST);



		if ( !$post_data ) wp_send_json_error();



		do_action( sprintf('%s_after_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		wp_send_json_success(array(
			'message' => sprintf('%s saved', self::$slug),
			self::$slug => $post_data
		));
	}



	static function ajax_delete ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::$instance->settings->basename)) || $_POST['post_type'] !== Kanban_Post_Types::format_post_type(self::$slug) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_delete', Kanban::$instance->settings->basename, self::$slug) );



		$is_successful = Kanban_Post::delete($_POST);



		do_action( sprintf('%s_after_%s_ajax_delete', Kanban::$instance->settings->basename, self::$slug) );



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



	static function get_all()
	{
		if ( !isset(self::$instance->all_projects) )
		{
			$post_type_key = Kanban_Post_Types::format_post_type(self::$slug);

			$args = array(
				'posts_per_page'   => -1,
				'post_type'        => $post_type_key,
				'post_status'      => 'publish',
			);

			$posts = get_posts( $args );

			self::$instance->all_projects = Kanban_Post::apply_postmeta_and_terms_to_posts($posts);
		}

		return self::$instance->all_projects;
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


