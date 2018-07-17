<?php


class Kanban_Field extends Kanban_Abstract {


	/**
	 * @var array Database table fields and types for filtering.
	 */
	protected $fields = array(
		'id'               => '%d',
		'created_dt_gmt'   => '%s',
		'created_user_id'  => '%s',
		'modified_dt_gmt'  => '%s',
		'modified_user_id' => '%d',
		'label'            => '%s',
		'field_type'       => '%s',
		'options'          => '%s',
		'board_id'         => '%d',
		'is_active'        => '%d',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'fields';

	public function ajax_add( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['board_id']) ) {
			header( 'HTTP/1.1 401 Missing board id' );
			return false;
		}

		$row = $this->set_row( array(
			'board_id' => $data['board_id'],
			'field_type' => $data['field_type'],
		) );

		return $row;
	}

	public function ajax_replace( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['field_id']) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		$data['id'] = $data['field_id'];

		$row = $this->set_row( $data );

		return $row;
	}

	public function ajax_replace_option ( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['option']) ) {
			header( 'HTTP/1.1 401 Missing option' );
			return false;
		}

		if ( !isset($data['value']) ) {
			header( 'HTTP/1.1 401 Missing value' );
			return false;
		}

		if ( !isset($data['field_id']) ) {
			header( 'HTTP/1.1 401 Missing field id' );
			return false;
		}

		$field = $this->get_row( $data['field_id'] );

		if ( empty($field) ) {
			header( 'HTTP/1.1 401 Field not found' );
			return false;
		}

		if ( isset($field->{$data['option']}) ) {
			$field->{$data['option']} = $data['vale'];
		} else {
			$field->options[$data['option']] = $data['value'];
			$field->options = $this->format_options_for_db($field->options);
		}

		$row = $this->set_row( $field );

		return $row;
	}

	public function ajax_delete ($data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['field_id']) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		$row = $this->get_row($data['field_id']);

		$row = $this->set_row( array(
			'id' => $data['field_id'],
			'is_active' => 0
		) );

		$row = $this->get_row($data['field_id']);

		return $row;
	}

	public function get_results_by_boards( $board_ids, $since_dt = null ) {
		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		if ( is_numeric( $board_ids ) || is_string( $board_ids ) ) {
			$board_ids = array( $board_ids );
		}

		$in = sprintf(
			" AND $table.`board_id` IN (%s) ",
			implode( ',', $board_ids )
		);

		$since = '';
		if ( DateTime::createFromFormat( 'Y-m-d H:i:s', $since_dt ) !== false ) {

			$since = "
			AND $table.`modified_dt_gmt` > '$since_dt' 
			";

			if ( is_user_logged_in() ) {
				$since .= sprintf(
					" AND $table.`modified_user_id` != %d ",
					get_current_user_id()
				);
			}
		}

		$rows = $wpdb->get_results(
			"
					SELECT $table.* 
					FROM $table
					WHERE 1=1
					AND $table.is_active = 1
					$in
					$since
				;",
			OBJECT_K
		);

		foreach ( $rows as $row_id => &$row ) {
			$row = $this->format_data_for_app( $row );

			// Remove hidden fields if current user does not have card-read.
			if ( isset($row->options['is_hidden']) && $row->options['is_hidden'] && ! Kanban_User::instance()->current_user_has_cap('card-read') ) {
				unset( $rows[$row_id]);
			}
		}

		return $rows;
	}


	public function get_row ($id ) {

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.* 
					FROM $table
					WHERE 1=1
					AND $table.id = %d
				;",
				$id
			),
			OBJECT
		);

		$row = $this->format_data_for_app( $row );

		return $row;
	}

	public function get_row_by_fieldvalue_id ($fieldvalue_id ) {

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();
		$fieldvalue_table = Kanban_Db::instance()->prefix() . Kanban_Fieldvalue::instance()->get_table();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.* 
					FROM $table
					
					JOIN $fieldvalue_table
					ON $table.id = $fieldvalue_table.field_id
					
					WHERE 1=1
					AND $fieldvalue_table.id = %d
				;",
				$fieldvalue_id
			),
			OBJECT
		);

		$row = $this->format_data_for_app( $row );

		return $row;
	}

	public function set_row( $data ) {
		return parent::set_row( $data );
	}

	public function format_options_for_db($options) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'is_hidden':
					$value = $this->format_bool_for_db($value);
					break;
				case 'placeholder':
					$value = $this->format_string_for_db($value);
					break;
				case 'default_content':
					$value = $this->format_string_for_db($value);
					break;
				case 'view_layout_width':
					$value = $this->format_int_for_db($value);
					break;
				default:
					unset($options[$key]);
					break;
			}
		}

		return (array) $options;
	}

	public function format_options_for_app($options) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'is_hidden':
					$value = $this->format_bool_for_app($value);
					break;
				case 'placeholder':
					$value = $this->format_string_for_app($value);
					break;
				case 'default_content':
					$value = $this->format_string_for_app($value);
					break;
				case 'view_layout_width':
					$value = $this->format_int_for_app($value);
					break;
				default:
					unset($options[$key]);
					break;
			}
		}

		return (array) $options;
	}

	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_app($row);

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'board_id':
					$value = $this->format_int_for_app($value);
					break;
				case 'field_type':
					$value = $this->format_string_for_app($value);
					break;
				case 'label':
					$value = $this->format_string_for_app($value);
					break;
				case 'options':
					$value = $this->format_json_for_app($value);

					$class = __CLASS__ . '_' . ucwords($row->field_type);
					if ( !class_exists($class) ) {
						$class = __CLASS__;
					}

					$value = $class::instance()->format_options_for_app( (array) $value);

					break;
			}
		}

		return $row;
	}

	public function format_data_for_db( $row ) {

		$row = parent::format_data_for_db($row);

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'board_id':
					$value = $this->format_int_for_db($value);
					break;
				case 'field_type':
					$value = $this->format_string_for_db($value);
					break;
				case 'label':
					$value = $this->format_string_for_db($value);
					break;
				case 'options':
					$value = $this->format_options_for_db($value);
					$value = $this->format_json_for_db($value);
					break;
			}
		}

		return $row;
	}

	public function format_content_for_db($content) {

		$content = $this->format_string_for_db($content);

		return $content;
	}

	public function format_content_for_app($content) {

		$content = $this->format_string_for_app($content);

		return $content;
	}

	public function get_fieldtype_class ($field_type) {
		$class = __CLASS__;
		if ( class_exists(__CLASS__ . '_' . ucwords($field_type)) ) {
			$class = __CLASS__ . '_' . ucwords($field_type);
		}

		return $class;
	}

	public function get_label ($field) {

		if ( is_numeric($field) ) {
			$field = $this->get_row($field);
		}

		if ( isset($field->label) && !empty($field->label) ) {
			return $field->label;
		}

		if ( isset($field->field_type) && !empty($field->field_type) ) {
			return $field->field_type;
		}

		return __('New field');
	}


	public function get_field_types() {
		$path = Kanban::instance()->settings()->path . '/app/src/Field/';

		$field_types = array();
		foreach ( glob( $path . "*.js" ) as $filename ) {
			$basename = basename( $filename, '.js' );

			if ( substr( $basename, 0, 5 ) == 'index' ) {
				continue;
			}

			$field_types[] = $basename;
		}

		return $field_types;
	}

	// define the db schema
	public function get_create_table_sql() {

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		return "CREATE TABLE $table (
					id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					is_active BOOLEAN DEFAULT TRUE NOT NULL,
					created_dt_gmt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					created_user_id bigint(20) DEFAULT '0' NOT NULL,
					modified_dt_gmt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					modified_user_id bigint(20) DEFAULT '0' NOT NULL,
					label varchar(128) DEFAULT '' NOT NULL,
					field_type varchar(128) DEFAULT '' NOT NULL,
					options text DEFAULT '' NOT NULL,
					board_id bigint(20) DEFAULT '0' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql


}