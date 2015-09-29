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

		// add_action( 'wp', array(__CLASS__, 'post_save') );
		add_action( sprintf('wp_ajax_save_%s', self::$slug), array(__CLASS__, 'ajax_save') );
	}



	static function ajax_save ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::$instance->settings->basename)) || !isset($_POST[self::$slug]) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		if ( !isset($_POST[self::$slug]['post_type']) )
		{
			$post_type = Kanban_Post_Types::format_post_type (self::$slug);
			$_POST[self::$slug]['post_type'] = $post_type;
		}

		$post_data = Kanban_Post::save($_POST[self::$slug]);



		if ( !$post_data ) wp_send_json_error();



		do_action( sprintf('%s_after_%s_ajax_save', Kanban::$instance->settings->basename, self::$slug) );



		wp_send_json_success(array(
			'message' => sprintf('%s saved', self::$slug),
			self::$slug => $post_data
		));
	}



	static function ajax_delete ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-save', Kanban::$instance->settings->basename)) || !isset($_POST[self::$slug]) || !is_user_logged_in() ) wp_send_json_error();



		do_action( sprintf('%s_before_%s_ajax_delete', Kanban::$instance->settings->basename, self::$slug) );



		$is_successful = Kanban_Post::delete($_POST[self::$slug]);



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
			global $wpdb;

			$post_type_key = Kanban_Post_Types::format_post_type('project');
			$sql = "SELECT `{$wpdb->prefix}posts`.*
					FROM `{$wpdb->prefix}posts`
					WHERE `{$wpdb->prefix}posts`.`post_type` = '$post_type_key'
					AND `{$wpdb->prefix}posts`.`post_status` IN ('publish')
			;";

			$sql = apply_filters(
				sprintf('%s_sql_%s_get_all', Kanban::$instance->settings->basename, self::$slug),
				$sql
			);

			$posts = $wpdb->get_results($sql);

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


