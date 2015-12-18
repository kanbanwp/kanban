<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Option::init();



class Kanban_Option extends Kanban_Db
{
	private static $instance;
	protected static $table_name = 'options';
	protected static $table_columns = array(
		'name' => 'text',
		'value' => 'text'
	);

	protected static $defaults = array (
		'hour_interval' => '1',
		'allowed_users' => ''
	);

	protected static $options = array();



	static function init()
	{
		add_action('init', array(__CLASS__, 'save_settings'));

		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueue_js') );

	}


	static function replace ($data)
	{
		return self::_replace($data);
	}



	static function db_table ()
	{
		return "CREATE TABLE " . self::table_name() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					name varchar(64) NOT NULL,
					value longtext NOT NULL,
					UNIQUE KEY  (id)
				)";
	} // db_table



	static function get_defaults ()
	{
		// make sure there's always at least one user
		self::$defaults['allowed_users'] = serialize(array(get_current_user_id()));

		return self::$defaults;
	}



	static function get_all_raw ()
	{
		$table_name = self::table_name();

		$sql = "SELECT *
				FROM `{$table_name}`
		;";

		$sql = apply_filters(
			sprintf(
				'%s_sql_%s_get_all',
				Kanban::get_instance()->settings->basename,
				self::$table_name
			),
			$sql
		);

		$records = parent::get_all($sql);

		// unserialize arrays
		foreach ($records as $key => $record)
		{
			if ( !is_serialized($record->value) ) continue;

			$records[$key]->value = unserialize($record->value);
		}

		return $records;
	}



	static function get_all ($sql = NULL)
	{
		if ( empty(self::$options) )
		{
			$records = self::get_all_raw();

			$output = array();
			foreach ($records as $record)
			{
				if ( is_serialized($record->value) )
				{
					$record->value = unserialize($record->value);
				}

				$output[$record->name] = $record->value;
			}

			self::$options = array_merge(self::get_defaults(), $output);
		}

		return self::$options;
	}



	static function get_option($name)
	{
		$options = self::get_all();

		return $options[$name];
	}



	static function enqueue_js($hook)
	{
		if ( !is_admin() || (isset($_GET['page']) && $_GET['page'] != sprintf('%s_settings', Kanban::get_instance()->settings->basename)) ) return;

	    wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
	    	'jquery-ui',
	    	'//code.jquery.com/ui/1.11.4/jquery-ui.js',
	    	array()
	    );

		wp_enqueue_script(
	    	't',
	    	sprintf('%s/js/t.min.js', Kanban::get_instance()->settings->uri),
	    	array()
	    );


	    wp_enqueue_script(
	    	sprintf('%s_settings', Kanban::get_instance()->settings->basename),
	    	sprintf('%s/js/admin-settings.js', Kanban::get_instance()->settings->uri),
	    	array( 'wp-color-picker' ),
	    	false,
	    	true
	    );
	}



	static function settings_page()
	{
		$settings = Kanban_Option::get_all();

		$all_users = get_users();
		$all_users_arr = array();
		foreach ($all_users as $user)
		{
			$all_users_arr[$user->ID] = Kanban_User::format_user_name($user);
		}

		$statuses = Kanban_Status::get_all();
		$statuses = Kanban_Utils::order_array_of_objects_by_property ($statuses, 'position');

		$estimates = Kanban_Estimate::get_all();
		$estimates = Kanban_Utils::order_array_of_objects_by_property ($estimates, 'position');

		$template = Kanban_Template::find_template('admin/settings');

		include_once $template;
	}



	static function save_settings ()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], sprintf('%s-%s', Kanban::get_instance()->settings->basename, Kanban_Option::table_name())) || !is_user_logged_in() ) return;


		$statuses = Kanban_Status::get_all();
		$status_ids = array_keys($statuses);



		// any statuses to delete?
		if ( isset($_POST['statuses']['saved']) )
		{
			$deleted_statuses = array_diff_key($status_ids, array_keys($_POST['statuses']['saved']));

			if ( !empty($deleted_statuses) )
			{
				foreach ($deleted_statuses as $key => $id)
				{
					Kanban_Status::delete(array('id' => $id));
				}
			}
		}



		// add new statuses first
		if ( isset($_POST['statuses']['new']) )
		{
			foreach ($_POST['statuses']['new'] as $status)
			{
				// save it
				$success = Kanban_Status::replace($status);

				if ( $success )
				{
					Kanban_Status::insert_id();

					// add it to all the statuses to save
					$_POST['statuses']['saved'][$status_id] = $status;
				}
			}
		}



		// now save all statuses with positions
		if ( isset($_POST['statuses']['saved']) )
		{
			foreach ($_POST['statuses']['saved'] as $status_id => $status)
			{
				$status['id'] = $status_id;

				Kanban_Status::replace($status);
			}
		}



		$estimates = Kanban_Estimate::get_all();
		$estimate_ids = array_keys($estimates);



		// any estimates to delete?
		if ( isset($_POST['estimates']['saved']) )
		{
			$deleted_estimates = array_diff($estimate_ids, array_keys($_POST['estimates']['saved']));

			if ( !empty($deleted_estimates) )
			{
				foreach ($deleted_estimates as $key => $id)
				{
					Kanban_Estimate::delete(array('id' => $id));
				}
			}
		}



		// add new estimates first
		if ( isset($_POST['estimates']['new']) )
		{
			foreach ($_POST['estimates']['new'] as $estimate)
			{
				// save it
				$success = Kanban_Estimate::replace($estimate);

				if ( $success )
				{
					$estimate_id = Kanban_Estimate::insert_id();

					// add it to all the estimates to save
					$_POST['estimates']['saved'][$estimate_id] = $estimate;
				}
			}
		}



		// now save all estimates with positions
		if ( isset($_POST['estimates']['saved']) )
		{
			foreach ($_POST['estimates']['saved'] as $estimate_id => $estimate)
			{
				$estimate['id'] = $estimate_id;

				Kanban_Estimate::replace($estimate);
			}
		}



		// get current settings
		$settings = Kanban_Option::get_all_raw();
		$settings = Kanban_Utils::build_array_with_id_keys($settings);



		// save all single settings
		foreach ($_POST['settings'] as $key => $value)
		{
			if ( is_array($value) )
			{
				$value = serialize($value);
			}

			$data = array(
				'name' => $key,
				'value' => $value
			);

			// see if it's already set
			$id = Kanban_Utils::find_key_of_object_by_property ('name', $key, $settings);

			if ( $id )
			{
				$data['id'] = $id;
			}

			Kanban_Option::_replace($data);
		}



		$url = add_query_arg(
			array(
				'message' => urlencode(__('Settings saved', Kanban::get_text_domain() ))
			),
			$_POST['_wp_http_referer']
		);

		wp_redirect($url);
		exit;
	}



	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

}


