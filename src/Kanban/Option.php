<?php

/**
 * For saving and getting Kanban-specific options.
 */



// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Class Kanban_Option
 */
class Kanban_Option extends Kanban_Db {

	// The table name of this class.
	protected static $table_name = 'options';

	// Define db table columns and their validation type.
	protected static $table_columns = array(
		'name'     => 'text',
		'value'    => 'text',
		'board_id' => 'int',
	);

	// Defaults for all options, so at least something is returned.
	protected static $defaults = array(
		'hour_interval'               => '1',
		'allowed_users'               => array(),
		'show_all_cols'               => 0,
		// 'default_to_compact_view' => 0,
		'hide_progress_bar'           => 0,
		'use_default_login_page'      => 0,
		'default_estimate'            => '',
		'default_assigned_to'         => '',
		'default_assigned_to_creator' => 0,
		'default_assigned_to_first'   => 0,
		'status_auto_archive'         => array(),
		'status_auto_archive_days'    => 30, // days
		'hide_time_tracking'          => 0,
		'show_task_ids'               => 0,
		'board_css'                   => '',
		'disable_sync_notifications'  => 0,
		'updates_check_interval_sec'  => 5,
	);

	// Store the options on first load, to prevent mulitple db calls.
	protected static $options_by_board = array();
	protected static $records = array();
	protected static $records_by_board = array();



	/**
	 * Init the class
	 */
	static function init() {
		add_action( 'init', array( __CLASS__, 'save_settings' ), 10 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_js' ) );
	}



	/**
	 * Define the db schema.
	 *
	 * @return string Sql statement.
	 */
	static function db_table() {
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					name varchar(64) NOT NULL,
					value longtext NOT NULL,
					board_id bigint(20) NOT NULL DEFAULT 1,
					UNIQUE KEY id (id)
				)';
	}



	// Get the defaults for the class.
	static function get_defaults() {
		return apply_filters( 'kanban_option_get_defaults_return', self::$defaults );
	}



	/**
	 * Get a single default.
	 *
	 * @param $key The default to get.
	 *
	 * @return mixed False or the default option.
	 */
	static function get_default( $key ) {
		$defaults = self::get_defaults();

		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : false;
	}



	/**
	 * Get the options as db records.
	 *
	 * @param null $board_id The board id.
	 *
	 * @return mixed An array of db results.
	 */
	static function get_results( $board_id = null ) {
		if ( empty( self::$records ) ) {
			$sql = apply_filters( 'kanban_option_get_results_sql', 'SELECT * FROM `%s`;' );

			global $wpdb;
			self::$records = $wpdb->get_results(
				sprintf(
					$sql,
					self::table_name()
				)
			);

			self::$records = Kanban_Utils::build_array_with_id_keys( self::$records, 'id' );

			// Unserialize arrays.
			foreach ( self::$records as $key => $record ) {
				self::$records[ $key ]->value = maybe_unserialize( $record->value );
			}

			$boards                 = Kanban_Board::get_all();
			self::$records_by_board = array_fill_keys( array_keys( $boards ), array() );

			foreach ( self::$records as $key => $record ) {
				if ( ! isset( self::$records_by_board[ $record->board_id ] ) ) {
					continue;
				}
				self::$records_by_board[ $record->board_id ][ $key ] = $record;
			}
		}

		if ( ! is_numeric( $board_id ) ) {
			return apply_filters(
				'kanban_option_get_results_return',
				self::$records
			);
		}

		return apply_filters(
			'kanban_option_get_results_by_board_return',
			isset( self::$records_by_board[ $board_id ] ) ? self::$records_by_board[ $board_id ] : array(),
			$board_id
		);
	}



	/**
	 * Get the option as a db record.
	 *
	 * @param null $board_id The board id.
	 *
	 * @return array|mixed The option as a db record.
	 */
	static function get_results_by_board( $board_id = null ) {
		if ( ! is_numeric( $board_id ) ) {
			self::get_results();

			return self::$records_by_board;
		} else {
			return self::get_results( $board_id );
		}
	}



