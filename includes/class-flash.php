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
		if (session_status() == PHP_SESSION_NONE) session_start();

		Kanban::$instance->flash = new Kanban\Messages();

		Kanban::$instance->flash->msgTypes = array( 'default', 'info', 'warning', 'success', 'danger' );
		Kanban::$instance->flash->msgClass = 'alert alert-';
		Kanban::$instance->flash->msgWrapper = '<div class="%s%s alert-dismissible">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
								</button>
								%s
								</div>'
								. "\n";


		Kanban::$instance->flash = apply_filters(
			sprintf('%s_after_setup_flash_messages', Kanban::$instance->settings->basename),
			Kanban::$instance->flash
		);

	}
}