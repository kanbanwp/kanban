<?php


class Kanban_User_Cap extends Kanban_User {

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
		'user_email'       => '%d',
		'capabilities'     => '%s',
		'is_active'        => '%d',
		'is_admin'         => '%d',
		'board_id'         => '%d'
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'user_caps';


	/**
	 * Add a new board record for a user
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function ajax_add( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board-users') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( ! isset( $data['board_id'] ) ) {
			header( 'HTTP/1.1 401 Missing board id' );

			return false;
		}

		$user = get_user_by( 'id', $data['user_id'] );

		if ( empty( $user ) ) {
			header( 'HTTP/1.1 401 user not found' );

			return false;
		}

		$board = $this->set_row( array(
			'board_id'     => $data['board_id'],
			'user_id'      => $data['user_id'],
			'user_email'   => $user->user_email,
			'capabilities' => isset( $data['capabilities'] ) && is_array( $data['capabilities'] ) ? $data['capabilities'] : array(
				'card',
				'card-read',
				'comment'
			) // Default capabilities.
		) );

		$user = Kanban_User::instance()->get_user( $board->user_id );

		$user = $this->add_caps_to_user( $user, array( $board->board_id => $board ) );

		return $user;
	}


	/**
	 * Add a new admin record for a user
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function ajax_add_admin( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('admin-users') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		$user = get_user_by( 'id', $data['user_id'] );

		if ( empty( $user ) ) {
			header( 'HTTP/1.1 401 user not found' );

			return false;
		}

		return $this->add_admin($user, array('admin-board-create'));
	}

	/**
	 * Update a board record for a user
	 *
	 * @param $data
	 *
	 * @return bool|false|int
	 */
	public function ajax_replace( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board-users') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		$board = $this->get_board_by_user_id( $data['board_id'], $data['user_id'] );

		if ( ! isset( $board->id ) ) {
			header( 'HTTP/1.1 401 user not found' );

			return false;
		}

		$data['id'] = $board->id;

		$row = $this->set_row( $data );

		return $row;
	}


	/**
	 * Update admin record for a user
	 *
	 * @param $data
	 *
	 * @return bool|false|int
	 */
	public function ajax_replace_admin( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('admin-users') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		$board = $this->get_admin_by_user_id( $data['user_id'] );

		if ( ! isset( $board->id ) ) {
			header( 'HTTP/1.1 401 user not found' );

			return false;
		}

		$data['id'] = $board->id;

		$row = $this->set_row( $data );

		return $row;
	}

//
//		global $wpdb;
//
//		$table = Kanban_Db::instance()->prefix() . $this->get_table();
//
//		// Get kanban user records.
//		$rows = $wpdb->get_results(
//			$wpdb->prepare(
//				"
//					SELECT $table.user_id,
//					$table.`board_id`,
//					$table.capabilities,
//					$table.is_admin
//
//					FROM $table
//					WHERE 1=1
//					AND $table.`is_active` = 1
//					AND $table.`board_id` = %d
//				;",
//				$board_id
//			),
//			OBJECT_K
//		);
//
//		if ( empty( $rows ) ) {
//			return array();
//		}
//
//		$user_ids = array_keys( $rows );
//
//		$users = $this->get_users_by_id( $user_ids );
//
//		return $users;
//	}


	public function ajax_delete( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board-users') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( ! isset( $data['user_id'] ) ) {
			header( 'HTTP/1.1 401 Missing id' );

			return false;
		}

		if ( ! isset( $data['board_id'] ) ) {
			header( 'HTTP/1.1 401 Missing board id' );

			return false;
		}

		$row = $this->get_board_by_user_id( $data['board_id'], $data['user_id'] );

		$this->set_row( array(
			'id'        => $row->id,
			'is_active' => 0
		) );

