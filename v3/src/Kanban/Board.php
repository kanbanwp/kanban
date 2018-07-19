<?php


class Kanban_Board extends Kanban_Abstract {

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
		'lanes_order'      => '%s',
		'fields_order'     => '%s',
		'is_active'        => '%d',
		'is_public'        => '%d',
		'options'          => '%s',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'boards';


	public function ajax_replace( $data ) {
		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( ! isset( $data['board_id'] ) ) {
			header( 'HTTP/1.1 401 Missing board id' );

			return false;
		}

		$data['id'] = $data['board_id'];

		$row = $this->set_row( $data );

		return $row;
	}

	public function ajax_add( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('admin-board-create') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		$row = $this->set_row( array() );

		// Add a title field.
		$field = Kanban_Field::instance()->set_row( array (
			'board_id' => $row->id,
			'field_type' => 'title',
			'label' => __('Title', 'kanban'),
			'options' => array(
				'placeholder' => __('Add a title', 'kanban')
			)
		) );

		// Update the board with the new field.
		$row = Kanban_Board::instance()->set_row(array(
			'id' => $row->id,
			'fields_order' => array($field->id)
		));

		return $row;
	}

	public function ajax_delete ( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('admin-board-create') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['board_id']) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		$row = $this->get_row($data['board_id']);

		$this->set_row( array(
			'id' => $data['board_id'],
			'is_active' => 0
		) );

