<?php
/**
 * Class used by addons for managing licenses.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Class Kanban_Addon_License
 */
class Kanban_Addon_License {


	/**
	 * @var The static calling class.
	 */
	private $parent;



	/**
	 * Kanban_Addon_License constructor.
	 *
	 * @param $parent Static calling class.
	 */
	public function __construct( $parent ) {

		// Store the static reference to the calling addon class.
		$this->parent = $parent;

		// Add the license option field to the addon's options.
		$parent::$options[ 'license_' . $parent::$slug ] = '';

		// Add a "missing license" notice under the plugin on the plugins page.
		add_action(
			'after_plugin_row_' . $parent::$plugin_basename,
			array( $this, 'add_license_check_plugins_page' ),
			100,
			3
		);

		// Add the Kanban > Licenses page.
		add_action( 'admin_menu', array( $this, 'is_licenses_page' ) );

		// Add the license inputs to the Kanban > Licenses page.
		add_filter(
			'kanban_licenses_licenses',
			array( $this, 'add_field_to_licenses_admin_page' )
		);

		// Run a check on the license before saving it.
		add_action(
			'kanban_license_save_settings_before',
			array( $this, 'check_license' )
		);

//		$parent::$update_checker->addQueryArgFilter( array( $this, 'add_license_to_request' ) );
//		$parent::$update_checker->addHttpRequestArgFilter( array( $this, 'add_license_to_request' ) );
	}



	/**
	 * @return string The addon license option name.
	 */
//	public function get_license_name() {
//		$parent = $this->parent;
//
//		return 'license_' . $parent::$slug;
//	}



	/**
	 * Add a notice below the addon on the plugins admin page.
	 *
	 * @param $plugin_file The addon basename.
	 * @param $plugin_data The addon data.
	 * @param $status The status of the addon.
	 */
	public function add_license_check_plugins_page( $plugin_file, $plugin_data, $status ) {
		$parent  = $this->parent;
		$license = $this->get_license();

		if ( ! empty( $license ) ) {
			return;
		}

		$is_newer = false;
		if ( isset( $plugin_data[ 'Version'] ) && isset( $plugin_data[ 'new_version'] ) ) {

			$is_newer = version_compare( $plugin_data[ 'Version'], $plugin_data[ 'new_version'], '<' );
		}

		?>

		<tr class="plugin-update-tr active" id="<?php echo $parent::$slug ?>-license">
			<td colspan="3" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-error notice-alt">
					<p>
						You have not entered a license yet. To get updates, please add your license to <a
							href="<?php echo admin_url( 'admin.php?page=kanban_licenses' ) ?>#tab-licenses">Kanban &gt;
							Licenses</a>!
					</p>
				</div>
			</td>
		</tr>

		<?php if ( $is_newer ) : // Hide the update notice ?>
			<script>
				jQuery( function ( $ ) {
					$( '#<?php echo $parent::$slug ?>-update' ).remove();
				} );
			</script>
		<?php endif; // $is_newer ?>

		<?php
	}



	/**
	 * Add the Kanban > Licenses page.
	 */
	public function is_licenses_page() {
		if ( ! isset( $GLOBALS[ 'submenu'][ 'kanban'] ) ) {
			return;
		}

		$is_licenses_page = false;
		foreach ( $GLOBALS[ 'submenu'][ 'kanban'] as $subpage ) {

			foreach ( $subpage as $option ) {
				if ( $option == 'kanban_licenses' ) {
					$is_licenses_page = true;
					break 2;
				}
			}
		}

		if ( ! $is_licenses_page ) {
			add_submenu_page(
				'kanban',
				'Licenses',
				'Licenses',
				'manage_options',
				'kanban_licenses',
				array( 'Kanban_Admin', 'licenses_page' )
			);
		}
	}



	/**
	 * Add the license inputs to the Kanban > Licenses page
	 *
	 * @param $val Html being sent to the page
	 *
	 * @return Html returned to the page
	 */
	public function add_field_to_licenses_admin_page( $val ) {
		$parent = $this->parent;

		$license = $this->get_license();

		ob_start();

		?>
		<tr>
			<th width="33%" scope="row">
				<label for="license_<?php echo $parent::$slug ?>">
					<?php echo $parent::$friendlyname ?>
				</label>
			</th>
			<td>
				<input name="settings[license_<?php echo $parent::$slug ?>]" id="license_<?php echo $parent::$slug ?>"
				       type="text" value="<?php echo $license ?>" class="large-text">
			</td>
		</tr>

		<?php

		$html_output = ob_get_contents();
		ob_end_clean();

		return $val . $html_output;

	}



	/**
	 * Get the addon license stored in the options table.
	 *
	 * @return mixed License or null.
	 */
	public function get_license() {
		global $wpdb;

		$parent = $this->parent;

		$option_table = Kanban_Option::table_name();

		// get this way, without board
		$license = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `value` FROM $option_table WHERE name = %s ORDER BY `board_id` DESC;",
				'license_' . $parent::$slug
			)
		);

		return $license;
	}



	/**
	 * Check the license when Kanban > licenses admin page is submitted.
	 */
	public function check_license() {
		$parent = $this->parent;

		// Check nonce.
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'kanban-licenses' ) || ! is_user_logged_in() ) {
			return;
		}

		// Make sure the license is set.
		if ( ! isset( $_POST['settings'][ 'license_' . $parent::$slug ] ) || empty( $_POST['settings'][ 'license_' . $parent::$slug ] ) ) {
			return;
		}

		// Get current license.
		$license = $this->get_license();

		// Don't send if the same.
		if ( $license == $_POST['settings'][ 'license_' . $parent::$slug ] ) {
			return;
		}

		try {
			$returned = wp_remote_get( sprintf(
				'https://kanbanwp.com/?action=license-check&license=%s&url=%s&addon=%s',
				$_POST['settings'][ 'license_' . $parent::$slug ],
				site_url(),
				$parent::$slug
			) );

			// @todo show current license status
//			$response = json_decode($returned['body']);


		} catch ( Exception $e ) {
		}
	}
}


