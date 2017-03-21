<?php
/**
 * Manage addon licenses.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Class Kanban_License
 */
class Kanban_License {

	/**
	 * Setup the class.
	 */
	static function init() {
		add_action( 'init', array( __CLASS__, 'save_licenses' ), 10 );
	}



	/**
	 * Save settings from Kanban > Licenses admin menu page.
	 */
	static function save_licenses() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-licenses' ) || ! is_user_logged_in() ) {
			return;
		}



		do_action( 'kanban_license_save_settings_before', $_POST[ 'licenses' ] );



		if ( isset( $_POST[ 'licenses' ] ) ) {
			if ( Kanban_Utils::is_network() ) {
				update_site_option( 'kanban_addon_licenses', $_POST[ 'licenses' ], false );
			} else {
				update_option( 'kanban_addon_licenses', $_POST[ 'licenses' ], false );
			}

		}


//		$board = Kanban_Board::get_current();

		// Get current settings.
//		$settings = Kanban_Option::get_all( $board->id );

		// Get options table for deleting below.
//		$option_table = Kanban_Option::table_name();

		// Save all single settings.
//		foreach ( $settings as $key => $value ) {
//			// If empty or not license, skip it.
//			if ( substr( $key, 0, 7 ) != 'license' ) {
//				continue;
//			}

		// Delete any previously set licenses.
//			$wpdb->delete( $option_table, array( 'name' => $key ) );

//			Kanban_Option::update_option( $key, $_POST[ 'settings' ][ $key ], 0 );
//		}

		do_action( 'kanban_license_save_settings_after', $_POST[ 'licenses' ] );

		$url = add_query_arg(
			array(
				'message' => urlencode( __( 'Licenses saved', 'kanban' ) ),
			),
			sanitize_text_field( wp_unslash( $_POST[ '_wp_http_referer' ] ) )
		);

		wp_redirect( $url );
		exit;
	}



	/**
	 * Licenses were stored in Kanban_Options table. For multi-site, they're moved
	 * to WordPress options table.
	 */
	static function migrate_licenses() {

		// See if licenses are already stored.
		$kanban_addon_licenses = get_site_option( 'kanban_addon_licenses' );

		// If already stored, skip it.
		if ( !empty($kanban_addon_licenses) ) return;



		global $wpdb;

		$options_table = Kanban_Option::table_name();

		// Get any current licenses
		$license_records = $wpdb->get_results( "
			SELECT *
			FROM $options_table
			WHERE name LIKE 'license_kanban%';
		;" );

		$kanban_addon_licenses = array();

		foreach ( $license_records as $record ) {

			// Remove prefix
			$name = str_replace( 'license_', '', $record->name );

			// Convert to words, and capitalize
			$name = ucwords( str_replace( '-', ' ', $name ) );

			// Add underscores to match class name.
			$name = str_replace( ' ', '_', $name );

			$kanban_addon_licenses[ $name ] = $record->value;
		}

		// Store array of licenses
		if ( Kanban_Utils::is_network() ) {
			update_site_option( 'kanban_addon_licenses', $kanban_addon_licenses, false );
		} else {
			update_option( 'kanban_addon_licenses', $kanban_addon_licenses, false );
		}
	}
}



