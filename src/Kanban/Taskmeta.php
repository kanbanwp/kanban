<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }



// Kanban_Taskmeta::init();
class Kanban_Taskmeta extends Kanban_Db
{
	// the common name for this class
	static $slug = 'taskmeta';

	// the table name of this class
	protected static $table_name = 'taskmeta';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'meta_key' => 'text',
	 	'meta_value' => 'text',
	 	'created_dt_gmt' => 'datetime',
		'task_id' => 'int',
	);



	static function get_one( $task_id, $meta_key ) {
		global $wpdb;

		$table_name = self::table_name();

		$sql = $wpdb->prepare(
			"SELECT * FROM $table_name WHERE task_id = %d AND meta_key = %s",
			$task_id,
			$meta_key
		);

		$sql = apply_filters( 'kanban_taskmeta_get_one_sql', $sql );

		$record = $wpdb->get_row( $sql );

		return $record;
	}



	static function update( $task_id, $meta_key, $meta_value ) {
		// delete existing record
		self::delete( $task_id, $meta_key );

		$is_successful = self::_insert(
			array(
				'meta_key'       => $meta_key,
				'meta_value'     => $meta_value,
				'created_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
				'task_id'        => $task_id,
			)
		);

		// update modified data
		Kanban_Task::update(
			$task_id,
			array(
				'modified_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
				'modified_user_id' => get_current_user_id()
			)
		);

		return $is_successful;
	}



	static function delete( $task_id, $meta_key ) {
		$is_successful = self::_delete(
			array(
				'task_id'  => $task_id,
				'meta_key' => $meta_key,
			)
		);

		return $is_successful;
	}




	static function duplicate( $taskmeta_id, $data = array() ) {
		// reset
		unset( $data['id'] );
		$data['created_dt_gmt'] = Kanban_Utils::mysql_now_gmt();

		$is_successful = self::_duplicate( $taskmeta_id, $data );

		return $is_successful;
	}



	// define the db schema
	static function db_table() {
		return 'CREATE TABLE ' . self::table_name() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			task_id bigint(20) unsigned NOT NULL DEFAULT '0',
			created_dt_gmt datetime NOT NULL,
			meta_key varchar(128) DEFAULT NULL,
			meta_value longtext,
			UNIQUE KEY id (id),
			KEY task_id (task_id),
			KEY meta_key (meta_key)
			)";
	} // db_table
}
