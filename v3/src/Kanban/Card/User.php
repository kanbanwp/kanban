<?php


class Kanban_Card_User extends Kanban_Abstract {

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
		'user_id'          => '%d',
		'card_id'          => '%d',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'card_users';


	/**
	 * Add a new board record for a user
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function ajax_add( $data ) {

		if ( ! Kanban_User::instance()->current_user_has_cap( 'card-read' ) ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );

			return false;
		}

		if ( ! isset( $data['card_id'] ) ) {
			header( 'HTTP/1.1 401 Missing card id' );

			return false;
		}

		$row = $this->add_current_user_to_card($data['card_id']);

		return row;
	}

	public function ajax_delete( $data ) {

		if ( ! Kanban_User::instance()->current_user_has_cap( 'card-read' ) ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );

			return false;
		}

		if ( ! isset( $data['card_id'] ) ) {
			header( 'HTTP/1.1 401 Missing card id' );

			return false;
		}

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$wpdb->query(
			$wpdb->prepare("
					Update $table
					
					SET $table.is_active = 0
					WHERE $table.`user_id` = %d
					AND $table.`card_id` = %d
				;",
				get_current_user_id(),
				$data['card_id']
			)
		);

		return true;

	} // delete

	public function add_current_user_to_card ($card_id) {

		$card_id = intval($card_id);

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$row = $wpdb->get_row(
			$wpdb->prepare("
					SELECT $table.*
					
					FROM $table
					WHERE 1=1
					AND $table.`user_id` = %d
					AND $table.`card_id` = %d
					AND is_active = 1
				;",
				get_current_user_id(),
				$card_id
			)
		);

		if ( empty($row) ) {
			$row = $this->set_row(array(
				'card_id' => $card_id,
				'user_id' => get_current_user_id()
			));
		}

		return $row;
	}

	public function add_users_to_card ($user_ids, $card_id) {

		$user_ids = array_map('intval', $user_ids);

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$in = implode(',', $user_ids);

		$rows = $wpdb->get_col(
			$wpdb->prepare("
					SELECT $table.`user_id`
					
					FROM $table
					WHERE 1=1
					AND $table.`user_id` IN $in
					AND $table.`card_id` = %d
					AND is_active = 1
				;",
				$card_id
			)
		);

		if ( !empty($rows) ) {
			$user_ids = array_diff( $user_ids, $rows );
		}

		if ( !empty($user_ids) ) {
			foreach ($user_ids as $user_id ) {
				$row = $this->set_row( array(
					'card_id' => $card_id,
					'user_id' => $user_id
				) );
			}
		}

		return $user_ids;
	}

	public function get_user_ids_by_card_id( $card_id ) {

		global $wpdb;

		$card_id = intval($card_id);

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$rows = $wpdb->get_col(
			$wpdb->prepare("
					SELECT $table.user_id
					
					FROM $table
					WHERE 1=1
					AND $table.`card_id` = %d
					AND is_active = 1
				;",
				$card_id
			)
		);

		if ( empty( $rows ) ) {
			return array();
		}

		return array_map('intval', $rows);
	} // get_user_ids_by_card_id

	public function set_row( $data ) {
		return parent::set_row( $data );
	}

	public function get_card_ids_for_current_user() {

		if ( ! is_user_logged_in() ) {
			return (object) array();
		}

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$rows = $wpdb->get_col(
			$wpdb->prepare("
					SELECT $table.card_id
					
					FROM $table
					WHERE 1=1
					AND $table.`user_id` = %d
					AND is_active = 1
				;",
				get_current_user_id()
			)
		);

		if ( empty( $rows ) ) {
			return array();
		}

		return array_map('intval', $rows);
	} // get_card_ids_for_current_user

	public function get_row( $id ) {

		$id = intval( $id );

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		// Get kanban user records.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.user_id,
					$table.card_id
					
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

		$row = $this->format_data_for_app( $row );

		return $row;
	} // get_row

	public function format_data_for_db( $row ) {

		$row = parent::format_data_for_db( $row );

		if ( empty( $row ) ) {
			return array();
		}

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'card_id':
					$value = $this->format_int_for_db( $value );
					break;
				case 'user_id':
					$value = $this->format_int_for_db( $value );
					break;
			}
		}

		return $row;
	} // format_data_for_db

	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_app( $row );

		if ( empty( $row ) ) {
			return array();
		}

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'card_id':
					$value = $this->format_int_for_app( $value );
					break;
				case 'user_id':
					$value = $this->format_int_for_app( $value );
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
					user_id bigint(20) DEFAULT '0' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql

}