		return true;
	}

	public function ajax_delete_admin( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('admin-users') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( ! isset( $data['user_id'] ) ) {
			header( 'HTTP/1.1 401 Missing id' );

			return false;
		}

		$row = $this->get_board_by_user_id( 0, $data['user_id'] );

		if ( ! empty( $row ) ) {
			$this->set_row( array(
				'id'        => $row->id,
				'is_active' => 0
			) );
		}

		return true;
	}

	/**
	 * Get all users, with caps, for a specific board
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function ajax_get_by_board( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board-users') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( ! isset( $data['board_id'] ) ) {
			return array();
		}

		$board_id = intval( $data['board_id'] );

		$board = Kanban_Board::instance()->get_row($board_id);

		if ( empty( $board ) ) {
			return array();
		}

		$boards = $this->get_boards_by_board_id( $board_id );

		$user_ids = array_keys( $boards );

		$user_ids[] = $board->created_user_id;

		$users = $this->get_users( $user_ids, true );

		return $users;
	} // get_board_by_user_id

	/**
	 * Get all board caps for a single board, returning caps for a bunch of users
	 *
	 * @param $board_id
	 *
	 * @return array
	 */
	public function get_boards_by_board_id( $board_id ) {

		$board_id = intval( $board_id );

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		// Get kanban user records.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT $table.user_id,
					$table.capabilities,
					$table.is_admin
					
					FROM $table
					WHERE 1=1
					AND $table.`is_active` = 1
					AND $table.`board_id` = %d
				;",
				$board_id
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
	}


	public function add_caps_to_user( $user, $boards = array() ) {

		if ( empty( $boards ) ) {
			$boards = Kanban_User_Cap::instance()->get_boards_by_user_id( $user->id );
		}

		foreach ( $boards as $i => &$board ) {
			$is_admin = $board->is_admin;
			unset( $board->is_admin );

			if ( $is_admin ) {
				$user->capabilities->admin = (array) $board->capabilities;
				unset( $boards[ $i ] );
			}
		}

		foreach ( $boards as $board_id => $board ) {
			$user->capabilities->boards[ $board_id ] = $board->capabilities;
		}

		return $user;
	}

	public function add_admin ($wp_user, $capabilities = array()) {

		$board = $this->set_row( array(
			'user_id'      => $wp_user->id,
			'user_email'   => $wp_user->user_email,
			'board_id'     => 0,
			'is_admin'     => true,
			'capabilities' => !empty( $capabilities ) ? $capabilities : array( 'admin-board-create' )
			// Default capabilities.
		) );

		$user = Kanban_User::instance()->get_user( $board->user_id );

		$user = $this->add_caps_to_user( $user, array( $board->board_id => $board ) );

		return $user;
	}

	public function user_has_cap( $user, $cap, $board_id = null ) {

		// Just in case.
		if ( ! isset( $user->id ) || ! isset( $user->capabilities ) ) {
			return false;
		}

		// Check for admin.
		if ( in_array( 'admin', $user->capabilities->admin ) ) {
			return true;
		}

		// Check for admin cap.
		if ( isset($user->capabilities) && in_array( $cap, $user->capabilities->admin ) ) {
			return true;
		}

		// If they got this far and no boardId, try to load it.
		if ( (empty($board_id) || is_null( $board_id )) && isset($user->options->app['current_board']) ) {
			$board_id = $user->options->app['current_board'];
		}

		// If they made it this far and there's no board id, must be false.
		if ( empty($board_id) || is_null( $board_id ) ) {
			return false;
		}

		// Break it up.
		$capArr = explode( '-', $cap );

		// If user created the board, treat them like a board admin.
		$board = Kanban_Board::instance()->get_row( $board_id );

		if ( $board->created_user_id == $user->id && ($capArr[0] == 'board' || $capArr[0] == 'card' || $capArr[0] == 'comment') ) {
			return true;
		}

		// If there's no record, they don't have the cap.
		if ( ! isset( $user->capabilities->boards[ $board_id ] ) ) {
			return false;
		}

		// Check for section admin.
		if ( in_array( $capArr[0], $user->capabilities->boards[ $board_id ] ) ) {
			return true;
		}

		// Check for cap.
		if ( in_array( $cap, $user->capabilities->boards[ $board_id ] ) ) {
			return true;
		}

		// Default to returning false.
		return false;
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
					$table.capabilities,
					$table.is_admin
 
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

	}

	public function get_admin_by_user_id( $user_id, $since_dt = null ) {
		$board = $this->get_board_by_user_id( 0, $user_id, $since_dt );

		$is_admin = $board->is_admin;
		unset( $board->is_admin );

		if ( ! $is_admin ) {
			return false;
		}

		return $board;
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
					$table.capabilities,
					$table.is_admin
					
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
	} // get_boards_by_user_id

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

			if ( is_user_logged_in() ) {
				$since .= sprintf(
					" AND $table.`modified_user_id` != %d ",
					get_current_user_id()
				);
			}
		}

		// Get kanban user records.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT $table.`board_id`,
					$table.capabilities,
					$table.is_admin
					
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
					$table.capabilities,
					$table.is_admin
					
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
	}

	public function get_admin_boards() {
		$boards = $this->get_boards_by_board_id( 0 );

		foreach ( $boards as $i => &$board ) {

			$is_admin = $board->is_admin;
			unset( $board->is_admin );

			if ( ! $is_admin ) {
				unset( $boards[ $i ] );
			}
		}

		return $boards;
	}

	public function format_board_for_user( $row ) {

		$row = parent::format_data_for_app( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'capabilities':
					$value = $this->format_json_for_app( $value );

					// Every user must always have card-read.
					$value[] = 'card-read';
					$value = array_unique($value);
					break;
				case 'is_app':
					$value = $this->format_bool_for_app( $value );
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

					// Every user must always have card-read.
					$value[] = 'card-read';
					$value = $this->format_json_for_db( array_unique($value) );
					break;
				case 'user_id':
					$value = $this->format_int_for_db( $value );
					break;
				case 'user_email':
					$value = $this->format_string_for_db( $value );
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
					user_email varchar(128) DEFAULT '' NOT NULL,
					board_id bigint(20) DEFAULT '0' NOT NULL,
					is_admin BOOLEAN DEFAULT FALSE NOT NULL,
					capabilities text DEFAULT '' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active),
					KEY user_id (user_id)
				)";
	} // get_create_table_sql

}