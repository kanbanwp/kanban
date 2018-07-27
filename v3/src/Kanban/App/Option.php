<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Kanban_App_Option extends Kanban_Abstract {

	private static $instance;

	/**
	 * @var array Database table fields and types for filtering.
	 */
	protected $fields = array(
		'id'               => '%d',
		'created_dt_gmt'   => '%s',
		'created_user_id'  => '%s',
		'modified_dt_gmt'  => '%s',
		'modified_user_id' => '%d',
		'options'          => '%s',
		'is_active'        => '%d',
	);

	// the instance of this object
	/**
	 * @var string database table name.
	 */
	protected $table = 'app_options';

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

	public function ajax_replace( $data ) {

		if ( !Kanban_User::instance()->current_user_has_cap('admin') ) {
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


		$row = $this->get_row();

		if ( empty( $row ) ) {
			$row          = (object) array();
			$row->options = array();
		}

		if ( ! is_array( $row->options ) ) {
			$row->options = array();
		}

		$row->options[ $data['option'] ] = $data['value'];
		$row->options                    = $this->format_options_for_db( $row->options );

		$row = $this->set_row( $row );

		return $row;
	}

	public function get_row( $id = 0 ) {
		global $wpdb;

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$row = $wpdb->get_row(
			"
					SELECT $table.* 
					FROM $table
				;",
			OBJECT
		);

		if ( empty( $row ) ) {
			return (object) array(
				'options' => array()
			);
		}

		$row = $this->format_data_for_app( $row );

		return $row;
	}

	public function format_options_for_db( $options ) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'site_takeover':
					$value = $this->format_bool_for_db( $value );
					break;
				case 'notification_from_name':
					$value = $this->format_string_for_db( $value );
					$value = preg_replace("/[^a-zA-Z0-9]+/", "", $value);
					break;
				case 'notification_from_email':
					$value = $this->format_string_for_db( $value );
					if ( !is_email($value) ) {
						$value = '';
					}
					break;
				default:
					unset( $options[ $key ] );
					break;
			}
		}

		return $options;
	}

	public function format_options_for_app( $options ) {

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'site_takeover':
					$value = $this->format_bool_for_app( $value );
					break;
				case 'notification_from_name':
					$value = $this->format_string_for_app( $value );
					break;
				case 'notification_from_email':
					$value = $this->format_string_for_app( $value );
					break;

			}
		}

		return (array) $options;
	}

	public function format_data_for_app( $row ) {

		$row = parent::format_data_for_app( $row );

		if ( empty($row) ) return array();

		foreach ( $row as $key => &$value ) {
			switch ( $key ) {
				case 'options':
					$value = $this->format_json_for_app( $value );
					$value = $this->format_options_for_app( $value );
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
				case 'options':
					$value = $this->format_options_for_db( $value );
					$value = $this->format_json_for_db( $value );
					break;
			}
		}

		return $row;
	} // format_data_for_db


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
					options text DEFAULT '' NOT NULL,
					PRIMARY KEY  (id),
					KEY is_active (is_active)
				)";
	} // get_create_table_sql


} // Kanban_App


