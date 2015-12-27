<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



class Kanban_Utils
{
	static function get_nonce()
	{
		return sprintf('%s_nonce', Kanban::get_instance()->settings->basename);
	}



	static function build_array_with_id_keys ($arr, $id_key = 'id')
	{
		$return = array();

		foreach ($arr as $obj)
		{
			$return[$obj->$id_key] = $obj;
		}

		return $return;
	}



	static function make_word_plural ($word)
	{
		return substr($word, -1) == 's' ? __( sprintf('%ses', $word), Kanban::get_text_domain() ) : __( sprintf('%ss', $word), Kanban::get_text_domain() );
	}



	// http://stackoverflow.com/a/17694792/38241
	static function find_key_of_object_by_property ($prop, $val, $arr)
	{
		foreach($arr as $key => $item)
		{
			if ($val == $item->$prop)
			{
				return $key;
				break;
			}
		}

		return FALSE;
	}



	static function order_array_of_objects_by_property ($arr, $property)
	{
		usort(
			$arr,
			function($a, $b) use ($property)
			{
				return strcmp($a->$property, $b->$property);
			}
		);

		return $arr;
	}



	static function mysql_now_gmt ()
	{
		return gmdate('Y-m-d H:i:s');
	}



	static function str_for_frontend ($str)
	{
			return htmlspecialchars(stripcslashes(stripcslashes(stripcslashes($str))), ENT_QUOTES);
	}
}


