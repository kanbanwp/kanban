<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Option::init();



class Kanban_Option extends Kanban_Db
{
	// the instance of this object
	private static $instance;

	// the table name of this class
	protected static $table_name = 'options';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'name'  => 'text',
		'value' => 'text'
	);

	// defaults for all options, so at least something is returned
	protected static $defaults = array (
		'hour_interval' => '1',
		'allowed_users' => '',
		'show_all_cols' => 0,
		'default_to_compact_view' => 0,
		'hide_progress_bar' => 0
	);

	// store the options on first load, to prevent mulitple db calls
	protected static $options = array();
	protected static $options_raw = array();



	static function init()
	{
		add_action( 'init', array( __CLASS__, 'save_settings' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_js' ) );

	}



	// extend parent, so it's accessible from other classes
	static function replace( $data )
	{
		return self::_replace( $data );
	}



	static function update( $key, $value )
	{
		if ( is_array( $value ) || is_object( $value ) )
		{
			$value = serialize( $value );
		}


		$data = (object) array(
			'name'  => $key,
			'value' => $value
		);

		$option = self::get_option_raw( $key );

		if ( $option )
		{
			$data->id = $option->id;
		}

		return self::_replace( $data );
	}



	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					name varchar(64) NOT NULL,
					value longtext NOT NULL,
					UNIQUE KEY  (id)
				)';
	} // db_table



	static function get_defaults()
	{
		// make sure there's always at least one user
		self::$defaults['allowed_users'] = serialize( array( get_current_user_id() ) );


		return apply_filters( 'kanban_option_get_defaults_return', self::$defaults );
	}



	static function get_all_raw()
	{
		if ( empty( self::$options_raw ) )
		{

			$table_name = self::table_name();

			$sql = "SELECT *
					FROM `{$table_name}`
			;";

			$sql = apply_filters( 'kanban_option_get_all_raw_sql', $sql );

			self::$options_raw = parent::get_all( $sql );

			self::$options_raw = Kanban_Utils::build_array_with_id_keys( self::$options_raw, 'id' );

			// unserialize arrays
			foreach ( self::$options_raw as $key => $record )
			{
				if ( ! is_serialized( $record->value ) ) continue;

				self::$options_raw[$key]->value = unserialize( $record->value );
			}
		}

		return apply_filters(
			'kanban_option_get_all_raw_return',
			self::$options_raw
		);
	}



	static function get_all( $sql = NULL )
	{
		if ( empty( self::$options ) )
		{
			$records = self::get_all_raw();

			$output = array();
			foreach ( $records as $record )
			{
				if ( is_serialized( $record->value ) )
				{
					$record->value = unserialize( $record->value );
				}

				$output[$record->name] = $record->value;
			}

			self::$options = array_merge( self::get_defaults(), $output );
		}

		return self::$options;
	}



	static function get_option( $name )
	{
		$options = self::get_all();

		if ( ! isset( $options[$name] ) )
		{
			return null;
		}

		return $options[$name];
	}



	static function get_option_raw( $option_name )
	{
		foreach ( self::get_all_raw() as $option )
		{
			if ( $option->name == $option_name )
			{
				return $option;
				break;
			}
		}

		return FALSE;
	}



	static function enqueue_js( $hook )
	{
		if ( ! is_admin() || (isset( $_GET['page'] ) && $_GET['page'] != sprintf( '%s_settings', Kanban::get_instance()->settings->basename )) ) return;

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			'jquery-ui',
			'//code.jquery.com/ui/1.11.4/jquery-ui.js',
			array()
		);

		wp_enqueue_script(
			't',
			sprintf( '%s/js/t.min.js', Kanban::get_instance()->settings->uri ),
			array()
		);


		wp_enqueue_script(
			sprintf( '%s_settings', Kanban::get_instance()->settings->basename ),
			sprintf( '%s/js/admin-settings.js', Kanban::get_instance()->settings->uri ),
			array( 'wp-color-picker' ),
			false,
			true
		);
	}



	static function settings_page()
	{
		$settings = Kanban_Option::get_all();
		if ( is_serialized( $settings['allowed_users'] ) )
		{
			$settings['allowed_users'] = unserialize( $settings['allowed_users'] );
		}

		$all_users = get_users();
		$all_users_arr = array();
		foreach ( $all_users as $user )
		{
			$all_users_arr[$user->ID] = Kanban_User::get_username_long( $user );
		}

		$statuses = Kanban_Status::get_all();
		$statuses = Kanban_Utils::order_array_of_objects_by_property ( $statuses, 'position', 'int' );

		$estimates = Kanban_Estimate::get_all();
		$estimates = Kanban_Utils::order_array_of_objects_by_property ( $estimates, 'position', 'int' );

		$template = Kanban_Template::find_template( 'admin/settings' );

		include_once $template;
	}



	static function save_settings()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-options' ) || ! is_user_logged_in() ) return;



		do_action( 'kanban_option_save_settings_before', $_POST );



		$statuses = Kanban_Status::get_all();
		$status_ids = array_keys( $statuses );



		// any statuses to delete?
		if ( isset( $_POST['statuses']['saved'] ) )
		{
			$deleted_statuses = array_diff( $status_ids, array_keys( $_POST['statuses']['saved'] ) );

			if ( ! empty( $deleted_statuses ) )
			{
				foreach ( $deleted_statuses as $key => $id )
				{
					Kanban_Status::delete( array( 'id' => $id ) );
				}
			}
		}



		// add new statuses first
		if ( isset( $_POST['statuses']['new'] ) )
		{
			foreach ( $_POST['statuses']['new'] as $status )
			{
				// save it
				$success = Kanban_Status::replace( $status );

				if ( $success )
				{
					$status_id = Kanban_Status::insert_id();

					// add it to all the statuses to save
					$_POST['statuses']['saved'][$status_id] = $status;
				}
			}
		}



		// now save all statuses with positions
		if ( isset( $_POST['statuses']['saved'] ) )
		{
			foreach ( $_POST['statuses']['saved'] as $status_id => $status )
			{
				$status['id'] = $status_id;

				Kanban_Status::replace( $status );
			}
		}



		$estimates = Kanban_Estimate::get_all();
		$estimate_ids = array_keys( $estimates );



		// any estimates to delete?
		if ( isset( $_POST['estimates']['saved'] ) )
		{
			$deleted_estimates = array_diff( $estimate_ids, array_keys( $_POST['estimates']['saved'] ) );

			if ( ! empty( $deleted_estimates ) )
			{
				foreach ( $deleted_estimates as $key => $id )
				{
					Kanban_Estimate::delete( array( 'id' => $id ) );
				}
			}
		}



		// add new estimates first
		if ( isset( $_POST['estimates']['new'] ) )
		{
			foreach ( $_POST['estimates']['new'] as $estimate )
			{
				// save it
				$success = Kanban_Estimate::replace( $estimate );

				if ( $success )
				{
					$estimate_id = Kanban_Estimate::insert_id();

					// add it to all the estimates to save
					$_POST['estimates']['saved'][$estimate_id] = $estimate;
				}
			}
		}



		// now save all estimates with positions
		if ( isset( $_POST['estimates']['saved'] ) )
		{
			foreach ( $_POST['estimates']['saved'] as $estimate_id => $estimate )
			{
				$estimate['id'] = $estimate_id;

				Kanban_Estimate::replace( $estimate );
			}
		}



		// get current settings
		$settings = Kanban_Option::get_all_raw();
		$settings = Kanban_Utils::build_array_with_id_keys( $settings );



		// save all single settings
		foreach ( $_POST['settings'] as $key => $value )
		{
			if ( is_array( $value ) )
			{
				$value = serialize( $value );
			}

			$data = array(
				'name'  => $key,
				'value' => $value
			);

			// see if it's already set
			$id = Kanban_Utils::find_key_of_object_by_property ( 'name', $key, $settings );

			if ( $id )
			{
				$data['id'] = $id;
			}

			Kanban_Option::_replace( $data );
		}



		$url = add_query_arg(
			array(
				'message' => urlencode( __( 'Settings saved', 'kanban' ) )
			),
			$_POST['_wp_http_referer']
		);

		wp_redirect( $url );
		exit;
	}



	/**
	 * get the instance of this class
	 * @return object the instance
	 */
	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}



	/**
	 * construct that can't be overwritten
	 */
	private function __construct() { }

}
