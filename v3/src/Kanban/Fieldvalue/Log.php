<?php


class Kanban_Fieldvalue_Log extends Kanban_Abstract {

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
		'fieldvalue_id'    => '%d',
		'content'          => '%s',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'fieldvalue_log';


	public function set_row( $data ) {
		return parent::set_row( $data );
	}


	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_app( $row );

		if ( empty( $row ) ) {
			return array();
		}

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'content':

					$field = Kanban_Field::instance()->get_row( $row->field_id );
					$class = Kanban_Field::instance()->get_fieldtype_class( $field->field_type );

					$value = $class::instance()->format_content_for_app( $value );
					break;
				case 'fieldvalue_id':
					$value = $this->format_int_for_app( $value );
					break;
			}
		}

		return $row;
	}

	public function format_data_for_db( $row ) {

		$row = parent::format_data_for_db( $row );

		if ( empty( $row ) ) {
			return array();
		}

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'content':

					$field = Kanban_Field::instance()->get_row_by_fieldvalue_id( $row['fieldvalue_id'] );
					$class = Kanban_Field::instance()->get_fieldtype_class( $field->field_type );

					$value = $class::instance()->format_content_for_db( $value );

					break;
				case 'fieldvalue_id':
					$value = $this->format_int_for_db( $value );
					break;
			}
		}

		return $row;
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
					fieldvalue_id bigint(20) DEFAULT '0' NOT NULL,
					content text DEFAULT '' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql

}