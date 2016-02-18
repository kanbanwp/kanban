<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Task::init();



class Kanban_Taskmeta extends Kanban_Db
{
	// the common name for this class
	static $slug = 'taskmeta';

	// the table name of this class
	protected static $table_name = 'taskmeta';



	static function update($task_id, $meta_key, $meta_value)
	{
		global $wpdb;



		// delete existing record
		$wpdb->delete(
			self::table_name(),
			array(
				'task_id' => $task_id,
				'meta_key' => $meta_key
			)
		);



		// create new record
		$success = $wpdb->insert(
			self::table_name(),
			array(
				'meta_key' => $meta_key,
				'meta_value' => $meta_value,
				'task_id' => $task_id,
			),
			array(
				'%s',
				'%s',
				'%d'
			)
		);



		return $success;
	}


	// define the db schema
	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			task_id bigint(20) unsigned NOT NULL DEFAULT '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			UNIQUE KEY  (id),
			KEY task_id (task_id),
			KEY meta_key (meta_key)
			)";
	} // db_table
}




