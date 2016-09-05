<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



class Kanban_Addon_License
{
	private $parent;


	public function __construct ($parent)
	{

		$this->parent = $parent;

		$parent::$options[$this->get_license_name()] = '';

		add_action(
			"after_plugin_row_" . $parent::$plugin_basename,
			array($this, 'add_license_check_plugins_page'),
			100,
			3
		);

		add_action( 'admin_menu', array( $this, 'is_licenses_page' ) );

		// add add-on settings to settings page
		add_filter(
			'kanban_licenses_licenses',
			array($this, 'add_settings_license')
		);

		add_action(
			'kanban_license_save_settings_before',
			array($this, 'check_license')
		);

		$parent::$update_checker->addQueryArgFilter(array($this, 'add_license_to_request'));
		$parent::$update_checker->addHttpRequestArgFilter(array($this, 'add_license_to_request'));
	}



	public function add_license_to_request($args)
	{
		$license = $this->get_license();
		$args['license_key'] = $license;

		$site_url = site_url();
		$args['site_url'] = $site_url;

		return $args;
	}



	public function get_license_name ()
	{
		$parent = $this->parent;
		return 'license_' . $parent::$slug;
	}



	public function add_license_check_plugins_page ($plugin_file, $plugin_data, $status)
	{
		$parent = $this->parent;
		$license = $this->get_license();

		if ( !empty($license) ) return;

		$is_newer = FALSE;
		if ( isset($plugin_data['Version']) && isset($plugin_data['new_version']) ) {

			$is_newer = version_compare( $plugin_data['Version'], $plugin_data['new_version'], '<' );
		}

		?>

		<tr class="plugin-update-tr active" id="<?php echo $parent::$slug ?>-license">
			<td colspan="3" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-error notice-alt">
					<p>
						You have not entered a license yet. To get updates, please add your license to <a href="<?php echo admin_url('admin.php?page=kanban_licenses') ?>#tab-licenses">Kanban &gt; Licenses</a>!
					</p>
				</div>
			</td>
		</tr>

		<?php if ( $is_newer ) : ?>
		<script>
			jQuery(function($){
				$('#<?php echo $parent::$slug ?>-update').remove();
			});
		</script>
		<?php endif; // $is_newer ?>

		<?php
	}



	public function is_licenses_page()
	{
		if ( !isset($GLOBALS['submenu']['kanban']) ) return;

		$is_licenses_page = FALSE;
		foreach ( $GLOBALS['submenu']['kanban'] as $subpage)
		{

			foreach ($subpage as $option)
			{
				if ( $option == 'kanban_licenses' )
				{
					$is_licenses_page = TRUE;
					break 2;
				}
			}
		}

		if ( !$is_licenses_page )
		{
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
	 * Add the license inputs to the licenses settings page
	 *
	 * @param $val Html being sent to the page
	 *
	 * @return Html returned to the page
	 */
	public function add_settings_license ($val)
	{
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
					<input name="settings[license_<?php echo $parent::$slug ?>]" id="license_<?php echo $parent::$slug ?>" type="text" value="<?php echo $license ?>" class="large-text">
				</td>
			</tr>

		<?php

		$html_output = ob_get_contents();
		ob_end_clean();



		return $val . $html_output;

	}



	public function get_license ()
	{
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



	public function check_license ()
	{
		$parent = $this->parent;

		// check nonce
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-licenses') || !is_user_logged_in() ) return;

		// make sure the license is set
		if ( !isset($_POST['settings']['license_' . $parent::$slug]) || empty($_POST['settings']['license_' . $parent::$slug]) ) return;



		// get current license
		$license = $this->get_license();

		// don't send if the same
		if ( $license == $_POST['settings']['license_' . $parent::$slug] ) return;



		try
		{
			wp_remote_get(sprintf(
				'https://kanbanwp.com/?action=license-check&license=%s&url=%s&addon=%s',
				$_POST['settings']['license_' . $parent::$slug],
				site_url(),
				$parent::$slug
			));
		}
		catch (Exception $e) {}
	}




}


