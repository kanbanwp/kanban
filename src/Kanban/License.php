<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



//Kanban_License::init();



class Kanban_License
{

	static function init()
	{
		add_action( 'init', array( __CLASS__, 'save_licenses' ), 10 );
	}



	static function save_licenses()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-licenses' ) || ! is_user_logged_in() ) return;



		do_action( 'kanban_license_save_settings_before', $_POST );



		$board = Kanban_Board::get_current();



		// get current settings
		$settings = Kanban_Option::get_all($board->id);



		// save all single settings
		foreach ( $settings as $key => $value )
		{
			// if empty or not license, skip it
			if ( substr($key, 0, 7) != 'license' ) continue;

			Kanban_Option::update_option($key, $_POST['settings'][$key], 0);
		}



		do_action( 'kanban_license_save_settings_after', $_POST );



		$url = add_query_arg(
			array(
				'message' => urlencode( __( 'Licenses saved', 'kanban' ) )
			),
			$_POST['_wp_http_referer']
		);

		wp_redirect( $url );
		exit;
	}


}



