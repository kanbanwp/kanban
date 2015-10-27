<?php

/**
 * @link http://mac-blog.org.ua/wordpress-custom-database-table-example-full/
 * @link https://deliciousbrains.com/managing-custom-tables-wordpress/
 */



Kanban_Db::init();

class Kanban_Db
{
	static $installed_ver;



	static function init()
	{
		add_action('plugins_loaded', array(__CLASS__, 'check_for_updates'));
	}




	private static function _fetch_sql( $key, $value )
	{
		global $wpdb;
		$sql = sprintf(
			'SELECT * FROM %s WHERE %s = %%s',
			self::table_name(),
			$key,
			$value
		);

		return $wpdb->prepare( $sql, $value );
	}



	static function get_row ( $key, $value )
	{
		global $wpdb;
		return $wpdb->get_row( self::_fetch_sql( $key, $value ) );
	}




	static function get_all ($sql)
	{
		global $wpdb;
		$records = $wpdb->get_results( $sql, OBJECT );

		return $records;
	}




	// static function replace ($data)
	// {
	// 	$id = isset($data['id']) ? self::update($data) : self::insert($data);

	// 	// $data = self::sanitize_data($data);

	// 	// global $wpdb;
	// 	// $wpdb->replace( self::table_name(), $data->data, $data->format );
	// 	return self::insert_id();


	// 	// return $id;
	// }



	static function insert( $data )
	{
		$data = self::sanitize_data($data);

		global $wpdb;
		$wpdb->insert( self::table_name(), $data->data, $data->format );
		return self::insert_id();
	}



	static function update( $data, $where )
	{
		$data = self::sanitize_data($data);

		global $wpdb;
		$wpdb->update( self::table_name(), $data->data, $where, $data->format );
	}



	static function delete( $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %s WHERE %s = %%s', self::table_name(), static::$primary_key );
		return $wpdb->query( $wpdb->prepare( $sql, $value ) );
	}



	// get last inserted id
	static function insert_id()
	{
		global $wpdb;
		return $wpdb->insert_id;
	}



	static function time_to_date( $time )
	{
		return gmdate( 'Y-m-d H:i:s', $time );
	}



	static function now()
	{
		return self::time_to_date( time() );
	}



	static function date_to_time( $date )
	{
		return strtotime( $date . ' GMT' );
	}



	static function table_name()
	{
		return Kanban_Db::format_table_name(static::$table_name);
	}



	static function sanitize_data ($data)
	{
		$good_data = array();
		$format = array();
		foreach ($data as $key => $value)
		{
			if ( !isset(static::$table_columns[$key]) ) continue;

			switch ( static::$table_columns[$key] )
			{
				case 'float':
					if ( !is_numeric($value) ) continue;

					$value = floatval($value);
					if ( empty($value) ) continue;

					$good_data[$key] = $value;
					$format[] = '%f';

					break;



				case 'int':
					if ( !is_numeric($value) ) continue;

					$value = intval($value);
					if ( empty($value) ) continue;

					$good_data[$key] = $value;
					$format[] = '%d';

					break;



				case 'text':
					$good_data[$key] = sanitize_text_field($value);
					$format[] = '%s';

					break;



				case 'datetime':
					if ( is_a($value, 'DateTime') )
					{
						$good_data[$key] = $value->format('Y-m-d H:i:s');
						$format[] = '%s';
					}
					elseif ( ($timestamp = strtotime($value)) !== FALSE )
					{
						$dt = new DateTime($value);
						$good_data[$key] = $dt->format('Y-m-d H:i:s');
						$format[] = '%s';
					}

					break;
			} // switch


		}


		return (object) array(
			'data' => $good_data,
			'format' => $format
		);


	}



	/**
	 * [check_for_updates description]
	 * @link http://mac-blog.org.ua/wordpress-custom-database-table-example-full/
	 * @return [type] [description]
	 */
	static function check_for_updates ()
	{
		// if (self::installed_ver() == Kanban::get_instance()->settings->db_version ) return FALSE;

		global $charset_collate;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$classes_with_tables = array(
			'Kanban_Comment',
			'Kanban_Estimate',
			'Kanban_Settings',
			'Kanban_Project',
			'Kanban_Status_Change',
			'Kanban_Status',
			'Kanban_Task',
			'Kanban_Task_Hour'
		);

		foreach ($classes_with_tables as $class)
		{
			$sql = $class::db_table();

			$sql = sprintf('%s %s;', $sql, $charset_collate);

			// save table
			dbDelta($sql);
		}



		// save db version to avoid updates
		update_option(
			sprintf(
				'%s_db_version',
				Kanban::get_instance()->settings->basename
			),
			Kanban::get_instance()->settings->db_version
		);
	}



	static function installed_ver ()
	{
		if ( !isset(self::$installed_ver) )
		{
			self::$installed_ver = get_option(
				sprintf(
					'%s_db_version',
					Kanban::get_instance()->settings->basename
				)
			);
		}

		return self::$installed_ver;
	}



	static function format_table_name ($table)
	{
		global $wpdb;

		return sprintf(
			'%s%s_%s',
			$wpdb->prefix,
			Kanban::get_instance()->settings->basename,
			$table
		);
	}



	// public static function get_instance()
	// {
	// 	if ( ! self::$instance )
	// 	{
	// 		self::$instance = new self();
	// 	}
	// 	return self::$instance;
	// }
}