	/**
	 * Get all the options for a board.
	 *
	 * @param null $board_id The board id.
	 *
	 * @return array The options.
	 */
	static function get_all( $board_id = null ) {

		$boards = Kanban_Board::get_all();

		if ( is_numeric( $boards ) && ! isset( $boards[ $board_id ] ) ) {
			return array();
		}

		if ( empty( self::$options_by_board ) ) {
			foreach ( self::get_results_by_board() as $record_board_id => $records ) {
				self::$options_by_board[ $record_board_id ] = array();

				foreach ( $records as $record ) {
					self::$options_by_board[ $record_board_id ][ $record->name ] = $record->value;
				}
			}

			foreach ( $boards as $board ) {

				$id = $board->id;

				if ( ! isset( self::$options_by_board[ $id ] ) ) {
					self::$options_by_board[ $id ] = array();
				}

				self::$options_by_board[ $id ] = array_merge( self::get_defaults(), self::$options_by_board[ $id ] );
			}
		}

		if ( ! is_numeric( $board_id ) ) {
			return apply_filters(
				'kanban_option_get_all_return',
				self::$options_by_board
			);
		}

		return apply_filters(
			'kanban_option_get_all_by_board_return',
			self::$options_by_board[ $board_id ]
		);
	}



	/**
	 * Get a single option.
	 *
	 * @param $name The name of the option.
	 * @param null $board_id The board id.
	 *
	 * @return mixed The option.
	 */
	static function get_option( $name, $board_id = null ) {
		if ( ! is_numeric( $board_id ) ) {
			$board = Kanban_Board::get_current();

			$board_id = $board->id;
		}

		$options = self::get_all( $board_id );

		if ( ! isset( $options[ $name ] ) ) {
			return self::get_default( $name );
		}

		return $options[ $name ];
	}



	/**
	 * Update a single option.
	 *
	 * @param $key The name of the option.
	 * @param $value The new value.
	 * @param int $board_id The board id.
	 */
	static function update_option( $key, $value, $board_id = null ) {
		if ( ! is_numeric( $board_id ) ) {
			$board    = Kanban_Board::get_current();
			$board_id = $board->id;
		}

		$data = (object) array(
			'name'     => $key,
			'value'    => maybe_serialize( $value ),
			'board_id' => $board_id,
		);

		$option = self::get_row_by( 'name', $key, $board_id );

		if ( $option ) {
			$data->id = $option->id;
		}

		$good_data = self::sanitize_data( $data );

		global $wpdb;
		$success = $wpdb->replace(
			self::table_name(),
			$good_data->data,
			$good_data->format
		);
	}



	static function delete_option ($key) {
		global $wpdb;
		$success = $wpdb->delete(
			self::table_name(),
			array('name' => $key)
		);

		return $success;
	}



	/**
	 * Get an option by some other property instead of name.
	 *
	 * @param $key The property name to get an option.
	 * @param $value The value the property should match.
	 * @param null $board_id THe board id.
	 *
	 * @return mixed|null The value of the matched option.
	 */
	static function get_row_by( $key, $value, $board_id = null ) {
		if ( ! is_numeric( $board_id ) ) {
			$board    = Kanban_Board::get_current();
			$board_id = $board->id;
		}

		// Make sure it's there.
		$results = self::get_results_by_board( $board_id );

		// If it's not, maybe it's a new board. reset and try again.
		if ( ! isset( $results ) ) {
			self::$options_by_board = array();
			self::$records          = array();
			self::$records_by_board = array();
		}

		$results = self::get_results_by_board( $board_id );

		// If it's not there, DENIED.
		if ( ! isset( $results ) ) {
			return null;
		}

		foreach ( self::get_results_by_board( $board_id ) as $option ) {
			if ( $option->$key == $value ) {
				return $option;
				break;
			}
		}

		return null;
	}



