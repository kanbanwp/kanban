<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Kanban_Option::init();



class Kanban_Option extends Kanban_Db
{
	private static $instance;
	protected static $table_name = 'options';



	static function init()
	{
	}



	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					name varchar(64) NOT NULL,
					value longtext NOT NULL,
					PRIMARY KEY  (id)
				)";
	} // db_table



	static function table_name()
	{
		return Kanban_Db::format_table_name(self::$table_name);
	}



	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

}


