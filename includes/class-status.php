<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Kanban_Status::init();



class Kanban_Status extends Kanban_Db
{
	private static $instance;
	protected static $table_name = 'statuses';
	protected static $table_columns = array(
		'title' => 'text',
		'color' => 'float',
		'position' => 'int'
	);



	static function init()
	{
	}



	static function get_all ($sql = NULL)
	{
		$table_name = self::table_name();

		$sql = "SELECT *
				FROM `{$table_name}`
		;";

		$sql = apply_filters(
			sprintf(
				'%s_sql_%s_get_all',
				Kanban::get_instance()->settings->basename,
				self::$table_name
			),
			$sql
		);

		$records = parent::get_all($sql);

		return Kanban_Utils::build_array_with_id_keys ($records, 'id');
	}



	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					title varchar(64) NOT NULL,
					color_hex varchar(6) NOT NULL,
					position bigint(20) NOT NULL,
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


