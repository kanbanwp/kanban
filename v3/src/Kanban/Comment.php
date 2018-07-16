<?php


class Kanban_Comment extends Kanban_Abstract {


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
		'card_id'          => '%d',
		'content'          => '%s',
		'comment_type'     => '%s',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'comments';



	public function ajax_get_by_card ($data) {

		if ( !Kanban_User::instance()->current_user_has_cap('comment-read') && !Kanban_User::instance()->current_user_has_cap('comment-write') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['card_id']) ) {
			header( 'HTTP/1.1 401 Missing card id' );
			return false;
		}

		$comments = self::get_results_by_cards($data['card_id']);

		return $comments;
	}

	public function ajax_add( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('comment-write') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['card_id']) ) {
			header( 'HTTP/1.1 401 Missing card id' );
			return false;
		}

		$row = $this->set_row( $data );

		// Make sure the user who created the comment is following the card.
		Kanban_Card_User::instance()->add_current_user_to_card($data['card_id']);

		return $row;
	}

	public function ajax_replace( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('comment-write') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( isset($data['comment_id']) ) {
			$data['id'] = $data['comment_id'];
		}

		$row = $this->set_row( $data );

		return $row;
	}

	public function ajax_delete ( $data ) {

		if ( !isset($data['comment_id']) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		$row = $this->get_row($data['comment_id']);

		if ( $row->created_user_id != get_current_user_id() ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		$this->set_row( array(
			'id' => $data['comment_id'],
			'is_active' => 0
		) );

		$row = $this->get_row($data['comment_id']);

		return $row;

	} // delete

	public function ajax_upload ( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('comment-write') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['card_id']) ) {
			header( 'HTTP/1.1 401 Missing card id' );
			return false;
		}

		$file_data = Kanban_File::instance()->upload_from_post($data['card_id']);

		return $file_data;
	}

	public function get_results_by_cards ($card_ids, $since_dt = null) {

		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		if ( is_numeric($card_ids) || is_string($card_ids) ) {
			$card_ids = array($card_ids);
		}

		$in = sprintf(
			" AND $table.`card_id` IN (%s) ",
			implode(',', $card_ids)
		);

		$since = '';
		$isActive = "AND $table.is_active = 1";
		if ( DateTime::createFromFormat('Y-m-d H:i:s', $since_dt) !== FALSE) {

			$since = "
			AND $table.`modified_dt_gmt` > '$since_dt' 
			";

			$isActive = '';

//			if ( is_user_logged_in() ) {
//				$since .= sprintf(
//					" AND $table.`modified_user_id` != %d ",
//					get_current_user_id()
//				);
//			}
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

	public function get_row($id) {

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

		if ( empty( $row ) ) {
			return (object) array();
		}

		$row = $this->format_data_for_app( $row );

		return $row;
	}

	public function set_row( $data ) {
		return parent::set_row( $data );
	}

	public function format_data_for_db( $row ) {

		$row = parent::format_data_for_db($row);

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'content':
					$value = $this->format_string_for_db($value);
					break;
				case 'card_id':
					$value = $this->format_int_for_db($value);
					break;
			}
		}

		return $row;
	}

	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_app($row);

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'content':
					$value = $this->format_string_for_app($value);
					break;
				case 'card_id':
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
					content text DEFAULT '' NOT NULL,
					comment_type varchar(128) DEFAULT 'system' NOT NULL,
					card_id bigint(20) DEFAULT '0' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql


}