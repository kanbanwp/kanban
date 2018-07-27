<?php


class Kanban_Field_Date extends Kanban_Field {

	private static $instance;

	public function send_notifications_after_fieldvalue_update ($fieldvalue, $prev_content) {

		if ( $fieldvalue->field_type != 'date') return;

		$field = Kanban_Field::instance()->get_row($fieldvalue->field_id);

		$formatted_content = $this->format_content_for_emails($fieldvalue->content, $field);

		$board_id = Kanban_Card::instance()->get_board_id_by_card_id($fieldvalue->card_id);

		// Get all users who follow the card.
		$card_user_ids = Kanban_Card_User::instance()->get_user_ids_by_card_id($fieldvalue->card_id);

		// Remove the user who modified the field.
		if ( isset($card_user_ids[$fieldvalue->modified_user_id]) ) {
			unset($card_user_ids[$fieldvalue->modified_user_id]);
		}

		if ( !empty($card_user_ids) ) {

			$subject = sprintf(
				__( 'Card #%d has been updated on board "%s"' ),
				$fieldvalue->card_id,
				Kanban_Board::instance()->get_label( $board_id )
			);

			$message = Kanban_Template::instance()->render(
				Kanban::instance()->settings()->path . '/board/emails/fieldvalue/updated.php',
				array(
					'field_label' => Kanban_Field::instance()->get_label( $fieldvalue->field_id ),
					'card_id'     => $fieldvalue->card_id,
					'content'     => $formatted_content,
					'card_url'    => Kanban_Card::instance()->get_uri( $fieldvalue->card_id )
				)
			);

			Kanban_Notification::instance()->notify_users_with_email_template($card_user_ids, $subject, $message);
		}
	}

//	public function get_results_by_fields( $field_ids) { // , $start, $end
//
//		if ( !isset($start) || DateTime::createFromFormat( 'Y-m-d', $start ) === false ) {
//			$start = Date('Y-m-01');
//		}
//
//		if ( !isset($end) || DateTime::createFromFormat( 'Y-m-d', $end ) === false ) {
//			$end = Date('Y-m-t');
//		}
//
//		global $wpdb;
//
//		$table       = Kanban_Db::instance()->prefix() . Kanban_Fieldvalue::instance()->get_table();
//		$table_field = Kanban_Db::instance()->prefix() . Kanban_Field::instance()->get_table();
//
//		if ( is_numeric( $field_ids ) || is_string( $field_ids ) ) {
//			$field_ids = array( $field_ids );
//		}
//
//		$in = sprintf(
//			" AND $table.`field_id` IN (%s) ",
//			implode( ',', $field_ids )
//		);
//
//		$rows = $wpdb->get_results(
//			"
//				SELECT $table.*,
//				$table_field.`field_type`
//
//				FROM $table
//
//				LEFT JOIN $table_field
//				ON $table.`field_id` = $table_field.`id`
//
//				WHERE 1=1
//				AND $table.is_active = 1
//				$in
//
//			;",
//			OBJECT_K
//		);
////		-- AND DATE($table.`content`) >= '$start'
////		-- AND DATE($table.`content`) >= '$end'
//
//
////		echo $wpdb->last_query . ' - LINE ' . __LINE__ . "\n<br />";
////		exit;
//		foreach ( $rows as $row_id => &$row ) {
//			$row = Kanban_Fieldvalue::instance()->format_data_for_app( $row );
//		}
//
//		return $rows;
//	} // get_results_by_fields

	public function format_options_for_db($options) {

		$field_options = parent::format_options_for_db($options);

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'show_datecount':
					$value = $this->format_bool_for_db($value);
					break;
				case 'is_date_range':
					$value = $this->format_bool_for_db($value);
					break;
				default:
					unset($options[$key]);
					break;
			}
		}

		$options += $field_options;

		return (array) $options;
	}

	public function format_options_for_app($options) {
		$field_options = parent::format_options_for_app($options);

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'show_datecount':
					$value = $this->format_bool_for_app($value);
					break;
				case 'is_date_range':
					$value = $this->format_bool_for_app($value);
					break;
				default:
					unset($options[$key]);
					break;
			}
		}


		$options += $field_options;

		return (array) $options;
	}

	public function format_content_for_db($content) {

		$content = $this->format_json_for_db($content);

		return $content;
	}

	public function format_content_for_app($content) {

		$content = $this->format_json_for_app($content);

		return $content;
	}

	public function format_content_for_emails ($content, $field) {

		if ( '' == $content ) {
			return $content;
		}

		$current_user = Kanban_User::instance()->get_current();

		if ( !isset($current_user->options->app['date_view_format']) ) {
			$current_user->options->app['date_view_format'] = 'M d, yyyy';
		}

		$formatted_content = '';
		if ( DateTime::createFromFormat('Y-m-d', $content->start) !== FALSE ) {
			$dt = DateTime::createFromFormat('Y-m-d', $content->start);

			$formatted_content .= $this->format_datetime_for_current_user($dt);
		}

		if ( $field->options['is_date_range'] ) {
			$formatted_content .= ' -> ';

			if ( DateTime::createFromFormat('Y-m-d', $content->end) !== FALSE ) {
				$dt = DateTime::createFromFormat('Y-m-d', $content->end);
				$formatted_content .= $this->format_datetime_for_current_user($dt);
			}
		}

		return $formatted_content;
	}

	public function format_datetime_for_current_user ($dt) {
		$current_user = Kanban_User::instance()->get_current();

		if ( !isset($current_user->options->app['date_view_format']) ) {
			$current_user->options->app['date_view_format'] = 'M d, yyyy';
		}

		switch ( $current_user->options->app['date_view_format'] ) {
			case 'mm/dd/yyyy';
				return $dt->format('m\/d\/Y');
				break;
			case 'dd/mm/yyyy';
				return $dt->format('d\/m\/Y');
				break;
			case 'yyyy-mm-dd';
				return $dt->format('Y-m-d');
				break;
			default:
				return $dt->format('M j, Y');
		}
	}

	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

			add_action(
				'kanban_fieldvalue_ajax_replace_set_row_after',
				array( self::$instance, 'send_notifications_after_fieldvalue_update' ),
				0,
				2
			);

		}

		return self::$instance;
	}
}