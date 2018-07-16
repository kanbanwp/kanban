<?php


class Kanban_Card extends Kanban_Abstract {

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
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'cards';

	public function ajax_add( $data ) {

		if ( ! Kanban_User::instance()->current_user_has_cap( 'card-write' ) ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );

			return false;
		}

		if ( ! isset( $data['board_id'] ) ) {
			header( 'HTTP/1.1 401 Missing board id' );

			return false;
		}

		if ( ! isset( $data['lane_id'] ) ) {
			header( 'HTTP/1.1 401 Missing lane id' );

			return false;
		}

		$row = $this->set_row( array(
			'board_id' => $data['board_id'],
			'lane_id'  => $data['lane_id']
		) );

		// Store the initial card lane.
		Kanban_Card_Log::instance()->set_row( array(
			'card_id' => $row->id,
			'lane_id' => $row->lane_id
		) );

		// Make sure the user who created the card is following the card.
		Kanban_Card_User::instance()->add_current_user_to_card( $row->id );

		return $row;
	}

	public function set_row( $data ) {
		return parent::set_row( $data );
	}

	public function ajax_replace( $data ) {

		if ( ! Kanban_User::instance()->current_user_has_cap( 'card-write' ) ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );

			return false;
		}

		if ( ! isset( $data['card_id'] ) ) {
			header( 'HTTP/1.1 401 Missing id' );

			return false;
		}

		$card = $this->get_row( $data['card_id'] );

		if ( empty( $card ) ) {
			header( 'HTTP/1.1 401 Card not found' );

			return false;
		}

		// If the card has moved lanes, store it.
		if ( isset( $data['lane_id'] ) && $data['lane_id'] != $card->lane_id ) {
			Kanban_Card_Log::instance()->set_row( array(
				'card_id' => $data['card_id'],
				'lane_id' => $data['lane_id']
			) );
		}

		$data['id'] = $data['card_id'];

		$row = $this->set_row( $data );

		return $row;
	}

	public function get_row( $id ) {
		global $wpdb;

		$table             = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_fieldvalues = Kanban_Db::instance()->prefix() . Kanban_Fieldvalue::instance()->get_table();
		$table_comments    = Kanban_Db::instance()->prefix() . Kanban_Comment::instance()->get_table();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT $table.*,
				(
					SELECT GROUP_CONCAT($table_fieldvalues.id)
					FROM $table_fieldvalues
					WHERE $table_fieldvalues.`card_id` = $table.id
					AND $table_fieldvalues.`is_active` = 1
				) as fieldvalues,
				(
					SELECT GROUP_CONCAT($table_comments.id)
					FROM $table_comments
					WHERE $table_comments.`card_id` = $table.id
					AND $table_comments.`is_active` = 1
				) as comments,
				(
					SELECT count($table_comments.id)
					FROM $table_comments
					WHERE $table_comments.`card_id` = $table.id
					AND $table_comments.`is_active` = 1
					AND $table_comments.`comment_type` = 'user'
				) as comment_count_user
				
				    
				FROM $table
				
				
				WHERE 1=1
				AND $table.id = %d
				",
				$id
			),
			OBJECT
		);

		$row = $this->format_data_for_app( $row );

		return $row;
	} // delete

	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_app( $row );

		if ( empty( $row ) ) {
			return array();
		}

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'fieldvalues':
					$value = $this->format_csv_for_app( $value );
					break;
				case 'comments':
					$value = $this->format_csv_for_app( $value );
					break;
				case 'lane_id':
					$value = $this->format_int_for_app( $value );
					break;
			}
		}

		return $row;
	}

	public function ajax_copy( $data ) {

		if ( ! Kanban_User::instance()->current_user_has_cap( 'card-write' ) ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );

			return false;
		}

		if ( ! isset( $data['card_id'] ) ) {
			header( 'HTTP/1.1 401 Missing id' );

			return false;
		}

		$row = $this->get_row( $data['card_id'] );

		if ( empty( $row ) ) {
			header( 'HTTP/1.1 401 record not found' );

			return false;
		}

		$lane = Kanban_Lane::instance()->get_row( $row->lane_id );

		// Store time so we can get new records later.
		$since_dt = new DateTime( 'now -1 second' );

		// Remove the id so we create a new one.
		unset( $row->id );

		// Get the new row.
		$new_row = $this->set_row( $row );

		// Add new card to lane.
		$index             = array_search( $data['card_id'], $lane->cards_order );
		$pos               = false === $index ? count( $lane->cards_order ) : $index + 1;
		$lane->cards_order = array_merge(
			array_slice( $lane->cards_order, 0, $pos ),
			array( $new_row->id ),
			array_slice( $lane->cards_order, $pos )
		);

		$lane = Kanban_Lane::instance()->update_cards_order( $row->lane_id, $lane->cards_order );

		// Copy field values
		$fieldvalues = Kanban_Fieldvalue::instance()->get_results_by_cards( array( $data['card_id'] ) );

		foreach ( $fieldvalues as $fieldvalue ) {
			unset( $fieldvalue->id );
			$fieldvalue->card_id = $new_row->id;
			Kanban_Fieldvalue::instance()->set_row( (array) $fieldvalue );
		}

		$app_data = Kanban_App::instance()->get_updates( $since_dt->format( 'Y-m-d H:i:s' ) );

		return $app_data;
	}

	public function ajax_delete( $data ) {

		if ( ! Kanban_User::instance()->current_user_has_cap( 'card-write' ) ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );

			return false;
		}

		if ( ! isset( $data['card_id'] ) ) {
			header( 'HTTP/1.1 401 Missing id' );

			return false;
		}

