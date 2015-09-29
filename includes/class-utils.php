<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Kanban_Utils {

	static function get_nonce() {
		return sprintf( '%s_nonce', Kanban::$instance->settings->basename );
	}


	static function format_key( $post_type, $key ) {
		$post_type = Kanban_Post_Types::format_post_type( $post_type );

		return sprintf( '%s_%s', $post_type, $key );
	}


	static function build_array_with_id_keys( $arr, $id_key = 'ID' ) {
		$return = array();

		foreach ( $arr as $obj ) {
			$return[ $obj->$id_key ] = $obj;
		}

		return $return;
	}


	static function make_word_plural( $word ) {
		return substr( $word, - 1 ) == 's' ? sprintf( '%ses', $word ) : sprintf( '%ss', $word );
	}


	// http://stackoverflow.com/a/17694792/38241
	static function find_key_of_object_by_property( $prop, $val, $arr ) {
		foreach ( $arr as $key => $item ) {
			if ( $val == $item->$prop ) {
				return $key;
				break;
			}
		}

		return false;
	}
}