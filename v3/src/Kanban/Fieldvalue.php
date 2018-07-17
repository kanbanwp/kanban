<?php


class Kanban_Fieldvalue extends Kanban_Abstract {


	/**
	 * @var array Database table fields and types for filtering.
	 */
	protected $fields = array(
		'id'               => '%d',
		'created_dt_gmt'   => '%s',
		'created_user_id'  => '%s',
		'modified_dt_gmt'  => '%s',
		'modified_user_id' => '%d',
		'content'          => '%s',
		'field_id'         => '%d',
		'card_id'          => '%d',
		'is_active'        => '%d',
	);


	/**
	 * @var string database table name.
	 */
	protected $table = 'fieldvalues';


	public function ajax_replace( $data ) {

		if ( ! Kanban_User::instance()->current_user_has_cap( 'card-write' ) ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );

			return false;
		}

		if ( ! isset( $data['fieldvalue_id'] ) && ( ! isset( $data['card_id'] ) || ! isset( $data['field_id'] ) ) ) {
			header( 'HTTP/1.1 401 Missing id' );
			return false;
		}

		if ( isset( $data['fieldvalue_id'] ) ) {
			$row              = Kanban_Fieldvalue::get_row( $data['fieldvalue_id'] );
			$data['field_id'] = $row->field_id;
		}

		if ( ! isset( $data['field_id'] ) ) {
			header( 'HTTP/1.1 401 Missing field id' );

			return false;
		}

		$row_prev = (object) array();
		if ( isset( $data['fieldvalue_id'] ) ) {
			$data['id'] = $data['fieldvalue_id'];
			$row_prev = Kanban_Fieldvalue::get_row( $data['fieldvalue_id'] );
		}

		$prev_content = '';
		if ( isset( $row_prev->content ) ) {
			$prev_content = $row_prev->content;
		}

		$row = $this->set_row( $data );

		// Store the new fieldvalue.
		Kanban_Fieldvalue_Log::instance()->set_row( array(
			'fieldvalue_id' => $row->id,
			'content' => $row->content
		) );

		do_action(
			'kanban_fieldvalue_ajax_replace_set_row_after',
			$row,
			$prev_content
		);

		return $row;
	}

	public function set_row( $data ) {

//		$row = (object) array();
//		if ( isset( $data['fieldvalue_id'] ) ) {
//			$row = Kanban_Fieldvalue::get_row( $data['fieldvalue_id'] );
//		}
//
//		$prev_content = '';
//		if ( isset( $row->content ) ) {
//			$prev_content = $row->content;
//		}

		return parent::set_row( $data );

//		$field = Kanban_Field::instance()->get_row( $row->field_id );
//
//		$class = Kanban_Field::instance()->get_fieldtype_class( $field->field_type );
//
//		$data['content'] = $class::instance()->format_content_for_app( $data['content'] );

//		if ( isset( $data['content'] ) && !empty($data['content']) && is_string($data['content']) && $data['content'] != $prev_content )  {
//
//			preg_match_all( '/data-mention=\"([0-9]*)\"/',
//				$data['content'],
//				$matches,
//				PREG_PATTERN_ORDER
//			);
//
//			if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
//				$user_ids = array_filter( array_unique( $matches[1] ) );
//
//				// Remove the user that last modified it, so they don't notify themselves.
//				if (($key = array_search( $row->modified_user_id, $user_ids)) !== false) {
//					unset($user_ids[$key]);
//				}
//
//				if ( ! empty( $user_ids ) ) {
//					$board = Kanban_Board::instance()->get_row( $field->board_id );
//
//					$subject = sprintf(
//						__( 'Board "%s": You were mentioned in field "%s" (card "%d")' ),
//						Kanban_Board::instance()->get_label( $board ),
//						Kanban_Field::instance()->get_label( $field ),
//						$row->card_id
//					);
//
//					$message = sprintf(
//						'%s' . "\n\n"
//						. '%s' . "\n\n"
//						. '%s' . "\n"
//						. '%s',
//						__( 'Heads up!' ),
//						sprintf(
//							__( 'You were mentioned in the "%s" field on card "%d".' ),
//							Kanban_Field::instance()->get_label( $field ),
//							$row->card_id,
//							Kanban_Board::instance()->get_label( $board )
//						),
//						__( 'Follow this link to read more:' ),
//						add_query_arg(
//							array(
//								'board' => $field->board_id,
//								'modal' => 'card',
//								'card'  => $row->card_id
//							),
//							Kanban_Router::instance()->get_page_uri( 'board' )
//						)
//					);
//
//					Kanban_Notification::instance()->notify_users( $user_ids, $subject, $message );
//				}
//			}
//		}

//		return $row;
	}

	public function get_row( $id ) {
		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_field = Kanban_Db::instance()->prefix() . Kanban_Field::instance()->get_table();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.* ,
					$table_field.`field_type`
					
					FROM $table
					
					LEFT JOIN $table_field
					ON $table.`field_id` = $table_field.`id`
					
					WHERE 1=1
					AND $table.id = %d
				",
				$id
			),
			OBJECT
		);

		if ( empty($row) ) return (object) array();

		$row = $this->format_data_for_app( $row );

		return $row;
	}

	public function get_results_by_cards( $card_ids, $since_dt = null ) {

		global $wpdb;

		$table       = Kanban_Db::instance()->prefix() . $this->get_table();
		$table_field = Kanban_Db::instance()->prefix() . Kanban_Field::instance()->get_table();

		if ( is_numeric( $card_ids ) || is_string( $card_ids ) ) {
			$card_ids = array( $card_ids );
		}

		$in = sprintf(
			" AND $table.`card_id` IN (%s) ",
			implode( ',', $card_ids )
		);

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

		$rows = $wpdb->get_results(
			"
					SELECT $table.*,
					$table_field.`field_type`
					 
					FROM $table
					
					LEFT JOIN $table_field
					ON $table.`field_id` = $table_field.`id`
					
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
	} // get_results_by_cards

	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_app( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'content':

					$field = Kanban_Field::instance()->get_row( $row->field_id );
					$class = Kanban_Field::instance()->get_fieldtype_class( $field->field_type );

					$value = $class::instance()->format_content_for_app( $value );
					break;
				case 'field_id':
					$value = $this->format_int_for_app( $value );
					break;
				case 'card_id':
					$value = $this->format_int_for_app( $value );
					break;
			}
		}

		return $row;
	}

	public function format_data_for_db( $row ) {

		$row = parent::format_data_for_db( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'content':

					$field = Kanban_Field::instance()->get_row( $row['field_id'] );
					$class = Kanban_Field::instance()->get_fieldtype_class( $field->field_type );

					$value = $class::instance()->format_content_for_db( $value );

					break;
				case 'field_id':
					$value = $this->format_int_for_db( $value );
					break;
				case 'card_id':
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
					content text DEFAULT '' NOT NULL,
					card_id bigint(20) DEFAULT '0' NOT NULL,
					field_id bigint(20) DEFAULT '0' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql

}