//		$row = $this->get_row($data['card_id']);
//
//		if ( empty($row) ) {
//			header( 'HTTP/1.1 401 record not found' );
//			return false;
//		}

		$this->set_row( array(
			'id'        => $data['card_id'],
			'is_active' => 0
		) );

		return true;

	}

	public function get_board_id_by_card_id( $card_id ) {

		$card_id = intval( $card_id );

		global $wpdb;

		$table       = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_lane  = Kanban_Db::instance()->prefix() . Kanban_Lane::instance()->get_table();
		$table_board = Kanban_Db::instance()->prefix() . Kanban_Board::instance()->get_table();

		$board_id = $wpdb->get_var(
			$wpdb->prepare( "
					SELECT $table_board.id
					
					FROM $table
					
					JOIN $table_lane
					ON $table_lane.`id` = $table.lane_id
					
					JOIN $table_board
					ON $table_board.`id` = $table_lane.board_id
					
					WHERE $table.id = %d
				;",
				$card_id
			)
		);

		return $board_id;
	}

	public function get_results_by_lanes( $lane_ids, $since_dt = null ) {
		global $wpdb;

		$table             = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_fieldvalues = Kanban_Db::instance()->prefix() . Kanban_Fieldvalue::instance()->get_table();
		$table_comments    = Kanban_Db::instance()->prefix() . Kanban_Comment::instance()->get_table();

		if ( is_numeric( $lane_ids ) || is_string( $lane_ids ) ) {
			$lane_ids = array( $lane_ids );
		}

		$in = sprintf(
			" AND $table.`lane_id` IN (%s) ",
			implode( ',', $lane_ids )
		);

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

		$rows = $wpdb->get_results(
			"
				SELECT $table.*,
				(
					SELECT GROUP_CONCAT($table_fieldvalues.id)
					FROM $table_fieldvalues
					WHERE $table_fieldvalues.`card_id` = $table.id
					AND $table_fieldvalues.`is_active` = 1
				) as fieldvalues,
				(
					SELECT GROUP_CONCAT($table_comments.id)
					FROM $table_comments
					WHERE $table_comments.`card_id` = $table.id
					AND $table_comments.`is_active` = 1
				) as comments,
				(
					SELECT count($table_comments.id)
					FROM $table_comments
					WHERE $table_comments.`card_id` = $table.id
					AND $table_comments.`is_active` = 1
					AND $table_comments.`comment_type` = 'user'
				) as comment_count_user
				    
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
		}

		return $rows;
	}

	public function get_results_by_ids( $card_ids = array() ) {

		$card_ids = array_filter( $card_ids, 'intval' );

		global $wpdb;

		$table             = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_fieldvalues = Kanban_Db::instance()->prefix() . Kanban_Fieldvalue::instance()->get_table();
		$table_comments    = Kanban_Db::instance()->prefix() . Kanban_Comment::instance()->get_table();

		$in = sprintf(
			" AND $table.`lane_id` IN (%s) ",
			implode( ',', $card_ids )
		);

		$rows = $wpdb->get_results(
			"
				SELECT $table.*,
				(
					SELECT GROUP_CONCAT($table_fieldvalues.id)
					FROM $table_fieldvalues
					WHERE $table_fieldvalues.`card_id` = $table.id
					AND $table_fieldvalues.`is_active` = 1
				) as fieldvalues,
				(
					SELECT GROUP_CONCAT($table_comments.id)
					FROM $table_comments
					WHERE $table_comments.`card_id` = $table.id
					AND $table_comments.`is_active` = 1
				) as comments
				    
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
	} // format_data_for_db

	public function format_data_for_db( $row ) {

		$row = parent::format_data_for_db( $row );

		if ( empty( $row ) ) {
			return array();
		}

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'lane_id':
					$value = $this->format_int_for_db( $value );
					break;
			}
		}

		return $row;
	}

	public function get_uri ($card_id, $modal = true) {
		$board_id = Kanban_Card::instance()->get_board_id_by_card_id($card_id);

		return add_query_arg(
			array(
				'board' => $board_id,
				'modal' => $modal === true ? 'card' : '',
				'card' => $card_id
			),
			Kanban_Router::instance()->get_page_uri('board')
		);
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
					lane_id bigint(20) DEFAULT '0' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql

}