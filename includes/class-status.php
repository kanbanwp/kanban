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
		'color_hex' => 'text',
		'board_id' => 'int',
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
				ORDER BY `position` ASC
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
					color_hex varchar(7) NOT NULL,
					position bigint(20) NOT NULL,
					board_id bigint(20) NOT NULL,
					UNIQUE KEY  (id),
					KEY board_id (board_id)
				)";
	} // db_table



	static function replace ($data)
	{
		return self::_replace($data);
	}



	static function delete ($where)
	{
		return self::_delete($where);
	}



	static function insert_id ()
	{
		return self::_insert_id();
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


