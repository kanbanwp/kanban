<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }



// Kanban_Db::init();
abstract class Kanban_Db
{
	static $installed_ver;



	static function init() {

		// Make sure custom tables exist - HIGHEST PRIORITY!
		add_action( 'plugins_loaded', array( __CLASS__, 'check_for_updates' ), 10, 0 );
	}



	private static function _fetch_sql( $key, $value ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT * FROM %s WHERE %s = %%s',
			self::table_name(),
			$key,
			$value
		);

		return $wpdb->prepare( $sql, $value );
	}



	protected static function _get_row( $key, $value ) {
		global $wpdb;
		return $wpdb->get_row( self::_fetch_sql( $key, $value ) );
	}




	// protected static function _get_all( $sql )
	// {
	// global $wpdb;
	// $records = $wpdb->get_results( $sql, OBJECT );
	// return $records;
	// }
	protected static function _replace( $data ) {
		$data = self::sanitize_data( $data );

		if ( isset( $data->data['id'] ) && (int) $data->data['id'] > 0 ) {
			$success = (bool) self::_update(
				$data->data,
				array( 'id' => $data->data['id'] )
			);
		} else {
			$success = (bool) self::_insert( $data->data );
		}

		return $success;
	}



	protected static function _insert( $data ) {
		$data = self::sanitize_data( $data );

		global $wpdb;
		$success = (bool) $wpdb->insert( static::table_name(), $data->data, $data->format );

		return $success;
	}



	protected static function _update( $data, $where ) {
		$data = self::sanitize_data( $data );

		global $wpdb;
		$success = (bool) $wpdb->update( static::table_name(), $data->data, $where, $data->format );

		return $success;
	}



	protected static function _delete( $where ) {
		global $wpdb;
		$success = $wpdb->delete( static::table_name(), $where );
	}




	protected static function _duplicate( $id, $data = array() ) {
		global $wpdb;
		$record = (array) self::_get_row( 'id', $id );

		// reset
		unset( $record['id'] );

		// update with different values
		$record = array_merge( $record, $data );

		// insert new record
		return self::_insert( $record );
	}


	// get last inserted id
	protected static function _insert_id() {
		global $wpdb;
		return $wpdb->insert_id;
	}



	static function time_to_date( $time ) {
		return gmdate( 'Y-m-d H:i:s', $time );
	}



	static function now() {
		return self::time_to_date( time() );
	}



	static function date_to_time( $date ) {
		return strtotime( $date . ' GMT' );
	}



	public static function table_name() {
		return Kanban_Db::format_table_name( static::$table_name );
	}



	public static function table_columns () {
		return apply_filters(
			'kanban_db_table_columns',
			static::$table_columns
		);
	}



	static function sanitize_data( $data ) {
		$good_data = array();
		$format = array();
		foreach ( $data as $key => $value ) {
			if ( $key == 'id' ) {
				if ( ! is_numeric( $value ) ) { continue; }

				$value = intval( $value );
				if ( empty( $value ) ) { continue; }

				$good_data[ $key ] = $value;
				$format[] = '%d';

				continue;
			}

			if ( ! isset( static::$table_columns ) || ! isset( static::$table_columns[ $key ] ) ) { continue; }

			switch ( static::$table_columns[ $key ] ) {
				case 'bool':
					$value = (bool) $value;

					$good_data[ $key ] = $value;
					$format[] = '%d';

					break;

				case 'float':
					if ( ! is_numeric( $value ) ) { continue; }

					$value = floatval( $value );
					if ( empty( $value ) ) { continue; }

					$good_data[ $key ] = $value;
					$format[] = '%f';

					break;

				case 'int':
					if ( ! is_numeric( $value ) ) { continue; }

					$value = intval( $value );

					if ( ! is_int( $value ) ) { continue; }

					$good_data[ $key ] = $value;
					$format[] = '%d';

					break;

				case 'text':
					// $good_data[$key] = sanitize_text_field( $value );
					// @link https://developer.wordpress.org/reference/functions/sanitize_text_field/
					$value = wp_check_invalid_utf8( $value );
					$value = wp_pre_kses_less_than( $value );
					$good_data[ $key ] = $value;
					$format[] = '%s';

					break;

				case 'datetime':
					if ( is_a( $value, 'DateTime' ) ) {
						$good_data[ $key ] = $value->format( 'Y-m-d H:i:s' );
						$format[] = '%s';
					} elseif ( ($timestamp = strtotime( $value )) !== false ) {
						$dt = new DateTime( $value );
						$good_data[ $key ] = $dt->format( 'Y-m-d H:i:s' );
						$format[] = '%s';
					}

					break;
			} // switch

		}

		return (object) array(
			'data'   => $good_data,
			'format' => $format,
		);

	}



	/**
	 * Triggered on plugins_loaded priority 10
	 * @link http://mac-blog.org.ua/wordpress-custom-database-table-example-full/
	 */
	static function check_for_updates() {

		// See if we're out of sync.
		if ( version_compare( self::installed_ver(), Kanban::get_instance()->settings->plugin_data['Version'] ) === 0 ) { return false; }



		// If installed version is empty, then new install.
		$installed_ver = self::installed_ver();

		if ( empty($installed_ver) ) {

			set_transient(
				sprintf( '_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename ),
				true,
				30
			);
		}



		global $charset_collate, $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Get all classes that have db tables for updating.
		$classes_with_tables = array(
			'Kanban_Board',
			'Kanban_Comment',
			'Kanban_Estimate',
			'Kanban_Option',
			'Kanban_Project',
			'Kanban_Status_Change',
			'Kanban_Status',
			'Kanban_Task',
			'Kanban_Taskmeta',
			'Kanban_Task_Hour',
		);

		// Loop over dbtables, checking for updates.
		foreach ( $classes_with_tables as $class ) {
			$sql = $class::db_table();

			$sql = sprintf( '%s %s;', $sql, $charset_collate );

			// save table
			dbDelta( $sql );
		}

		// Now that we know we have tables, populate them.
		Kanban_Db::add_defaults();



		// House cleaning: move all licenses to the options table.
		Kanban_License::migrate_licenses();



		// Make sure every task has a board.
		$boards_table = Kanban_Board::table_name();

		// Get first board.
		$sql = "SELECT `id`
				FROM `{$boards_table}`
				WHERE `is_active` = 1
				ORDER BY id ASC
				LIMIT 1
		;";

		$board_id = $wpdb->get_var( $sql );

		if ( is_numeric($board_id) && $board_id > 0 ) {
			$classes_with_board_id = array(
				'Kanban_Estimate',
				'Kanban_Status',
				'Kanban_Task',
			);

			foreach ( $classes_with_board_id as $class ) {
				$table = $class::table_name();

				$sql = "UPDATE $table
					SET `board_id` = $board_id
					WHERE `board_id` IS NULL
					OR `board_id` = 0
				;";

				$wpdb->query( $sql );
			}
		}



		// save db version to avoid updates
		update_option( 'kanban_db_version', Kanban::get_instance()->settings->plugin_data['Version'], TRUE );
	}



	static function add_defaults() {
		global $wpdb;

		$boards_table = Kanban_Board::table_name();

		$sql = "SELECT count(`id`)
				FROM `{$boards_table}`
		;";

		$boards_count = $wpdb->get_var( $sql );

		if ( $boards_count == 0 ) {
			$data = array(
				'title'           => 'Kanban board',
				'created_dt_gmt'  => Kanban_Utils::mysql_now_gmt(),
				'modified_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
				'user_id_author'  => get_current_user_id(),
				'is_active'       => 1,
			);

			Kanban_Board::replace( $data );
		}

		// get first board for task
		$sql = "SELECT `id`
				FROM `{$boards_table}`
				ORDER BY id
				LIMIT 1
		;";

		$board_id = $wpdb->get_var( $sql );



		$allowed_users = kanban_Option::get_option( 'allowed_users', $board_id );

		if ( empty( $allowed_users ) ) {

			// Add current user, why not.
			$allowed_users = array( get_current_user_id() );

			// Make sure the site admin user is added.
			if ( Kanban_Utils::is_network() ) {
				$admin_email = get_option('admin_email');

				$user = get_user_by('email', $admin_email);

				if ( !empty($user) ) {
					$allowed_users[] = $user->ID;
					array_unique($allowed_users);
				}
			}

			Kanban_Option::update_option( 'allowed_users', $allowed_users, $board_id );
		}

//
//
//		$status_table = Kanban_Status::table_name();
//
//		$sql = "SELECT count(`id`)
//				FROM `{$status_table}`
//		;";
//
//		$status_count = $wpdb->get_var( $sql );
//
//		if ( $status_count == 0 ) {
//			$statuses = array(
//				'Backlog'     => '#8224e3',
//				'Ready'       => '#eeee22',
//				'In progress' => '#81d742',
//				'QA'          => '#f7a738',
//				'Done'        => '#1e73be',
//				'Archive'     => '#333333',
//			);
//
//			$i = 0;
//			foreach ( $statuses as $status => $color ) {
//				$data = array(
//					'title'     => $status,
//					'color_hex' => $color,
//					'position'  => $i,
//					'board_id'  => $board_id,
//				);
//
//				Kanban_Status::replace( $data );
//
//				$i++;
//			}
//		}
//
//		// get second status for task
//		$sql = "SELECT `id`
//				FROM `{$status_table}`
//				ORDER BY id
//				LIMIT 1, 1
//		;";
//
//		$status_id = $wpdb->get_var( $sql );
//
//		$estimate_table = Kanban_Estimate::table_name();
//
//		$sql = "SELECT count(`id`)
//				FROM `{$estimate_table}`
//		;";
//
//		$estimate_count = $wpdb->get_var( $sql );
//
//		if ( $estimate_count == 0 ) {
//			$estimates = array(
//					'2'  => '2h',
//					'4'  => '4h',
//					'8'  => '1d',
//					'16' => '2d',
//					'32' => '4d',
//				);
//
//			$i = 0;
//			foreach ( $estimates as $hours => $title ) {
//				$data = array(
//					'title'    => $title,
//					'hours'    => $hours,
//					'position' => $i,
//					'board_id'  => $board_id,
//				);
//
//				Kanban_Estimate::replace( $data );
//
//				$i++;
//			}
//		}
//
//		$tasks_table = Kanban_Task::table_name();
//
//		$sql = "SELECT count(`id`)
//				FROM `{$tasks_table}`
//		;";
//
//		$tasks_count = $wpdb->get_var( $sql );
//
//		if ( $tasks_count == 0 ) {
//			$data = array(
//				'title'           => 'Your first task',
//				'board_id'        => $board_id,
//				'status_id'		  => $status_id,
//				'created_dt_gmt'  => Kanban_Utils::mysql_now_gmt(),
//				'modified_dt_gmt' => Kanban_Utils::mysql_now_gmt(),
//				'user_id_author'  => get_current_user_id(),
//				'is_active'       => 1,
//			);
//
//			Kanban_Task::replace( $data );
//		}



		// Make sure all options are in the db.
		$options_table = Kanban_Option::table_name();

		// Get all existing options.
		$sql = "SELECT *
				FROM `{$options_table}`
		;";

		$options = $wpdb->get_results( $sql );

		// Build an array of existing options.
		$options_arr = array();
		foreach ( $options as $option ) {
			$options_arr[ $option->name ] = $option->value;
		}

		// Get all defaults.
		$defaults = Kanban_Option::get_defaults();

		// Loop over the defaults.
		foreach ( $defaults as $key => $value ) {

			// If default is already a stored option, skip it.
			if ( isset( $options_arr[ $key ] ) ) { continue; }

			// Otherwise, store it.
			Kanban_Option::update_option( $key, $value );
		}



		return true;
	}


	/**
	 * get the stored db version
	 *
	 * @return float the current stored version
	 */
	static function installed_ver() {
		// if it hasn't been loaded yet
		if ( ! isset( self::$installed_ver ) ) {
			// get it from the db, and store it
			self::$installed_ver = get_option( 'kanban_db_version' );
		}

		// return the stored db version
		return self::$installed_ver;
	}



	/**
	 * build the table name with "namespacing"
	 *
	 * @param  string $table the classname for the table
	 * @return string        the complete table name
	 */
	static function format_table_name( $table ) {
		global $wpdb;

		return sprintf(
			'%s%s_%s',
			$wpdb->prefix,
			Kanban::get_instance()->settings->basename,
			$table
		);
	}



	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	// public static function get_instance()
	// {
	// if ( ! self::$instance )
	// {
	// self::$instance = new self();
	// }
	// return self::$instance;
	// }
	/**
	 * construct that can't be overwritten
	 */
	private function __construct() { }
}
