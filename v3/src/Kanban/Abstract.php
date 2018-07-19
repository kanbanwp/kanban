<?php


abstract class Kanban_Abstract {


	// the instance of this object
	private static $_instances = array();

	/**
	 * @var array Database table fields and types for filtering.
	 */
	protected $fields = array(
		'id'               => '%d',
		'created_dt_gmt'   => '%s',
		'created_user_id'  => '%s',
		'modified_dt_gmt'  => '%s',
		'modified_user_id' => '%d',
		'is_active'        => '%d',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = '';


	/**
	 * @param Array $data Array of data
	 *
	 * @return false|int
	 */
	protected function set_row( $data ) {
		global $wpdb;

		if ( is_object($data) ) {
			$data = (array) $data;
		}

		$isNew = false;

		$is_delete = isset($data['is_active']) && $data['is_active'] == 0 ? true : false;

		// Add params for new record.
		if ( ! isset( $data['id'] ) || ! is_numeric( $data['id'] ) ) {
			$data['is_active']       = '1';
			$data['created_dt_gmt']  = current_time( 'mysql', 1 );
			$data['created_user_id'] = isset( $data['created_user_id'] ) ? $data['created_user_id'] : get_current_user_id();
			$isNew                   = true;
		}

		do_action( 'kanban_set_row_before', $data );

		// Add params for all data.
		$data['modified_dt_gmt']  = current_time( 'mysql', 1 );
		$data['modified_user_id'] = isset( $data['modified_user_id'] ) ? $data['modified_user_id'] : get_current_user_id();

		// Validate, format data and remove invalid fields.
		$data = $this->format_data_for_db( $data );

		// Get the formats we need.
		$format = array_intersect_key( $this->get_fields(), $data );
		ksort( $format );

		// Save the changes.
		if ( $isNew ) {

			$is_successful = $wpdb->insert(
				Kanban_Db::instance()->prefix() . $this->get_table(),
				$data,
				$format
			);

			$id = $wpdb->insert_id;

		} else {
			$id = $data['id'];
			unset( $data['id'] );
			unset( $format['id'] );

			$is_successful = $wpdb->update(
				Kanban_Db::instance()->prefix() . $this->get_table(),
				$data,
				array( 'id' => $id ),
				$format,
				array( '%d' )
			);
		}

		if ( ! $is_successful ) {
			header( 'HTTP/1.1 501 Record not saved' );
			return false;
		}

		if ( $is_successful && $is_delete ) {
			return true;
		}

		// Try to get the post data, to make sure it saved.
		$row = $this->get_row( $id );

		// If it didn't save, return error.
		if ( !$is_delete && ! $row ) {
			header( 'HTTP/1.1 501 Saved record not found' );
			return false;
		}

		do_action( 'kanban_set_row_after', $row );

		$row->is_new = (bool) $isNew;
		$row->is_updated = true;

		$row = $this->format_data_for_app($row);

		return $row;
	} // replace

//	public function replace ($data ) {
//		if ( ! Kanban_User::instance()->current_user_has_cap( 'admin' ) ) {
//			header( 'HTTP/1.1 401 Current user does not have cap' );
//
//			return false;
//		}
//
//		$row = $this->set_row($data);
//
//		return $row;
//	}

	public function get_row( $id ) {
		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.* 
					FROM $table
					WHERE 1=1
					AND $table.id = %d
				",
				$id
			),
			OBJECT
		);

		if ( empty($row) ) return (object) array();

		$row = $this->format_data_for_app( $row );

		return $row;
	}

