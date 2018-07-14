<?php


class Kanban_User_Option extends Kanban_User {

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
		'options'          => '%s',
		'is_active'        => '%d',
		'is_app'           => '%d',
		'board_id'         => '%d',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'user_options';


	public function ajax_replace( $data ) {

		if ( ! isset( $data['option'] ) ) {
			header( 'HTTP/1.1 401 Missing option' );

			return false;
		}

		if ( ! isset( $data['value'] ) ) {
			header( 'HTTP/1.1 401 Missing value' );

			return false;
		}

		if ( ! isset( $data['board_id'] ) ) {
			header( 'HTTP/1.1 401 Missing board id' );

			return false;
		}

		$board = $this->get_board_by_user_id( $data['board_id'], get_current_user_id() );

		if ( empty( $board ) || $board->board_id == 0 ) {
			$board           = (object) array();
			$board->board_id = $data['board_id'];
			$board->is_app   = false;
			$board->user_id  = get_current_user_id();
			$board->options  = array();
		}

		if ( ! is_array( $board->options ) ) {
			$board->options = array();
		}

		$board->options[ $data['option'] ] = $data['value'];
		$board->options                    = $this->format_options_for_db( $board->options );

		$row = $this->set_row( $board );

		return $row;
	}

	/**
	 * Update app record for a user
	 *
	 * @param $data
	 *
	 * @return bool|false|int
	 */
	public function ajax_replace_app( $data ) {

		if ( ! isset( $data['option'] ) ) {
			header( 'HTTP/1.1 401 Missing option' );

			return false;
		}

		if ( ! isset( $data['value'] ) ) {
			header( 'HTTP/1.1 401 Missing value' );

			return false;
		}

		$row = $this->replace_app( $data['option'], $data['value'] );

		return $row;
	}

	public function replace_app ($option, $value) {
		$board = $this->get_app_by_user_id( get_current_user_id() );

		if ( empty( $board ) || $board->board_id != 0 ) {
			$board          = (object) array();
			$board->board   = 0;
			$board->is_app  = true;
			$board->user_id = get_current_user_id();
			$board->options = array();
		}

		$board->options[ $option ] = $value;
		$board->options = $this->format_app_options_for_db( $board->options );

		$row = $this->set_row( $board );

		return $row;
	}

//	public function set_row( $data ) {
//		return parent::set_row( $data );
//	}

//	public function ajax_replace_app_all ( $data ) {
//
//		if ( !isset($data['options']) ) {
//			header( 'HTTP/1.1 401 Missing options' );
//			return false;
//		}
//
//		$board = $this->get_app_by_user_id(get_current_user_id());
//
//		if ( empty($board) || $board->board_id != 0 ) {
//
//			$board = (object) array();
//
//			$board->board = 0;
//			$board->is_app = true;
//			$board->user_id = get_current_user_id();
//			$board->options = $data['options'];
//		}
//
//		$board->options = array_merge($board->options, $data['options']);
//
//		$board->options = $this->format_app_options_for_db($board->options);
//
//		$row = $this->set_row( $board );
//
//		return $row;
//	}

	public function add_options_to_user( $user, $boards = array() ) {

		if ( empty( $boards ) ) {
			$boards = Kanban_User_Option::instance()->get_boards_by_user_id( $user->id );
		}

		foreach ( $boards as $i => $board ) {

			if ( $board->is_app ) {
				$user->options->app = (array) $board->options;
				unset( $boards[ $i ] );
			}
		}

		foreach ( $boards as $board_id => $board ) {
			$user->options->boards[ $board_id ] = $board->options;
		}

		return $user;
	}

