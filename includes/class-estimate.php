<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Kanban_Estimate::init();



class Kanban_Estimate extends Kanban_Db
{
	// the instance of this object
	private static $instance;

	// the table name of this class
	protected static $table_name = 'estimates';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'title'    => 'text',
		'hours'    => 'float',
		'board_id' => 'int',
		'position' => 'int'
	);



	static function init()
	{
	}



	static function get_all( $sql = NULL )
	{
		$table_name = self::table_name();

		$sql = "SELECT *
				FROM `{$table_name}`
				ORDER BY `position` ASC
		;";

		$sql = apply_filters( 'kanban_estimate_get_all_sql', $sql );

		$records = parent::get_all( $sql );

		return apply_filters(
			'kanban_estimate_get_all_return',
			Kanban_Utils::build_array_with_id_keys ( $records, 'id' )
		);
	}



	// extend parent, so it's accessible from other classes
	static function replace( $data )
	{
		return self::_replace( $data );
	}



	// extend parent, so it's accessible from other classes
	static function delete( $where )
	{
		return self::_delete( $where );
	}



	// extend parent, so it's accessible from other classes
	static function insert_id()
	{
		return self::_insert_id();
	}



	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					title varchar(64) NOT NULL,
					hours decimal(6, 4) NOT NULL,
					position bigint(20) NOT NULL,
					board_id bigint(20) NOT NULL,
					UNIQUE KEY  (id),
					KEY board_id (board_id)
				)';
	} // db_table



	/**
	 * get the instance of this class
	 * @return object the instance
	 */
	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}



	/**
	 * construct that can't be overwritten
	 */
	private function __construct() { }

}