		return true;

	} // delete

	public function ajax_get_data( $data ) {

		if ( ! isset( $data['board_id'] ) ) {
			header( 'HTTP/1.1 401 Missing board id' );

			return false;
		}

		$board = $this->get_row( $data['board_id'] );

		if ( empty( $board ) ) {
			header( 'HTTP/1.1 401 Board not found' );

			return false;
		}

		$user_can_view = false;

		if ( $board->is_public ) {
			$user_can_view = true;
		}

		if ( ! $user_can_view && is_user_logged_in() ) {

			if ( $board->created_user_id == get_current_user_id() ) {
				$user_can_view = true;
			}

			if ( ! $user_can_view && Kanban_User::current_user_has_cap('admin-board-view-all') ) {
					$user_can_view = true;
			}

			if ( ! $user_can_view ) {
				$current_user = Kanban_User::instance()->get_current();

				if ( isset( $current_user->capabilities->boards[ $board->id ] ) ) {
					$user_can_view = true;
				}
			}
		}

		if ( ! $user_can_view ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );

			return false;
		}

		$data = (object) array();

		$data->boards = array($board->id => $board);

		$board_ids   = array( $board->id );
		$data->lanes = Kanban_Lane::instance()->get_results_by_boards( $board_ids );
		$data->fields = Kanban_Field::instance()->get_results_by_boards( $board_ids );

		$data->cards = array();

		if ( ! empty( $data->lanes ) ) {
			$lane_ids    = array_keys( $data->lanes );
			$data->cards = Kanban_Card::instance()->get_results_by_lanes( $lane_ids );
		}

		$data->fieldvalues = array();

		if ( ! empty( $data->cards ) ) {
			$card_ids          = array_keys( $data->cards );
			$data->fieldvalues = Kanban_Fieldvalue::instance()->get_results_by_cards( $card_ids );
		}

		return $data;
	}

	public function ajax_replace_option ( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( ! isset( $data['option'] ) ) {
			header( 'HTTP/1.1 401 Missing option' );

			return false;
		}

		if ( ! isset( $data['value'] ) ) {
			header( 'HTTP/1.1 401 Missing value' );

			return false;
		}


		$board = $this->get_row( $data['board_id'] );

		if ( empty( $board ) ) {
			header( 'HTTP/1.1 401 Board not found' );

			return false;
		}

		if ( ! is_array( $board->options ) ) {
			$board->options = array();
		}

		if ( isset($board->{ $data['option'] }) ) {
			$board->{ $data['option'] } = $data['value'];
		} else {
			$board->options[ $data['option'] ] = $data['value'];
			$board->options                    = $this->format_options_for_db( $board->options );
		}

		$row = $this->set_row( $board );

		return $row;
	}

	public function get_results( $since_dt = null ) {
		global $wpdb;

		$table       = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_users = Kanban_Db::instance()->prefix() . Kanban_User_Cap::instance()->get_table();

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

		// Everyone can see public boards.
		$where = " $table.is_public = 1 ";

		if ( is_user_logged_in() ) {

			$current_user = Kanban_User::instance()->get_current();

			if ( is_array( $current_user->capabilities->boards ) ) {

				$where .= sprintf( " OR $table.created_user_id = %d ",
					get_current_user_id()
				);

				// Get boards they have access to.
				$board_ids = array_keys( $current_user->capabilities->boards );

				if ( ! empty( $board_ids ) ) {
					$where .= sprintf( " OR $table.id IN ('%s') ",
						implode( "','", $board_ids )
					);
				}
			}
		}

		$where = sprintf(
			' AND (%s) ',
			$where
		);

		// If admin, clear where so they see all boards.
		if ( is_user_logged_in() ) {

			$current_user = Kanban_User::instance()->get_current();

			if ( in_array( 'admin', $current_user->capabilities->admin ) ) {
				$where = '';
			}
		}

		$rows = $wpdb->get_results(
			"
					SELECT $table.*,
					(
						SELECT GROUP_CONCAT($table_users.user_id)
						FROM $table_users
						WHERE $table_users.`board_id` = $table.id
						AND $table_users.`is_active` = 1
					) as users 
					
					FROM $table
					WHERE 1=1
					AND $table.is_active = 1
					$where
					$since
				;",
			OBJECT_K
		);

		foreach ( $rows as $row_id => &$row ) {
			$row = $this->format_data_for_app( $row );
		}

		return $rows;
	}

	public function get_results_by_ids( $board_ids = array() ) {

		$board_ids = array_filter( $board_ids, 'intval' );

		global $wpdb;

		$table       = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_users = Kanban_Db::instance()->prefix() . Kanban_User_Cap::instance()->get_table();

		$in = sprintf(
			" AND $table.`id` IN (%s) ",
			implode( ',', $board_ids )
		);

		$rows = $wpdb->get_results(
			"
					SELECT $table.*,
					(
						SELECT GROUP_CONCAT($table_users.user_id)
						FROM $table_users
						WHERE $table_users.`board_id` = $table.id
						AND $table_users.`is_active` = 1
					) as users 
					
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

	public function get_row( $id ) {
		global $wpdb;

		$table       = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_users = Kanban_Db::instance()->prefix() . Kanban_User_Cap::instance()->get_table();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.*,
					(
						SELECT GROUP_CONCAT($table_users.user_id)
						FROM $table_users
						WHERE $table_users.`board_id` = $table.id
						AND $table_users.`is_active` = 1
					) as users 
					
					FROM $table
					WHERE 1=1
					AND $table.id = %d
				",
				$id
			),
			OBJECT
		);

		if ( empty( $row ) ) {
			return (object) array();
		}

		$row = $this->format_data_for_app( $row );

		return $row;
	}

	public function set_row( $data ) {
		return parent::set_row( $data );
	}

	public function format_options_for_db($options) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'card_creator_delete_card':
					$value = $this->format_bool_for_db($value);
					break;
				case 'card_creator_move_card':
					$value = $this->format_bool_for_db($value);
					break;
				case 'users_list_mention':
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
				case 'card_creator_delete_card':
					$value = $this->format_bool_for_app($value);
					break;
				case 'card_creator_move_card':
					$value = $this->format_bool_for_app($value);
					break;
				case 'users_list_mention':
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
				case 'lanes_order':
					$value = $this->format_json_for_app($value);
					$value = array_map('intval', array_filter(array_unique($value)));
					break;
				case 'fields_order':
					$value = $this->format_json_for_app($value);
					$value = array_map('intval', array_filter(array_unique($value)));
					break;
				case 'is_public':
					$value = $this->format_bool_for_app($value);
					break;
				case 'options':
					$value = $this->format_json_for_app($value);
					$value = $this->format_options_for_app($value);
					break;
				case 'users':
					$value = $this->format_csv_for_app($value);
					$value[] = $row->created_user_id;
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
				case 'lanes_order':
					$value = array_map('strval', array_filter(array_unique($value)));
					$value = $this->format_json_for_db($value);
					break;
				case 'fields_order':
					$value = array_map('strval', array_filter(array_unique($value)));
					$value = $this->format_json_for_db($value);
					break;
				case 'is_public':
					$value = $this->format_bool_for_db($value);
					break;
				case 'options':
					$value = $this->format_options_for_db($value);
					$value = $this->format_json_for_db($value);
					break;
			}
		}

		return $row;
	}

	public function get_label ($board) {

		if ( is_numeric($board) ) {
			$board = $this->get_row($board);
		}

		if ( isset($board->label) && !empty($board->label) ) {
			return $board->label;
		}

		return __('New board');
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
					lanes_order text DEFAULT '' NOT NULL,
					fields_order text DEFAULT '' NOT NULL,
					is_public BOOLEAN DEFAULT FALSE NOT NULL,
					options text DEFAULT '' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql


}