<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }



// Kanban_Flash::init();
class Kanban_Flash
{
	static $namespace = 'kanban-flash';
	static $expire = 3600;



	/**
	 * Function to create and display error and success messages
	 *
	 * @link http://www.phpdevtips.com/2013/05/simple-session-based-flash-messages/
	 * @param  string message
	 * @param  string display class
	 * @return string message
	 */
	static function flash( $message = '', $class = 'success' ) {
		// No message, create it
		if ( ! empty( $message ) ) {
			self::clear();

			set_transient( self::$namespace, $message, self::$expire );
			set_transient( self::$namespace.'_class', $class, self::$expire );
		} // Message exists, display it
		else {
			$message = get_transient( self::$namespace );

			if ( ! empty( $message ) ) {

				$class = get_transient( self::$namespace . '_class' );
				if ( empty( $class ) ) {
					$class = 'success';
				}

				echo sprintf(
					'<div class="alert alert-%s">%s</div>',
					$class,
					stripslashes( get_transient( self::$namespace ) )
				);

				self::clear();
			}
		}
	}



	static function clear() {
		delete_transient( self::$namespace );
		delete_transient( self::$namespace.'_class' );
	}
}