	public function get_row( $id ) {

		$id = intval( $id );

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		// Get kanban user records.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.user_id,
					$table.board_id,
					$table.options,
					$table.is_app
					
					FROM $table
					WHERE 1=1
					AND $table.`id` = %d
				;",
				$id
			),
			OBJECT
		);

		if ( empty( $row ) ) {
			return (object) array();
		}

		$row = $this->format_board_for_user( $row );

		return $row;
	}

	public function get_board_by_user_id( $board_id, $user_id, $since_dt = null ) {
		global $wpdb;

		$user_id  = intval( $user_id );
		$board_id = intval( $board_id );

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$since = '';
		if ( DateTime::createFromFormat( 'Y-m-d H:i:s', $since_dt ) !== false ) {

			$since = "
			AND $table.`modified_dt_gmt` > '$since_dt' 
			";

//			if ( is_user_logged_in() ) {
//				$since .= sprintf(
//					" AND $table.`modified_user_id` != %d ",
//					get_current_user_id()
//				);
//			}
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.id,
					$table.user_id,
					$table.`board_id`,
					$table.options,
					$table.is_app
 
					FROM $table
					WHERE 1=1
					AND $table.user_id = %d
					AND $table.board_id = %d
					AND $table.is_active = 1
					$since
				",
				$user_id,
				$board_id
			),
			OBJECT
		);

		if ( empty( $row ) ) {
			return (object) array();
		}

		$row = $this->format_board_for_user( $row );

		return $row;

	} // get_board_by_user_id


	/**
	 * Get all board caps for a single user, returning all caps across all boards
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public function get_boards_by_user_id( $user_id, $since_dt = null ) {

		$user_id = intval( $user_id );

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$since = '';
		if ( DateTime::createFromFormat( 'Y-m-d H:i:s', $since_dt ) !== false ) {

			$since = "
			AND $table.`modified_dt_gmt` > '$since_dt' 
			";

//			if ( is_user_logged_in() ) {
//				$since .= sprintf(
//					" AND $table.`modified_user_id` != %d ",
//					get_current_user_id()
//				);
//			}
		}

		// Get kanban user records.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT $table.`board_id`,
					$table.options,
					$table.is_app
					
					FROM $table
					WHERE 1=1
					AND $table.`is_active` = 1
					AND $table.`user_id` = %d
					$since
				;",
				$user_id
			),
			OBJECT_K
		);

		if ( empty( $rows ) ) {
			return array();
		}

		foreach ( $rows as &$row ) {
			$row = $this->format_board_for_user( $row );
		}

		return $rows;
	} // get_boards_by_user_id

	/**
	 * Get all board caps for a multiple user, returning all caps across all boards
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public function get_boards_by_user_ids( $user_ids ) {

		$user_ids = array_map( 'intval', $user_ids );

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		// Get kanban user records.
		$rows = $wpdb->get_results(
			sprintf(
				"
					SELECT $table.`board_id`,
					$table.`user_id`,
					$table.options,
					$table.is_app
					
					FROM $table
					WHERE 1=1
					AND $table.`is_active` = 1
					AND $table.`user_id` IN (%s)
				;",
				implode( ',', $user_ids )
			),
			OBJECT
		);

		if ( empty( $rows ) ) {
			return array();
		}

		foreach ( $rows as &$row ) {
			$row = $this->format_board_for_user( $row );
		}

		return $rows;
	} // get_boards_by_user_id

	public function get_app_by_user_id( $user_id, $since_dt = null ) {
		$board = $this->get_board_by_user_id( 0, $user_id, $since_dt );

		$is_app = $board->is_app;
//		unset( $board->is_app );

		if ( ! $is_app ) {
			return false;
		}

		return $board;

	}

	public function format_app_options_for_db( $options ) {
		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'current_board':
					$value = $this->format_int_for_db( $value );
					break;
				case 'do_live_updates_check':
					$value = $this->format_bool_for_db( $value );
					break;
				case 'live_updates_check_interval':
					$value = $this->format_int_for_db( $value );
					break;
				case 'do_notifications':
					$value = $this->format_bool_for_db( $value );
					break;
				case 'first_day_of_week':
					$value = $this->format_string_for_db( $value );
					break;
				case 'date_view_format':
					$value = $this->format_string_for_db( $value );
					break;
				default:
					unset( $options[ $key ] );
					break;
			}
		}

		return $options;
	}

	public function format_app_options_for_app( $options ) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'current_board':
					$value = $this->format_int_for_app( $value );
					break;
				case 'do_live_updates_check':
					$value = $this->format_bool_for_app( $value );
					break;
				case 'live_updates_check_interval':
					$value = $this->format_int_for_app( $value );
					break;
				case 'do_notifications':
					$value = $this->format_bool_for_app( $value );
					break;
				case 'first_day_of_week':
					$value = $this->format_string_for_app( $value );
					break;
				case 'date_view_format':
					$value = $this->format_string_for_app( $value );
					break;

			}
		}

		return (array) $options;
	}

	public function format_options_for_db( $options ) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'view':
					$value = $this->format_json_for_db( $value );
					break;
				case 'show_task_id':
					$value = $this->format_bool_for_db( $value );
					break;
				default:
					unset( $options[ $key ] );
					break;
			}
		}

		return $options;
	}

	public function format_options_for_app( $options ) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'view':
					$value = $this->format_json_for_app( $value );
					break;
				case 'show_task_id':
					$value = $this->format_bool_for_app( $value );

			}
		}

		return (array) $options;
	}


	public function format_board_for_user( $row ) {

		$row = parent::format_data_for_app( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'options':
					$value = $this->format_json_for_app( $value );

					if ( $row->is_app == 1 ) {
						$value = $this->format_app_options_for_app( $value );
					} else {
						$value = $this->format_options_for_app( $value );
					}

					break;
				case 'is_app':
					$value = $this->format_bool_for_app( $value );
					break;
			}
		}

		return $row;
	} // format_board_for_user


	public function format_data_for_db( $row ) {

		$row = parent::format_data_for_db( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'options':
					if ( $row['is_app'] == 1 ) {
						$value = $this->format_app_options_for_db( $value );
					} else {
						$value = $this->format_options_for_db( $value );
					}

					$value = $this->format_json_for_db( $value );

					break;
				case 'user_id':
					$value = $this->format_int_for_db( $value );
					break;
				case 'board_id':
					$value = $this->format_int_for_db( $value );
					break;
				case 'is_app':
					$value = $this->format_bool_for_db( $value );
					break;
			}
		}

		return $row;
	}

	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_db( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'options':
					$value = $this->format_json_for_app( $value );
					$value = $this->format_options_for_app( $value );
					break;
				case 'user_id':
					$value = $this->format_int_for_app( $value );
					break;
				case 'board_id':
					$value = $this->format_int_for_app( $value );
					break;
				case 'is_app':
					$value = $this->format_bool_for_app( $value );
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
					user_id bigint(20) DEFAULT '0' NOT NULL,
					board_id bigint(20) DEFAULT '0' NOT NULL,
					is_app BOOLEAN DEFAULT FALSE NOT NULL,
					options text DEFAULT '' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active),
					KEY user_id (user_id)
				)";
	} // get_create_table_sql

}