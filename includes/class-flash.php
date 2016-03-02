<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Flash::init();



class Kanban_Flash
{
	static $namespace = 'kanban-flash';
	static $is_session_available = FALSE;


	static function init()
	{
		if ( ! function_exists( 'session_start' ) ) return;

		self::$is_session_available = TRUE;

		// @link http://stackoverflow.com/a/28377350/38241
		if ( version_compare( phpversion(), '5.4.0' ) != -1 )
		{
			if ( session_status() == PHP_SESSION_NONE )
			{
				session_start();
			}
		}
		else
		{
			if ( session_id() == '' )
			{
				session_start();
			}
		}
	}


	/**
	 * Function to create and display error and success messages
	 * @link http://www.phpdevtips.com/2013/05/simple-session-based-flash-messages/
	 * @param  string message
	 * @param  string display class
	 * @return string message
	 */
	static function flash( $message = '', $class = 'success' )
	{
		if ( ! self::$is_session_available ) return;


		//No message, create it
		if ( ! empty( $message ) )
		{
			self::clear();

			$_SESSION[self::$namespace] = $message;
			$_SESSION[self::$namespace.'_class'] = $class;
		}
		//Message exists, display it
		elseif ( ! empty( $_SESSION[self::$namespace] ) )
		{
			$class = ! empty( $_SESSION[self::$namespace.'_class'] ) ? $_SESSION[self::$namespace.'_class'] : 'success';
			echo sprintf(
				'<div class="alert alert-%s">%s</div>',
				$class,
				$_SESSION[self::$namespace]
			);

			self::clear();
		}
	}



	static function clear()
	{
		unset( $_SESSION[self::$namespace] );
		unset( $_SESSION[self::$namespace.'_class'] );
	}

}
