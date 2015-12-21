<?php

/**
 * the class for rendering our kanban board
 */



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// instantiate the plugin
Kanban_Board::init();



class Kanban_Board extends Kanban_Db
{
	private static $instance;
	protected static $table_name = 'boards';
	protected static $table_columns = array(
		'title' => 'text',
		'description' => 'text',
		'created_dt_gmt' => 'datetime',
		'modified_dt_gmt' => 'datetime',
		'user_id_author' => 'int',
		'is_active' => 'bool'
	);

	static $slug = 'board';



	static function init ()
	{
		add_filter('template_include', array(__CLASS__, 'send_page_data_to_template'), 100); // must be higher than template
	}



	/**
	 * load data needed for board's javascript
	 * @param  string $template the passed in template path
	 * @return string           the same template path
	 */
	static function send_page_data_to_template ($template)
	{
		if ( !isset(Kanban_Template::get_instance()->slug) || Kanban_Template::get_instance()->slug != self::$slug ) return $template;



		// make sure they don't need to upgrade
		if ( Kanban::get_instance()->settings->records_to_move > 0 )
		{
			?>
			<p>
			<?php echo sprintf(__('We\'ve found %s kanban records that need to be migrated for the latest version of Kanban for WordPress!', Kanban::get_text_domain()), Kanban::get_instance()->settings->records_to_move); ?>
			</p>
			<p>
			<?php echo sprintf(
					__(
						'Please visit the <a href="%s">Kanban welcome page</a> to migrate your data.',
						Kanban::get_text_domain()
					),
					add_query_arg(
						'page',
						'kanban_welcome',
						admin_url('admin.php')
					)
				);
			?>
			<?php
			exit;
		}



		global $wp_query;

		$wp_query->query_vars['kanban'] = (object) array();
		$wp_query->query_vars['kanban']->board = (object) array();


		// get all data for the javascript
		$wp_query->query_vars['kanban']->board->settings = Kanban_Option::get_all();

		$wp_query->query_vars['kanban']->board->allowed_users = Kanban_User::get_allowed_users();

		$wp_query->query_vars['kanban']->board->estimates = Kanban_Estimate::get_all();
		$wp_query->query_vars['kanban']->board->statuses = Kanban_Status::get_all();

		$wp_query->query_vars['kanban']->board->projects = Kanban_Project::get_all();
		$wp_query->query_vars['kanban']->board->tasks = Kanban_Task::get_all();

		$current_user_id = get_current_user_id();
		$wp_query->query_vars['kanban']->board->current_user = $wp_query->query_vars['kanban']->board->allowed_users[$current_user_id];

		$wp_query->query_vars['kanban']->board->col_percent_w = count($wp_query->query_vars['kanban']->board->statuses) > 0 ? 100/(count($wp_query->query_vars['kanban']->board->statuses)) : 100;
		$wp_query->query_vars['kanban']->board->sidebar_w = count($wp_query->query_vars['kanban']->board->statuses) > 0 ? 100/(count($wp_query->query_vars['kanban']->board->statuses)-2) : 0;


		return $template;
	}



	static function replace ($data)
	{
		return self::_replace($data);
	}



	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
					id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					title text NOT NULL,
					description text NOT NULL,
					created_dt_gmt datetime NOT NULL,
					modified_dt_gmt datetime NOT NULL,
					user_id_author bigint(20) NOT NULL,
					is_active BOOLEAN NOT NULL DEFAULT TRUE,
					UNIQUE KEY id (id),
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



	private function __construct() {}



} // Kanban_Board


