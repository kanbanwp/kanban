<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Task::init();


class Kanban_Task
{
	static $instance = false;
	private static $slug = 'task';



	static function init()
	{
		self::$instance = self::get_instance();

		// add_action( 'wp', array(__CLASS__, 'post_save') );
		add_action( sprintf('wp_ajax_save_%s', self::$slug), array(__CLASS__, 'ajax_save') );
		add_action( sprintf('wp_ajax_delete_%s', self::$slug), array(__CLASS__, 'ajax_delete') );

		add_action( 'add_meta_boxes', array(__CLASS__, 'add_comments_meta_box') );

	}




	static function format_hours($hours)
	{
		if ( $hours < 0 )
		{
			$hours = 0;
		}

		if ( $hours < 8 )
		{
			$label = sprintf('%sh', $hours);
		}
		else
		{
			$label = sprintf('%sd %sh', floor($hours/8), $hours % 8);
		}

		return $label;
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
		if ( !isset(self::$instance->all_tasks) )
		{
			global $wpdb;

			$post_type_key = Kanban_Post_Types::format_post_type(self::$slug);

			$sql = "SELECT *
					FROM `{$wpdb->prefix}posts`
					WHERE `{$wpdb->prefix}posts`.`post_type` = '$post_type_key'
					AND `{$wpdb->prefix}posts`.`post_status` IN ('publish')
			;";

			$sql = apply_filters(
				sprintf('%s_sql_%s_get_all', Kanban::$instance->settings->basename, self::$slug),
				$sql
			);

			$posts = $wpdb->get_results($sql);

			self::$instance->all_tasks = Kanban_Post::apply_postmeta_and_terms_to_posts($posts);
		}

		return self::$instance->all_tasks;
	}



	static function add_comments_meta_box()
	{
		add_meta_box
		(
			sprintf('%s_comments', Kanban_Post_Types::format_post_type(self::$slug)),
			'Task action log',
			array(__CLASS__, 'render_comments_meta_box'),
			Kanban_Post_Types::format_post_type(self::$slug),
			'side'
		);
	}


	static function render_comments_meta_box ($post)
	{
		global $wpdb;

		$comment_type = Kanban_Utils::format_key (self::$slug, 'comment');

		$sql = "SELECT `{$wpdb->prefix}comments`.*
				FROM `{$wpdb->prefix}comments`
				WHERE `comment_post_ID` = '{$post->ID}'
				AND `comment_type` = '{$comment_type}'
				ORDER BY `{$wpdb->prefix}comments`.`comment_date` DESC
			;";

		$sql = apply_filters(
			sprintf('%s_sql_%s_comments_meta_box', Kanban::$instance->settings->basename, self::$slug),
			$sql
		);

		$comments = $wpdb->get_results($sql);
		?>
		<style>
		#kanban_task_comments .inside {
			margin: 0;
			padding: 0;
		}
		#kanban_task_comments li {
			padding: 10px;
		}
		#kanban_task_comments small {
			color: #CCC;
			display: block;
		}
		</style>
		<ul class="striped">
		<?php foreach ($comments as $comment) : $comment_date_dt = new DateTime($comment->comment_date); ?>
			<li>
			<small>
			<?php echo $comment_date_dt->format('D, j M, Y') ?>
			at
			<?php echo $comment_date_dt->format('g:ia') ?>
			</small>
			<?php echo $comment->comment_content ?>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php
	}
	
	public static function get_slug() {
		return self::$slug;
	}

	static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

} // Kanban_Task



