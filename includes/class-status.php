<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Kanban_Status::init();



class Kanban_Status extends Kanban_Db
{
	// the instance of this object
	private static $instance;

	// the table name of this class
	protected static $table_name = 'statuses';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'title'     => 'text',
		'color_hex' => 'text',
		'board_id'  => 'int',
		'position'  => 'int'
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

		$sql = apply_filters( 'kanban_status_get_all_sql', $sql );

		$records = parent::get_all( $sql );

		foreach ( $records as $key => $record )
		{
			$records[$key]->title = Kanban_Utils::str_for_frontend( $records[$key]->title );
		}

		return apply_filters(
			'kanban_status_get_all_return',
			Kanban_Utils::build_array_with_id_keys ( $records, 'id' )
		);
	}



	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					title varchar(64) NOT NULL,
					color_hex varchar(7) NOT NULL,
					position bigint(20) NOT NULL,
					board_id bigint(20) NOT NULL,
					UNIQUE KEY  (id),
					KEY board_id (board_id)
				)';
	} // db_table



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
