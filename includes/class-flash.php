<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Flash::init();



class Kanban_Flash
{
	static function init()
	{
		add_action('init', array(__CLASS__, 'setup_flash_messages'));
	}



	static function setup_flash_messages()
	{
		// @link http://stackoverflow.com/a/28377350/38241
		if(version_compare(phpversion(), "5.4.0") != -1)
		{
			if (session_status() == PHP_SESSION_NONE)
			{
				session_start();
			}
		}
		else
		{
			if(session_id() == '')
			{
				session_start();
			}
		}

		Kanban::get_instance()->flash = new Kanban_Messages();



		Kanban::get_instance()->flash->msgTypes = array( 'default', 'info', 'warning', 'success', 'danger' );
		Kanban::get_instance()->flash->msgClass = 'alert alert-';
		Kanban::get_instance()->flash->msgWrapper = '<div class="%s%s alert-dismissible">
								%s
								</div>'
								. "\n";


		Kanban::get_instance()->flash = apply_filters(
			sprintf('%s_after_setup_flash_messages', Kanban::get_instance()->settings->basename),
			Kanban::get_instance()->flash
		);

	}
}
