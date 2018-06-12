<?php


class Kanban_Usergroup extends Kanban_Abstract {

	/**
	 * @var array Database table fields and types for filtering.
	 */
	protected $fields = array(
		'id'               => '%d',
		'created_dt_gmt'   => '%s',
		'created_user_id'  => '%s',
		'modified_dt_gmt'  => '%s',
		'modified_user_id' => '%d',
		'user_id'          => '%d',
		'capabilities'     => '%s',
		'label'            => '%s',
		'is_active'        => '%d',
		'board_id'         => '%d',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'usergroups';


	public function ajax_add ($data ) {

		$row = $this->set_row($data);

		return $row;
	}

	public function ajax_replace ($data ) {

		if ( !isset($data['usergroup_id']) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		$data['id'] = $data['usergroup_id'];

		$row = $this->set_row($data);

		return $row;
	}

	// For post requests.
	public function get_all () {
		return self::get_results();
	}


	public function format_data_for_app( $row ) {

		if ( empty($row) ) return $row;

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'capabilities':
					$value = is_array($value) ? $value : array_unique(array_filter( array_map( 'trim', explode( ',', $value ) ) ));
					break;
				case 'is_active':
					$value = $value == 1 ? true : false;
					break;
			}
		}

		return $row;
	}

	public function format_data_for_db( $row ) {

		// Filter the data based on allowed fields.
		$row = array_intersect_key( $row, $this->get_fields() );
		ksort( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'capabilities':
					$value = is_array( $value ) ? implode( ',', array_unique( $value ) ) : $value;
					break;
				case 'id':
					$value = intval ( $value );
					break;
				case 'is_active':
					$value = $value == 1 || (bool) $value == true ? true : false;
					break;

			}
		}

		return $row;
	}

	public function ajax_delete( $id ) {

		if ( is_array($id) ) {
			$id = $id['id'];
		}

		$id = intval ( $id);

		if ( ! is_int($id) ) {
			header( 'HTTP/1.1 400 Incorrect record id' );
			return false;
		}

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$rowCount = $wpdb->update(
			$table,
			array(
				'is_active' => 0
			),
			array(
				'id' => $id
			),
			array( '%d' ),
			array( '%d' )
		);

		return $rowCount == 1 ? TRUE : FALSE;
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
					capabilities text DEFAULT '' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql


}