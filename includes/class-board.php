<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Board::init();



class Kanban_Board
{
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

		global $wp_query;

		$wp_query->query_vars['kanban'] = (object) array();
		$wp_query->query_vars['kanban']->board = (object) array();


		// // get all data for the javascript
		$wp_query->query_vars['kanban']->board->allowed_users = Kanban_User::get_allowed_users();

		$wp_query->query_vars['kanban']->board->estimates = Kanban_Terms::terms_in_order ('task', 'estimate');

		$wp_query->query_vars['kanban']->board->status_tax_key = Kanban_Utils::format_key ('task', 'status');
		$wp_query->query_vars['kanban']->board->status_color_field_name = sprintf('%s_colors', $wp_query->query_vars['kanban']->board->status_tax_key);
		$wp_query->query_vars['kanban']->board->status_colors = Kanban_Settings::get_option($wp_query->query_vars['kanban']->board->status_color_field_name, null, array());
		$wp_query->query_vars['kanban']->board->statuses = Kanban_Terms::terms_in_order ('task', 'status');

		foreach ( $wp_query->query_vars['kanban']->board->statuses as $status)
		{
			if ( !isset($wp_query->query_vars['kanban']->board->status_colors[$status->term_id]) ) continue;
			$status->color = $wp_query->query_vars['kanban']->board->status_colors[$status->term_id];
		}

		$wp_query->query_vars['kanban']->board->projects = Kanban_Project::get_all();
		$wp_query->query_vars['kanban']->board->tasks = Kanban_Task::get_all();


		$current_user_id = get_current_user_id();

		$wp_query->query_vars['kanban']->board->current_user = get_user_by('id', $current_user_id);
		unset($wp_query->query_vars['kanban']->board->current_user->data->user_pass);
		$wp_query->query_vars['kanban']->board->current_user->data->long_name_email = Kanban_User::format_user_name ($wp_query->query_vars['kanban']->board->current_user);
		$wp_query->query_vars['kanban']->board->current_user->data->short_name = Kanban_User::format_user_name ($wp_query->query_vars['kanban']->board->current_user, TRUE);
		$wp_query->query_vars['kanban']->board->current_user->data->initials = Kanban_User::get_initials ($wp_query->query_vars['kanban']->board->current_user);

		$wp_query->query_vars['kanban']->board->col_percent_w = count($wp_query->query_vars['kanban']->board->statuses) > 0 ? 100/(count($wp_query->query_vars['kanban']->board->statuses)) : 100;
		$wp_query->query_vars['kanban']->board->sidebar_w = count($wp_query->query_vars['kanban']->board->statuses) > 0 ? 100/(count($wp_query->query_vars['kanban']->board->statuses)-2) : 0;


		return $template;
	}



} // Kanban_Board


