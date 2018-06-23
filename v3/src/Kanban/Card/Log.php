<?php


class Kanban_Card_Log extends Kanban_Abstract {

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
		'lane_id'          => '%d',
		'card_id'          => '%d',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'card_log';


	public function set_row( $data ) {
		return parent::set_row( $data );
	}


	public function format_data_for_db( $row ) {

		$row = parent::format_data_for_db($row);

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'lane_id':
					$value = $this->format_int_for_db($value);
					break;
			}
		}

		return $row;
	} // format_data_for_db

	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_app($row);

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'lane_id':
					$value = $this->format_int_for_app($value);
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
					card_id bigint(20) DEFAULT '0' NOT NULL,
					lane_id bigint(20) DEFAULT '0' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql

}