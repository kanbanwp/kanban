<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Kanban_Taskmeta::init();



class Kanban_Taskmeta extends Kanban_Db
{
	// the common name for this class
	static $slug = 'taskmeta';

	// the table name of this class
	protected static $table_name = 'taskmeta';

	// define db table columns and their validation type
	// protected static $table_columns = array(
	// 	'meta_value' => 'text',
	// 	'created_dt_gmt' => 'datetime'
	// );


	static function update( $task_id, $meta_key, $meta_value )
	{
		global $wpdb;



		// delete existing record
		self::delete($task_id, $meta_key);



		// create new record
		$success = $wpdb->insert(
			self::table_name(),
			array(
				'meta_key'       => $meta_key,
				'meta_value'     => $meta_value,
				'created_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
				'task_id'        => $task_id,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d'
			)
		);



		return $success;
	}



	static function delete( $task_id, $meta_key )
	{
		global $wpdb;



		// delete existing record
		$success = $wpdb->delete(
			self::table_name(),
			array(
				'task_id'  => $task_id,
				'meta_key' => $meta_key
			)
		);



		return $success;
	}



	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			task_id bigint(20) unsigned NOT NULL DEFAULT '0',
			created_dt_gmt datetime NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			UNIQUE KEY  (id),
			KEY task_id (task_id),
			KEY meta_key (meta_key)
			)";
	} // db_table
}
