<?php


class Kanban_Db {

	// the instance of this object
	private static $instance;


	private $installed_ver;


	public function prefix() {
		global $wpdb;

		return sprintf(
			'%s%s3_',
			$wpdb->prefix,
			Kanban::instance()->settings()->basename
		);
	}

	public function classes_with_tables() {
		return array(
			'Kanban_App_Option',
			'Kanban_Board',
			'Kanban_Lane',
			'Kanban_Card',
			'Kanban_Card_Log',
			'Kanban_Card_User',
			'Kanban_Field',
			'Kanban_Fieldvalue',
			'Kanban_Fieldvalue_Log',
			'Kanban_User_Cap',
			'Kanban_User_Option',
			'Kanban_Usergroup',
			'Kanban_Comment',
		);
	}


	/**
	 * Triggered on plugins_loaded priority 10
	 * @link http://mac-blog.org.ua/wordpress-custom-database-table-example-full/
	 */
	static function check_for_updates() {

		// See if we're out of sync.
		if ( version_compare( self::installed_ver(), Kanban::instance()->settings()->plugin_data['Version'] ) == 0 ) {
			return false;
		}

		// If installed version is empty, then new install.
		// @todo Move this somewhere more intuitive.
		$installed_ver = self::installed_ver();

		global $wpdb;

		// Install/update db tables.
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Loop over dbtables, checking for updates.
		foreach ( self::instance()->classes_with_tables() as $class ) {
			$sql = $class::instance()->get_create_table_sql();

			$sql = sprintf( '%s %s;', $sql, $charset_collate );

			// save table
			dbDelta( $sql );
		}

		// New install functionality.
		$users_table = Kanban_Db::instance()->prefix() . Kanban_User_Cap::instance()->get_table();

		$user_count = $wpdb->get_var("
			SELECT count(id)
			FROM $users_table
		;");

		if ( $user_count == 0 ) {

			// Set current user as admin.
			$user = get_user_by( 'id', get_current_user_id() );
			Kanban_User_Cap::instance()->add_admin( $user, array( 'admin' ) );
		}

			// @todo welcome page
//			set_transient(
//				sprintf( '_%s_welcome_screen_activation_redirect', Kanban::instance()->settings()->basename ),
//				true,
//				30
//			);
//		}

		// save db version to avoid updates
		update_option(
			'kanban_db_version',
			Kanban::instance()->settings()->plugin_data['Version'],
			true
		);
	}


	/**
	 * get the stored db version
	 *
	 * @return float the current stored version
	 */
	public function installed_ver() {
		// if it hasn't been loaded yet
		if ( ! isset( self::instance()->installed_ver ) ) {
			// get it from the db, and store it
			self::instance()->installed_ver = get_option( 'kanban_db_version' );
		}

		// return the stored db version
		return self::instance()->installed_ver;
	}


	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

			add_action( 'plugins_loaded', array( self::$instance, 'check_for_updates' ), 0, 0 );

		}

		return self::$instance;
	}

}