	/**
	 * @param $hook string The necessary hook.
	 */
	static function enqueue_js( $hook ) {
		if ( ! is_admin() || ! isset( $_GET[ 'page' ] ) || substr($_GET[ 'page' ], 0, 6) != 'kanban' ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			'jquery-ui',
			'//code.jquery.com/ui/1.11.4/jquery-ui.js',
			array()
		);

		wp_enqueue_script(
			't',
			sprintf( '%s/js/min/t-min.js', Kanban::get_instance()->settings->uri ),
			array()
		);

		wp_enqueue_script(
			'list',
			sprintf( '%s/js/list.min.js', Kanban::get_instance()->settings->uri )
		);

		// Add thickbox for extra options.
		add_thickbox();

		wp_register_script(
			'kanban-settings',
			sprintf( '%s/js/admin-settings.min.js', Kanban::get_instance()->settings->uri ),
			array( 'wp-color-picker' ),
			false,
			true
		);

		$translation_array = array(
			'url_contact'             => admin_url(),
			'url_board'               => Kanban_Template::get_uri(),
			Kanban_Utils::get_nonce() => wp_create_nonce( 'kanban-admin-comment' ),
		);
		wp_localize_script( 'kanban-settings', 'kanban', $translation_array );

		wp_enqueue_script( 'kanban-settings' );
	}



	/**
	 * Render the admin settings page.
	 */
	static function settings_page() {

		wp_enqueue_style(
			'kanban',
			sprintf( '%s/css/admin.css', Kanban::get_instance()->settings->uri )
		);



		// Get the current board.
		$board = Kanban_Board::get_current_by( 'GET' );

		// Get all the settings for display.
		$settings = Kanban_Option::get_all( $board->id );

		// Get all users to popualte user settings.
		$all_users     = get_users();
		$all_users_arr = array();
		foreach ( $all_users as $user ) {
			$all_users_arr[ $user->ID ] = Kanban_User::get_username_long( $user );
		}

		// Get all statuses to populate status settings.
		$statuses = Kanban_Status::get_all( $board->id );
		$statuses = Kanban_Utils::order_array_of_objects_by_property( $statuses, 'position', 'int' );

		// Get all estimates to populate $estimate settings.
		$estimates = Kanban_Estimate::get_all( $board->id );
		$estimates = Kanban_Utils::order_array_of_objects_by_property( $estimates, 'position', 'int' );

		// Get the template.
		$template = Kanban_Template::find_template( 'admin/settings' );

		include_once $template;
	}



	/**
	 * Save the settings from admin.
	 */
	static function save_settings() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-options' ) || ! is_user_logged_in() ) {
			return;
		}

		do_action( 'kanban_option_save_settings_before', $_POST );

		$board = Kanban_Board::get_current_by( 'POST' );

		// Get defaults.
		$defaults = self::get_defaults();

		// Save all single settings.
		foreach ( $defaults as $key => $value ) {

			// If empty.
			if ( ! isset( $_POST[ 'settings' ][ $key ] ) ) {

				// Try to use default.
				if ( isset( $defaults[ $key ] ) ) {
					$_POST[ 'settings' ][ $key ] = $defaults[ $key ];
				} else {
					continue;
				}
			}

			// Update the option.
			Kanban_Option::update_option(
				$key,
				$_POST[ 'settings' ][ $key ]
			);
		}

		do_action( 'kanban_option_save_settings_after', $_POST );

		$url = add_query_arg(
			array(
				'message' => urlencode( __( 'Settings saved', 'kanban' ) ),
			),
			sanitize_text_field( wp_unslash( $_POST[ '_wp_http_referer' ] ) )
		);

		wp_redirect( $url );
		exit;
	}



	/**
	 * Construct that can't be overwritten.
	 */
	private function __construct() {
	}
}
