<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Kanban_Option::init();



class Kanban_Option extends Kanban_Db
{
	private static $instance;
	protected static $table_name = 'options';
	protected static $table_columns = array(
		'name' => 'text',
		'value' => 'text'
	);

	protected static $defaults = array (
		'hour_interval' => '1',
		'allowed_users' => ''
	);



	static function init()
	{

	}


	static function replace ($data)
	{
		return self::_replace($data);
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


	static function get_defaults ()
	{
		self::$defaults['allowed_users'] = serialize(array(get_current_user_id()));

		return self::$defaults;
	}


	static function get_all_raw ()
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

		// unserialize arrays
		foreach ($records as $key => $record)
		{
			if ( !is_serialized($record->value) ) continue;

			$records[$key]->value = unserialize($record->value);
		}

		return $records;
	}



	static function get_all ($sql = NULL)
	{
		$records = self::get_all_raw();

		$output = array();
		foreach ($records as $record)
		{
			$output[$record->name] = $record->value;
		}

		$output = array_merge(self::get_defaults(), $output);

		return $output;
	}



	static function get_option($name)
	{
		global $wpdb;

		$table_name = self::table_name();

		$sql = "SELECT `value`
				FROM `{$table_name}`
				WHERE `name` = %s
		;";

		$value = $wpdb->get_var(
			$wpdb->prepare( $sql, $name )
		);
		if ( is_serialized($value) )
		{
			$value = unserialize($value);
		}

		return $value;
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


