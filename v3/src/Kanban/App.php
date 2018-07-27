<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Kanban_App {

	// the instance of this object
	private static $instance;

	/**
	 * construct that can't be overwritten
	 */
	private function __construct() {

	}

	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function ajax_updates_check() {

		if ( ! isset( $_POST['datetime'] ) ) {
			header( 'HTTP/1.1 400 Missing datetime' );

			return false;
		}

		$datetime = (int) ( $_POST['datetime'] / 1000 );

		if ( DateTime::createFromFormat( 'U', $datetime ) === false ) {
			header( 'HTTP/1.1 400 Invalid datetime' );

			return false;
		}

		$since_dt = DateTime::createFromFormat( 'U', $datetime );

		$app_data = $this->get_updates( $since_dt->format( 'Y-m-d H:i:s' ) );

		return $app_data;
	}

	public function get_updates( $since_dt = null ) {

		// Get everything the current user has access to.
		$app_data = $this->get_app_data();

		$data = (object) array();

		// If trying to use a date but the date is invalid, return nothing.
		if ( ! is_null( $since_dt ) && DateTime::createFromFormat( 'Y-m-d H:i:s', $since_dt ) === false ) {
			header( 'HTTP/1.1 400 Invalid datetime' );

			return $data;
		}

		// Set default.
		$data->users = array();

		// If the current user's caps for any board have been updated, then consider them updated.
		$current_user_board_records = Kanban_User_Cap::instance()->get_boards_by_user_id( get_current_user_id(), $since_dt );

		// If there are updated coard records, get the current user.
		if ( is_array( $current_user_board_records ) && ! empty( $current_user_board_records ) ) {

			// Get current user.
			$current_user = Kanban_User::instance()->get_current();

			// Do a rerender.
			$current_user->is_updated = true;

			// Add current user to users.
			$data->users = array(
				$current_user->id => $current_user
			);
		}

		$data->boards = Kanban_Board::instance()->get_results( $since_dt );

		$data->lanes = array();

		if ( ! empty( $app_data->boards ) ) {
			$board_ids = array_keys( $app_data->boards );

			$data->lanes = Kanban_Lane::instance()->get_results_by_boards( $board_ids, $since_dt );
		}

		// If lanes have been updated, they might have been added, so we need the order from the boards.
//		if ( !empty($data->lanes) ) {
//			$board_ids = array_map(function($o) { return $o->board_id; }, $data->lanes);
//			$lane_boards = Kanban_Board::instance()->get_results_by_ids($board_ids);
//			$data->boards = $data->boards + $lane_boards;
//		}

		$data->cards = array();

		if ( ! empty( $app_data->lanes ) ) {
			$lane_ids    = array_keys( $app_data->lanes );
			$data->cards = Kanban_Card::instance()->get_results_by_lanes( $lane_ids, $since_dt );
		}

		// If cards have been updated, they might have been added, so we need the order from the lanes.
		if ( ! empty( $data->cards ) ) {
			$lane_ids    = array_map( function ( $o ) {
				return $o->lane_id;
			}, $data->cards );
			$card_lanes  = Kanban_Lane::instance()->get_results_by_ids( $lane_ids );
			$data->lanes = $data->lanes + $card_lanes;
		}

		$data->fields = array();

		if ( ! empty( $app_data->boards ) ) {
			$board_ids    = array_keys( $app_data->boards );
			$data->fields = Kanban_Field::instance()->get_results_by_boards( $board_ids, $since_dt );
		}

		$data->fieldvalues = array();

		if ( ! empty( $app_data->cards ) ) {
			$card_ids          = array_keys( $app_data->cards );
			$data->fieldvalues = Kanban_Fieldvalue::instance()->get_results_by_cards( $card_ids, $since_dt );
		}

		$data->comments = array();

		if ( ! empty( $app_data->cards ) ) {
			$card_ids       = array_keys( $app_data->cards );
			$data->comments = Kanban_Comment::instance()->get_results_by_cards( $card_ids, $since_dt );
		}

		// Make sure we have the users for all comments.
		if ( ! empty( $data->comments ) ) {

			// Get cards for updated comments.
//			$card_ids = array_map( function ( $o ) {
//				return $o->card_id;
//			}, $data->comments );
//			$comment_cards = Kanban_Card::instance()->get_results_by_ids( $card_ids );
//			$data->cards   = $data->cards + $comment_cards;

			// Get users that created the comments.
			$user_ids = array_map( function ( $o ) {
				return $o->created_user_id;
			}, $data->comments );

			// Add comment users to users.
			$comment_users = Kanban_User::instance()->get_users( $user_ids );
			$data->users   = $data->users + $comment_users;
		}

		if ( ! empty( $data->boards ) ) {
			foreach ( $data->boards as &$board ) {
				$board->is_updated = true;
			}
		}

		if ( ! empty( $data->lanes ) ) {
			foreach ( $data->lanes as &$lane ) {
				$lane->is_updated = true;
			}
		}

		if ( ! empty( $data->cards ) ) {
			foreach ( $data->cards as &$card ) {
				$card->is_updated = true;
			}
		}

		if ( ! empty( $data->fields ) ) {
			foreach ( $data->fields as &$field ) {
				$field->is_updated = true;
			}
		}

		if ( ! empty( $data->fieldvalues ) ) {
			foreach ( $data->fieldvalues as &$fieldvalue ) {
				$fieldvalue->is_updated = true;
			}
		}

		if ( ! empty( $data->comments ) ) {
			foreach ( $data->comments as &$comment ) {
				$comment->is_updated = true;
			}
		}

		return $data;
	} // get_app_data

	public function get_app_data( $since_dt = null ) {

		$data = (object) array();

		$data->plugin_data = Kanban::instance()->settings()->plugin_data;

		// If trying to use a date but the date is invalid, return nothing.
		if ( ! is_null( $since_dt ) && DateTime::createFromFormat( 'Y-m-d H:i:s', $since_dt ) === false ) {
			header( 'HTTP/1.1 400 Invalid datetime' );

			return $data;
		}

		$current_user = Kanban_User::instance()->get_current();

		$data->boards = Kanban_Board::instance()->get_results( $since_dt );

		// If user doesn't have current board.
		if ( ! isset( $current_user->options->app['current_board'] ) || empty( $current_user->options->app['current_board'] ) || ! isset( $data->boards[ $current_user->options->app['current_board'] ] ) ) {

			if ( ! empty( $data->boards ) ) {

				// Get the first board.
				reset( $data->boards );

				// Set it.
				$current_user->options->app['current_board'] = key( $data->boards );

				// Save it.
				Kanban_User_Option::instance()->replace_app ('current_board', key( $data->boards ));
			}
		}

		$data->app               = Kanban_App_Option::instance()->get_row();
		$data->app->current_user = $current_user->id;
		$data->app->boards       = array_keys( $data->boards );

		$data->lanes = array();

		if ( ! empty( $data->boards ) ) {
			$board_ids   = array( $current_user->options->app['current_board'] ); // array_keys( $data->boards );
			$data->lanes = Kanban_Lane::instance()->get_results_by_boards( $board_ids, $since_dt );
		}

		$data->cards = array();

		if ( ! empty( $data->lanes ) ) {
			$lane_ids    = array_keys( $data->lanes );
			$data->cards = Kanban_Card::instance()->get_results_by_lanes( $lane_ids, $since_dt );
		}

		$data->fields = array();

		if ( ! empty( $data->boards ) ) {
			$board_ids    = array_keys( $data->boards );
			$data->fields = Kanban_Field::instance()->get_results_by_boards( $board_ids, $since_dt );
		}

		$data->fieldvalues = array();

		if ( ! empty( $data->cards ) ) {
			$card_ids          = array_keys( $data->cards );
			$data->fieldvalues = Kanban_Fieldvalue::instance()->get_results_by_cards( $card_ids, $since_dt );
		}

		// Remove fieldvalues, if field isn't set (specifically hidden fields that have been removed).
		if ( ! empty( $data->fieldvalues ) ) {
			foreach ( $data->fieldvalues as &$fieldvalue ) {
				if ( !isset($data->fields[$fieldvalue->field_id]) ) {
					unset($data->fieldvalues[$fieldvalue->id]);
				}
			}
		}

		$data->comments = array();

//		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'comments' ) {
//			$card_ids = array();
//
//			if ( isset( $_GET['card'] ) && isset( $data->cards[ $_GET['card'] ] ) ) {
//				$card_ids = array( $_GET['card'] );
//			} else if ( ! empty( $data->cards ) ) {
//				$card_ids = array_keys( $data->cards );
//			}
//
//			$data->comments = Kanban_Comment::instance()->get_results_by_cards( $card_ids, $since_dt );
//		}

		$data->users                      = Kanban_User::instance()->get_wp_users();
		$data->users[ $current_user->id ] = $current_user;

		return $data;
	}

//	public function ajax_get_calendar_data() {
//
//		if ( !isset($_POST['start']) || DateTime::createFromFormat( 'Y-m-d', $_POST['start'] ) === false ) {
//			$_POST['start'] = Date('Y-m-01');
//		}
//
//		if ( !isset($_POST['end']) || DateTime::createFromFormat( 'Y-m-d', $_POST['end'] ) === false ) {
//			$_POST['end'] = Date('Y-m-t');
//		}
//
//		$app_data = $this->get_calendar_data( $_POST['start'], $_POST['end'] );
//
//		return $app_data;
//	}
//
//	public function get_calendar_data ($start = null, $end = null) {
//
//		if ( !isset($start) || DateTime::createFromFormat( 'Y-m-d', $start ) === false ) {
//			$start = Date('Y-m-01');
//		}
//
//		if ( !isset($end) || DateTime::createFromFormat( 'Y-m-d', $end ) === false ) {
//			$end = Date('Y-m-t');
//		}
//
//
//		$data = (object) array();
//
//		$current_user = Kanban_User::instance()->get_current();
//
//		$data->boards = Kanban_Board::instance()->get_results( );
//
//		// If user doesn't have current board.
//		if ( ! isset( $current_user->options->app['current_board'] ) || empty( $current_user->options->app['current_board'] ) || ! isset( $data->boards[ $current_user->options->app['current_board'] ] ) ) {
//
//			if ( ! empty( $data->boards ) ) {
//
//				// Get the first board.
//				reset( $data->boards );
//
//				// Set it.
//				$current_user->options->app['current_board'] = key( $data->boards );
//
//				// Save it.
//				Kanban_User_Option::instance()->replace_app ('current_board', key( $data->boards ));
//			}
//		}
//
//		$data->app               = Kanban_App_Option::instance()->get_row();
//		$data->app->current_user = $current_user->id;
//		$data->app->boards       = array_keys( $data->boards );
//
//		$data->fields = array();
//
//		if ( ! empty( $data->boards ) ) {
//			$board_ids    = array_keys( $data->boards );
//			$data->fields = Kanban_Field::instance()->get_results_by_boards( array(1) ); // $board_ids
//		}
//
//		$fields = array(
//			'title_field' => 0,
//			'date_field' => 0
//		);
//
//		foreach ($data->fields as $field_id => $field) {
//			if ( $fields['date_field'] == 0 && $field->field_type == 'date' ) {
//				$fields['date_field'] = $field_id;
//			}
//
//			if ( $fields['title_field'] == 0 && $field->field_type == 'title' ) {
//				$fields['title_field'] = $field_id;
//			}
//
//			if ( $fields['title_field'] > 0 && $fields['date_field'] > 0 ) break;
//		}
//
//		global $wpdb;
//
//		$records = $wpdb->get_results(
//			$wpdb->prepare("
//					SELECT
//					`datefield`.card_id,
//					`datefield`.content AS 'dates',
//					`titlefield`.content AS 'title'
//
//					FROM wp_kanban3_fieldvalues AS datefield
//
//					JOIN wp_kanban3_fieldvalues AS titlefield
//					ON datefield.card_id = titlefield.card_id
//
//					WHERE 1=1
//					AND `datefield`.field_id = %d
//					AND `titlefield`.field_id = %d
//
//					GROUP BY `datefield`.card_id
//				;",
//				$fields['date_field'],
//				$fields['title_field']
//			));
//
//		$data->events = array();
//		if ( !empty($records ) ) {
//			foreach ($records as $record) {
//				$event = json_decode($record->dates);
//				$event->title = $record->title;
//
//				$data->events[] = $event;
//			}
//		}
//
//		return $data;
//	}

	public function get_strings() {
		return array(
			'kanban' => __( 'Kanban for WordPress', 'kanban' ),
			'notify' => array(
				'title' => __( 'Kanban for WordPress', 'kanban' ),
				'icon' => Kanban::instance()->settings()->uri . 'img/notify-favicon-250.png'
			),
			'user' => array(
				'updated' => __( 'User updated', 'kanban' ),
				'updated_error' => __( 'There was an error updating the user', 'kanban' ),
			),
			'app'    => array(
				'colorpicker' => Kanban::instance()->settings()->uri . 'img/colors.svg'
				// 'settings_updated' => __( '{0} updated the settings', 'kanban' ),
			),
			'board' => array(
				'added_error'               => __( 'There was an error adding the board', 'kanban' ),
				'retrieve_error' => __( 'There was an error retrieving the board', 'kanban' )
			),
			'lane' => array(
				'updated_error' => __( 'There was an error updating the lane', 'kanban' ),
				'added_error'               => __( 'There was an error adding the lane', 'kanban' ),
			),
			'preset' => array(
				'added_error'               => __( 'There was an error adding the preset', 'kanban' ),
			),
			'card'   => array(
				'added'                     => __( 'Added the card', 'kanban' ),
				'added_error'               => __( 'There was an error adding the card', 'kanban' ),
//				'deleted'                   => __( 'Deleted the card' ),
				'restore'                   => __( 'Card deleted. <u>Undo</u>?', 'kanban' ),
				'undeleted'                 => __( 'Restored the deleted card', 'kanban' ),
				'updates'                   => __( 'Your cards have been synced.', 'kanban' ),
				'updated_error'                => __( 'There was an error saving the card', 'kanban' ),
				'delete_error'              => __( 'There was an error deleting the card', 'kanban' ),
//				'moved_to_lane'             => __( 'Moved the card to "{0}"' ),
//				'moved_to_lane_previous'    => __( ' (previously "{0}")' ),
//				'moved_to_position'         => __( 'Reordered the card' ),
				'lane_empty_confirm'        => __( 'Are you sure you want to delete all cards in this lane?', 'kanban' ),
				'lane_wip_card_limit_error' => __( 'The WIP (Work In Progress) limit has been reached.', 'kanban' ),
			),
			'field'  => array(
				'added_error'               => __( 'There was an error adding the field', 'kanban' ),
				'updated_error' => __( 'There was an error updating the field', 'kanban' ),
//				'updated'              => __( 'Updated "{0}"' ),
//				'updated_with_content' => __( ' to: {0}' ),
//				'updated_previous'     => __( ' Previously: {0}' )
			),
			'comment' => array(
				'updated_error' => __( 'There was an error updating the comment', 'kanban' ),
				'added_error'               => __( 'There was an error adding the comment', 'kanban' ),
				'retrieve_error' => __( 'There was an error retrieving the comments', 'kanban' )
			),
			'general' => array(
				'error' => __( 'There was an error', 'kanban' )
			)
		);
	}
} // Kanban_App


