<?php



// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}



class Kanban_Addon_Updates
{


	private $parent;



	/**
	 * The plugin current version
	 * @var string
	 */
//	public $current_version;

	/**
	 * The plugin remote update path
	 * @var string
	 */
	public $update_path = 'https://kanbanwp.com/plugin-updates/update.php';

	/**
	 * Plugin Slug (plugin_directory/plugin_file.php)
	 * @var string
	 */
	public $plugin_slug;

	/**
	 * Plugin name (plugin_file)
	 * @var string
	 */
//	public $slug;



	public function __construct( $parent ) {

		// Store the static reference to the calling addon class.
		$this->parent = $parent;

		// define the alternative API for updating checking
		add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'check_update' ) );

		// Define the alternative response for information checking
		add_filter( 'plugins_api', array( &$this, 'check_info' ), 10, 3 );

		add_filter( 'http_request_host_is_external', array( &$this, 'allow_my_custom_host' ), 10, 3 );

	}



	/** Enable my local themes and plugins repository FFS!!! */

	function allow_my_custom_host( $allow, $host, $url ) {

		$parse = parse_url($this->update_path);

		if ( $host == $parse['host'] ) {
			$allow = true;
		}
		return $allow;
	}



	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 * @return object $ transient
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Get the remote version
		$remote_version = $this->get_remote_version();



		$parent = $this->parent;
		$current_version = $parent::$plugin_data[ 'Version' ];



		// If a newer version is available, add the update
		if ( version_compare( $current_version, $remote_version, '<' ) ) {

			$plugin_data = $this->get_remote_info();
//			echo '<pre>';
//			print_r($plugin_data);
//			echo "</pre><br>\n";
//			exit;
			$obj = new stdClass();
			$obj->id = 0;
			$obj->slug = $parent::$slug;
			$obj->plugin = $parent::$plugin_basename;
			$obj->new_version = $remote_version;
			$obj->url = $plugin_data->homepage;

			$obj->package = '';
			$obj->upgrade_notice = '';

			if ( isset($plugin_data->download_link) && filter_var($plugin_data->download_link, FILTER_VALIDATE_URL) !== FALSE) {
				$obj->package = $plugin_data->download_link;
			}

			if ( isset($plugin_data->upgrade_notice) ) {
				$obj->upgrade_notice = $plugin_data->upgrade_notice;
			}

			$obj->tested = $plugin_data->tested;

			$transient->response[ $parent::$plugin_basename ] = $obj;
		}
//		echo '<pre>';
//		print_r( $transient);
//		echo "</pre><br>\n";
//		exit;
		return $transient;
	}



	/**
	 * Add our self-hosted description to the filter
	 *
	 * @param boolean $false
	 * @param array $action
	 * @param object $arg
	 * @return bool|object
	 */
	public function check_info( $false, $action, $arg ) {

		$parent = $this->parent;

		if ( isset($arg->slug) && $arg->slug === $parent::$slug ) {
			$information = $this->get_remote_info();
			return $information;
		}
		return false;
	}



	/**
	 * Return the remote version
	 * @return string $remote_version
	 */
	public function get_remote_version() {

		$update_path = $this->build_update_path( 'version' );

		if ( !$update_path ) return FALSE;



		$request = wp_remote_post(
			$update_path,
			array(
				'body' => array(
					'action' => 'version'
				)
			)
		);

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) return FALSE;



		$json = $request[ 'body' ];

		$data = json_decode( $json );

		return $data->new_version;
	}



	/**
	 * Get information about the remote version
	 * @return bool|object
	 */
	public function get_remote_info() {

		$update_path = $this->build_update_path( 'info' );

		if ( !$update_path ) return FALSE;



		$request = wp_remote_post(
			$update_path,
			array(
				'body' => array(
					'action' => 'info'
				)
			)
		);



		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) return FALSE;



		$json = $request[ 'body' ];
		$data = json_decode( $json );

		$data->banners = (array)$data->banners;
		$data->sections = (array)$data->sections;

		return $data;
	}



	private function build_update_path( $action = NULL ) {

		$parent = $this->parent;

		$license = $parent::$license;

		$license_str = $license->get_license();



		// Add slug and to update path.
		$update_path = add_query_arg(
			array(
				'slug' => $parent::$slug,
				'license' => $license_str,
				'action' => $action
			),
			$this->update_path
		);

		return $update_path;
	}
}