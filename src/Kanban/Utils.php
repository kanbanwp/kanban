<?php
/**
 * Utility functions used throughout the plugin.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }



/**
 * Class Kanban_Utils
 */
class Kanban_Utils
{
	private static $is_network;


	/**
	 * Get the Kanban-namespaced nonce.
	 *
	 * @return string
	 */
	static function get_nonce() {
		return sprintf( '%s_nonce', Kanban::get_instance()->settings->basename );
	}



	static function is_network () {
		if ( ! isset( self::$is_network ) ) {
			self::$is_network = is_plugin_active_for_network( Kanban::get_instance()->settings->plugin_basename ) ? true : false;
		}

		return self::$is_network;
	}


	/**
	 * Convert a one-dimensional array of objects to two-dimensional array of objects based on property 'id'.
	 *
	 * @param array  $arr		Array of objects to convert.
	 * @param string $id_key	Key to sort by.
	 * @return array
	 */
	static function build_array_with_id_keys( $arr, $id_key = 'id' ) {
		$return = array();

		foreach ( $arr as $obj ) {
			if ( is_array( $obj ) ) { $obj = (object) $obj; }

			$return[ $obj->$id_key ] = $obj;
		}

		return $return;
	}


	/**
	 * Pluralize a word.
	 *
	 * @param string $word The word to pluralize.
	 * @return string
	 */
	static function make_word_plural( $word ) {
		return mb_substr( $word, -1 ) == 's' ? sprintf( __( '%ses', 'kanban' ), $word ) : sprintf( __( '%ss', 'kanban' ), $word );
	}


	/**
	 * Search through an array of objects, and return the object that has a key/value match.
	 *
	 * @link http://stackoverflow.com/a/17694792/38241
	 *
	 * @param string $prop		Property/key to find in an object.
	 * @param string $val		The value of the property to match.
	 * @param array  $arr		The array of objects to look through.
	 * @return bool|int|string
	 */
	static function find_key_of_object_by_property( $prop, $val, $arr ) {
		foreach ( $arr as $key => $item ) {
			if ( $val == $item->$prop ) {
				return $key;
				break;
			}
		}

		return false;
	}


	/**
	 * Order an array of objects based on a property.
	 *
	 * @param array  $arr                The array of objects.
	 * @param string $property			The property to order by.
	 * @param string $cmp_type	Order based on string or int.
	 * @return mixed
	 */
	static function order_array_of_objects_by_property( $arr, $property, $cmp_type = 'str' ) {

		if ( 'int' == $cmp_type ) {
			usort(
				$arr,
				function( $a, $b ) use ( $property ) {
					if ( is_array( $a ) ) { $a = (object) $a; }
					if ( is_array( $b ) ) { $b = (object) $b; }

					return ( (int) $a->$property < (int) $b->$property ) ? -1 : 1;
				}
			);
		} else {
			usort(
				$arr,
				function( $a, $b ) use ( $property ) {
					if ( is_array( $a ) ) { $a = (object) $a; }
					if ( is_array( $b ) ) { $b = (object) $b; }

					return strcmp( $a->$property, $b->$property );
				}
			);
		}

		return $arr;
	}



	/**
	 * Get current date time in GMT.
	 *
	 * @return false|string
	 */
	static function mysql_now_gmt() {
		return current_time('mysql', TRUE);
	}



	/**
	 * COnvert many slashes to one slash.
	 *
	 * @param string $str String to remove extra slashes from.
	 * @return mixed
	 */
	static function slashes( $str ) {
		return preg_replace( '/\\\\{2,}/', '\\', $str );
	}
}
