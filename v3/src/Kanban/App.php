<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Kanban_App {

	// the instance of this object
	private static $instance;


	private $slugs = array(
		'board',
		'login',
		'ajax',
		'reports',
		'test'
	);


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

			add_action( 'plugins_loaded', array( self::$instance, 'maybe_load_page' ) );
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
				Kanban_User_Option::instance()->ajax_replace_app( array(
					'option' => 'current_board',
					'value'  => $current_user->options->app['current_board']
				) );
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

	public function get_strings() {
		return array(
			'kanban' => __( 'Kanban' ),
			'app'    => array(
				'settings_updated' => __( '{0} updated the settings' ),
			),
			'card'   => array(
				'added'                     => __( 'Added the card' ),
//				'deleted'                   => __( 'Deleted the card' ),
				'restore'                   => __( 'Card deleted. <u>Undo</u>?' ),
				'undeleted'                 => __( 'Restored the deleted card' ),
				'updates'                   => __( 'Your cards have been synced.' ),
				'added_error'               => __( 'There was an error adding a card' ),
				'save_error'                => __( 'There was an error saving that card' ),
				'delete_error'              => __( 'There was an error deleting that card' ),
//				'moved_to_lane'             => __( 'Moved the card to "{0}"' ),
//				'moved_to_lane_previous'    => __( ' (previously "{0}")' ),
//				'moved_to_position'         => __( 'Reordered the card' ),
				'lane_empty_confirm'        => __( 'Are you sure you want to delete all cards in this lane?' ),
				'lane_wip_card_limit_error' => __( 'The WIP (Work In Progress) limit has been reached.' ),
			),
			'field'  => array(
//				'updated'              => __( 'Updated "{0}"' ),
//				'updated_with_content' => __( ' to: {0}' ),
//				'updated_previous'     => __( ' Previously: {0}' )
			)
		);
	}
} // Kanban_App


