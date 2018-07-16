<?php


class Kanban_Lane extends Kanban_Abstract {


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
		'cards_order'      => '%s',
		'is_active'        => '%d',
		'board_id'         => '%d',
		'options'          => '%s',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'lanes';


	public function ajax_cards_order ($data) {

		if ( !Kanban_User::instance()->current_user_has_cap('card-write') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['lane_id']) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		if ( !isset($data['cards_order']) || !isset($data['lane_id']) ) {
			header( 'HTTP/1.1 400 Missing params' );
			return false;
		}

		$row = $this->update_cards_order($data['lane_id'], $data['cards_order'] );

		return $row;
	}

	public function ajax_add ($data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['board_id']) ) {
			header( 'HTTP/1.1 401 Missing board id' );
			return false;
		}

		$row = $this->set_row( array(
			'board_id' => $data['board_id']
		) );

		return $row;
	}

	public function ajax_replace ($data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['lane_id']) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		$data['id'] = $data['lane_id'];

		$row = $this->set_row($data);

		return $row;
	}

	public function ajax_delete ($data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['lane_id']) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		$row = $this->get_row($data['lane_id']);

		$row = $this->set_row( array(
			'id' => $data['lane_id'],
			'is_active' => 0
		) );

		$row = $this->get_row($data['lane_id']);

		return $row;
	}

	public function update_cards_order ($lane_id, $cards_order) {
		$row = $this->set_row(array(
			'id' => $lane_id,
			'cards_order' => $cards_order
		));

		return $row;
	}

	public function get_results_by_boards ($board_ids, $since_dt = null) {
		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		if ( is_numeric($board_ids) || is_string($board_ids) ) {
			$board_ids = array($board_ids);
		}

		$in = sprintf(
			" AND $table.`board_id` IN (%s) ",
			implode(',', $board_ids)
		);

		$since = '';
		$isActive = "AND $table.is_active = 1";
		if ( DateTime::createFromFormat('Y-m-d H:i:s', $since_dt) !== FALSE) {

			$since = "
			AND $table.`modified_dt_gmt` > '$since_dt' 
			";

			$isActive = '';

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
					$isActive
					$in
					$since
				;",
			OBJECT_K
		);

		foreach ( $rows as $row_id => &$row ) {
			$row = $this->format_data_for_app( $row );
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

	public function set_row( $data ) {
		return parent::set_row( $data );
	}

	public function get_results_by_ids ($lane_ids = array()) {

		$lane_ids = array_filter($lane_ids, 'intval');

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$in = sprintf(
			" AND $table.`id` IN (%s) ",
			implode(',', $lane_ids)
		);

		$rows = $wpdb->get_results(
			"
					SELECT $table.* 
					FROM $table
					WHERE 1=1
					AND $table.is_active = 1
					$in
				;",
			OBJECT_K
		);

		foreach ( $rows as $row_id => &$row ) {
			$row = $this->format_data_for_app( $row );
		}

		return $rows;
	}

	public function format_options_for_db($options) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'color':
					$value = $this->format_string_for_db($value);
					break;
				default:
					unset($options[$key]);
					break;
			}
		}

		return $options;
	}

	public function format_options_for_app($options) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'color':
					$value = $this->format_string_for_app($value);
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
				case 'cards_order':
					$value = $this->format_json_for_app($value);
					$value = array_map('intval', array_filter(array_unique($value)));
					break;
				case 'board_id':
					$value = $this->format_int_for_app($value);
					break;
				case 'options':
					$value = $this->format_json_for_app($value);
					$value = $this->format_options_for_app($value);
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
				case 'cards_order':
					$value = array_map('strval', array_filter(array_unique($value)));
					$value = $this->format_json_for_db($value);
					break;
				case 'options':
					$value = $this->format_options_for_db($value);
					$value = $this->format_json_for_db($value);
					break;
			}
		}

		return $row;
	} // format_data_for_db


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
					cards_order text DEFAULT '' NOT NULL,
					board_id bigint(20) DEFAULT '0' NOT NULL,
					options text DEFAULT '' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql



}