//	public function get_results($since_dt = null) {
//		global $wpdb;
//
//		$table = Kanban_Db::instance()->prefix() . $this->get_table();
//
//		$since = '';
//		if ( DateTime::createFromFormat('Y-m-d H:i:s', $since_dt) !== FALSE) {
//
//			$since = "
//			AND $table.`modified_dt_gmt` > '$since_dt'
//			";
//
//			if ( is_user_logged_in() ) {
//			$since .= sprintf(
//			" AND $table.`modified_user_id` != %d ",
//			get_current_user_id()
//			);
//			}
//		}
//
//		$rows = $wpdb->get_results(
//			"
//					SELECT $table.*
//					FROM $table
//					WHERE 1=1
//					AND $table.is_active = 1
//					$since
//				",
//			OBJECT_K
//		);
//
//		foreach ( $rows as $row_id => &$row ) {
//			$row = $this->format_data_for_app( $row );
//		}
//
//		return $rows;
//	}

	public function format_data_for_db( $row ) {

		// Filter the data based on allowed fields.
		$row = array_intersect_key( (array) $row, $this->get_fields() );
		ksort( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'id':
					$value = $this->format_int_for_db($value);
					break;
				case 'is_active':
					$value = $this->format_bool_for_db($value);
					break;
				case 'created_dt_gmt':
					$value = $this->format_datetime_for_db($value);
					break;
				case 'created_user_id':
					$value = $this->format_int_for_db($value);
					break;
				case 'modified_dt_gmt':
					$value = $this->format_datetime_for_db($value);
					break;
				case 'modified_user_id':
					$value = $this->format_int_for_db($value);
					break;
			}
		}

		return $row;
	}

	public function format_data_for_app( $row ) {

		if ( empty($row) ) return $row;

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'id':
					$value = $this->format_int_for_app($value);
					break;
				case 'is_active':
					$value = $this->format_bool_for_app($value);
					break;
				case 'created_user_id':
					$value = $this->format_int_for_app($value);
					break;
				case 'created_dt_gmt':
					$value = $this->format_string_for_app($value);
					break;
				case 'modified_user_id':
					$value = $this->format_int_for_app($value);
					break;
				case 'modified_dt_gmt':
					$value = $this->format_string_for_app($value);
					break;
			}
		}

		return $row;
	}

	public function get_fields() {
		return $this->fields;
	} // fields

	public function get_table() {
		return $this->table;
	} // table


	// define the db schema
	public function get_create_table_sql() {

		return "";
	} // get_create_table_sql

	public function format_json_for_app ($value) {

		$newValue = is_object( $value ) || is_array( $value ) ? $value : json_decode( $value );

		// If it created an error, return the original value instead.
		$value = is_null( $newValue ) ? $value : $newValue;

		$value = is_null( $value ) ? array() : $value;

		if ( empty( $value ) ) {
			$value = array();
		}

		return $value;
	}

	public function format_csv_for_app ($value) {
		return is_array( $value ) ? $value : array_map('intval', array_filter( array_map( 'trim', explode( ',', $value ) ) ));
	}

	public function format_bool_for_app ($value) {
		return $value === 1 || $value === true || $value === 'true' || $value === '1' ? true : false;
	}

	public function format_int_for_app ($value) {
		return (int) intval ( $value );
	}

	public function format_float_for_app ($value) {
		return (float) floatval ( $value );
	}

	public function format_string_for_app ($value) {
		return (string) stripslashes_deep($value);
	}

	public function format_json_for_db ($value) {
		return is_object( $value ) || is_array( $value ) ? json_encode( $value ) : $value;
	}

	public function format_bool_for_db ($value) {
		return $value === 1 || $value === true || $value === 'true' || $value === '1' ? 1 : 0;
	}

	public function format_int_for_db ($value) {
		return (int) intval ( $value );
	}

	public function format_float_for_db ($value) {
		return (float) floatval ( $value );
	}

	public function format_string_for_db ($value) {
		return (string) stripslashes_deep($value);
	}

	public function format_datetime_for_db ($value) {

		$format = strlen($value) == 10 ? 'Y-m-d' : 'Y-m-d H:i:s';

		$d = DateTime::createFromFormat($format, $value);
		$is_valid = $d && $d->format($format) == $value;

		return $is_valid ? $value : Date($format);
	}



	/**
	 * get the instance of this class
	 * @link https://stackoverflow.com/a/1818931/38241
	 *
	 * @return object the instance
	 */

	public static function instance() {
		$class = get_called_class();
		if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class();
		}
		return self::$_instances[$class];
	}

}