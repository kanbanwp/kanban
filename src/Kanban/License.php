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

		global $wpdb;

		do_action( 'kanban_license_save_settings_before', $_POST );

		$board = Kanban_Board::get_current();

		// Get current settings.
		$settings = Kanban_Option::get_all( $board->id );

		// Get options table for deleting below.
		$option_table = Kanban_Option::table_name();

		// Save all single settings.
		foreach ( $settings as $key => $value ) {
			// If empty or not license, skip it.
			if ( substr( $key, 0, 7 ) != 'license' ) {
				continue;
			}

			// Delete any previously set licenses.
			$wpdb->delete( $option_table, array( 'name' => $key ) );

			Kanban_Option::update_option( $key, $_POST[ 'settings' ][ $key ], 0 );
		}

		do_action( 'kanban_license_save_settings_after', $_POST );

		$url = add_query_arg(
			array(
				'message' => urlencode( __( 'Licenses saved', 'kanban' ) ),
			),
			sanitize_text_field( wp_unslash( $_POST[ '_wp_http_referer' ] ) )
		);

		wp_redirect( $url );
		exit;
	}
}



