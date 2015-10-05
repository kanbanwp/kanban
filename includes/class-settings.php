<?php
/**
 * @link http://tareq.wedevs.com/2012/06/wordpress-settings-api-php-class/
 */



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Settings::init();



class Kanban_Settings
{
	static $instance;



	static function init()
	{
		self::$instance = self::get_instance();
		self::$instance->options_key = sprintf('%s_options', Kanban::$instance->settings->basename);

		add_action( 'admin_init', array(__CLASS__, 'admin_init') );
		add_action( 'admin_menu', array(__CLASS__, 'admin_menu') );
		add_action('parent_file', array(__CLASS__, 'parent_file'), 0);
	}



	static function plugin_page()
	{
		?>
		<link rel="stylesheet" href="<?php echo Kanban::$instance->settings->uri ?>/css/admin-settings.css">
		<div class="wrap">
			<h1>
				<?php echo sprintf('%s Settings', Kanban::$instance->settings->pretty_name) ?>
			</h1>

			<?php /*
			// @todo customize own settings pages
			// @link http://premium.wpmudev.org/blog/tabbed-interface/
						<h2 class="nav-tab-wrapper" id="wpseo-tabs">
							<a class="nav-tab nav-tab-active" id="general-tab" href="#top#general">General</a>
							<a class="nav-tab" id="knowledge-graph-tab" href="#top#knowledge-graph">Company Info</a>
							<a class="nav-tab" id="webmaster-tools-tab" href="#top#webmaster-tools">Webmaster Tools</a>
							<a class="nav-tab" id="security-tab" href="#top#security">Security</a>
						</h2>
			*/ ?>

		<?php
		settings_errors();

	    self::$instance->settings_api->show_navigation();
	    self::$instance->settings_api->show_forms();
?>
	    </div>
			<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
			<script src="<?php echo Kanban::$instance->settings->uri ?>/js/admin-settings.js"></script>
		<?php
	}




	static function welcome_page()
	{
		?>
		<div class="wrap">
			<h1>
				<?php echo __('About', Kanban::$instance->settings->file) ?> <?php echo Kanban::$instance->settings->pretty_name ?>
			</h1>

			<?php if ( isset($_GET['activation']) ) : ?>
				<div class="updated notice is-dismissible below-h2">
					<p><?php echo __('Thanks for using Kanban for WordPress!', Kanban::$instance->settings->file) ?></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text"><?php echo __('Dismiss this notice.', Kanban::$instance->settings->file) ?></span>
					</button>
				</div>
			<?php endif ?>

			<p>
				<a href="<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ) ?>" class="button" target="_blank">
					<?php echo __('Go to your board', Kanban::$instance->settings->file) ?>
				</a>
			</p>

			<p>
				<a href="http://kanbanwp.com/documentation" target="_blank">
					<?php echo __('Documentation', Kanban::$instance->settings->file) ?>
				</a>
			</p>
		</div>
		<?php
	}



	static function admin_menu()
	{
		// Base 64 encoded SVG image.
		$icon_svg = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAPCAYAAAAGRPQsAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkRFMDQwQTg1NUFFMTFFNUJBRDdBMjA0MjA4NTJFNzEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkRFMDQwQTk1NUFFMTFFNUJBRDdBMjA0MjA4NTJFNzEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCREUwNDBBNjU1QUUxMUU1QkFEN0EyMDQyMDg1MkU3MSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCREUwNDBBNzU1QUUxMUU1QkFEN0EyMDQyMDg1MkU3MSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PokTEeYAAABuSURBVHjaYvz//z8DCMw8fBXCwAHSbbUZgRReNUwMVASD1zAWGGPLuTvWDIwMf2GBwsiACKJ//xn/AcOMYfq+C5r/GRj//QPJM8JU/AfTf/4x/GccjYBBEpudm48zEogABqWyOXjVjMYm6QAgwADj+y/EHS5dLQAAAABJRU5ErkJggg==';

		add_menu_page(
			Kanban::$instance->settings->pretty_name,
			Kanban::$instance->settings->pretty_name,
			'manage_options',
			Kanban::$instance->settings->basename,
			null,
			$icon_svg
		);



		// redeclare same page to change name to settings
		// @link https://codex.wordpress.org/Function_Reference/add_submenu_page#Inside_menu_created_with_add_menu_page.28.29
		add_submenu_page(
			Kanban::$instance->settings->basename,
			'Welcome',
			'Welcome',
			'manage_options',
			Kanban::$instance->settings->basename,
			array(__CLASS__, 'welcome_page')
		);


		// don't show settings page if user doesn't have permission to manage plugins
		if ( current_user_can('manage_options') )
		{

			foreach ( Kanban_Post_Types::$post_types as $post_type_slug => $post_type_data )
			{

				$post_type_label = ucfirst($post_type_slug);

				if ( defined('KANBAN_DEBUG') && KANBAN_DEBUG === TRUE )
				{
					add_submenu_page(
						Kanban::$instance->settings->basename,
						sprintf('All %s', str_replace('_', ' ', Kanban_Utils::make_word_plural($post_type_label))),
						sprintf('All %s', str_replace('_', ' ', Kanban_Utils::make_word_plural($post_type_label))),
						'manage_options',
						sprintf(
							'edit.php?post_type=%s',
							Kanban_Post_Types::format_post_type ($post_type_slug)
						)
					);
				} // DEBUG

				foreach ($post_type_data['taxonomies'] as $taxonomy_slug => $values)
				{
					$taxonomy_key = Kanban_Utils::format_key ($post_type_slug, $taxonomy_slug);
					$taxonomy_label = ucwords(sprintf('%s %s', $post_type_slug, Kanban_Utils::make_word_plural($taxonomy_slug)));

					add_submenu_page(
						Kanban::$instance->settings->basename,
						$taxonomy_label,
						$taxonomy_label,
						'manage_options',
						sprintf(
							'edit-tags.php?taxonomy=%s&post_type=%s',
							$taxonomy_key,
							Kanban_Post_Types::format_post_type ($post_type_slug)
						)
					);
				} // taxonomies
			} // post_types
		} // activate_plugins



		// don't show settings page if user doesn't have permission to manage plugins
		if ( current_user_can('manage_options') )
		{
			// @link https://codex.wordpress.org/Function_Reference/add_submenu_page#Inside_menu_created_with_add_menu_page.28.29
			add_submenu_page(
				Kanban::$instance->settings->basename,
				'Settings',
				'Settings',
				'manage_options',
				sprintf(
					'admin.php?page=%s',
					Kanban::$instance->settings->basename
				),
				array(__CLASS__, 'plugin_page')
			);
		}

	} // admin_menu



	public static function get_submenu_file( $post_type_slug, $taxonomy_slug )
	{
		return sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s', Kanban_Utils::format_key( $post_type_slug, $taxonomy_slug ), Kanban_Post_Types::format_post_type($post_type_slug ) );
	}



	public static function parent_file( $parent_file )
	{
		global $submenu_file;

		$current_screen = get_current_screen();

        // Set the submenu as active/current while anywhere in your Custom Post Type (nwcm_news)
        if ( Kanban_Post_Types::format_post_type ('task') === $current_screen->post_type ) {

            if ( Kanban_Utils::format_key('task', 'status') === $current_screen->taxonomy ) {
                $submenu_file = self::get_submenu_file( 'task', 'status' );
            }
            if ( Kanban_Utils::format_key('task', 'estimate') === $current_screen->taxonomy ) {
				$submenu_file = self::get_submenu_file( 'task', 'estimate' );
            }

            $parent_file = Kanban::$instance->settings->basename;

        }

        return $parent_file;
	}



	static function admin_init ()
	{
		$users_field_name = sprintf('%s_user', Kanban::$instance->settings->basename);

		$status_tax_key = Kanban_Utils::format_key ('task', 'status');
		$status_color_field_name = sprintf('%s_colors', $status_tax_key);
		$status_order_field_name = sprintf('%s_order', $status_tax_key);


		$estimate_tax_key = Kanban_Utils::format_key ('task', 'estimate');
		$estimate_order_field_name = sprintf('%s_order', $estimate_tax_key);



		$sections = array(
	        array(
	            'id' => $users_field_name,
	            'title' => 'Users'
	        ),
	        array(
	            'id' => $status_order_field_name,
	            'title' => 'Status Order'
	        ),
	        array(
	            'id' => $status_color_field_name,
	            'title' => 'Status Colors'
	        ),
	        array(
	            'id' => $estimate_order_field_name,
	            'title' => 'Estimate Order'
	        ),
	    );



	    $fields = array();



		$fields[$users_field_name] = array();

		$all_users = get_users();
		$all_users_arr = array();
		foreach ($all_users as $user)
		{
			$all_users_arr[$user->ID] = Kanban_User::format_user_name($user);
		}

	    $fields[$users_field_name][] = array(
            'name' => 'allowed_users',
            'label' => 'Allowed Users',
            'desc' => 'Users who have access to the board',
            'type' => 'multicheck',
            'options' => $all_users_arr
	    );



		$statuses_in_order = Kanban_Terms::terms_in_order ('task', 'status');



		$fields[$status_color_field_name] = array();

		foreach ($statuses_in_order as $status)
		{
			$fields[$status_color_field_name][] = array(
                'name' => $status->term_id,
                'label' => $status->name,
                'type' => 'color'
            );
	    }



		$fields[$status_order_field_name] = array();

		foreach ($statuses_in_order as $status)
		{
			$fields[$status_order_field_name][$status->term_id] = array(
                'name' => $status->term_id,
                'label' => $status->name,
                'type' => 'number'
            );
	    }




		$estimates_in_order = Kanban_Terms::terms_in_order ('task', 'estimate');




		$fields[$estimate_order_field_name] = array();

		foreach ($estimates_in_order as $status)
		{
			$fields[$estimate_order_field_name][$status->term_id] = array(
                'name' => $status->term_id,
                'label' => $status->name,
                'type' => 'number'
            );
	    }



	    self::$instance->settings_api = new WeDevs_Settings_API();

	    //set sections and fields
	    self::$instance->settings_api->set_sections( $sections );
	    self::$instance->settings_api->set_fields( $fields );

	    //initialize them
	    self::$instance->settings_api->admin_init();
	}




	static function get_option ( $section, $option = null, $default = '' )
	{
	    $options = get_option( $section );

	    if ( empty($options) )
	    {
	    	return $default;
	    }

	    if ( isset( $options[$option] ) )
	    {
	        return $options[$option];
	    }

	    return $options;
	}



	// static function get_option($option)
	// {
	// 	self::$instance->options = !self::$instance->options ? get_option(Kanban_Settings::$instance->options_key) : self::$instance->options;

	// 	return self::$instance->options[$option];
	// }




	// static function update_option($option_name, $new_value)
	// {
	// 	$options = get_option(Kanban_Settings::$instance->options_key);

	// 	if( !is_array($options) )
	// 	{
	// 		$options = array();
	// 	}

	// 	$options[$option_name] = $new_value;

	// 	update_option(Kanban_Settings::$instance->options_key, $options);
	// }



	// static function post_save_users ()
	// {
	// 	if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'save_users') || !is_user_logged_in() ) return;

	// 	self::update_option('allowed_users', $_POST['allowed_users']);

	// 	Kanban::$instance->flash->add('success', 'The users have been updated');

	// 	wp_redirect($_POST['_wp_http_referer']);
	// 	exit;
	// }



	// static function post_save_status ()
	// {
	// 	if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'save_status_order') || !is_user_logged_in() ) return;

	// 	$tax_key = Kanban_Utils::format_key ('task', 'status');

	// 	// save names
	// 	$name_field = sprintf('%s_name', $tax_key);

	// 	foreach ($_POST[$name_field] as $term_id => $name)
	// 	{
	// 		wp_update_term(
	// 			$term_id,
	// 			$tax_key,
	// 			array(
	// 			  'name' => $name
	// 			)
	// 		);
	// 	}

	// 	// save order
	// 	$order_name = sprintf('%s_order', $tax_key);
	// 	self::update_option($order_name, $_POST[$order_name]);

	// 	// save colors
	// 	// https://pippinsplugins.com/adding-custom-meta-fields-to-taxonomies/
	// 	$color_name = sprintf('%s_colors', $tax_key);
	// 	self::update_option($color_name, $_POST[$color_name]);

	// 	Kanban::$instance->flash->add('success', 'Status order has been saved');

	// 	wp_redirect($_POST['_wp_http_referer']);
	// 	exit;
	// }



	// static function post_save_estimate_order ()
	// {
	// 	if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'save_estimate_order') || !is_user_logged_in() ) return;

	// 	$tax_key = Kanban_Utils::format_key ('task', 'estimate');
	// 	$field_name = sprintf('%s_order', $tax_key);

	// 	self::update_option($field_name, $_POST[$field_name]);


	// 	Kanban::$instance->flash->add('success', 'Estimates order has been saved');

	// 	wp_redirect($_POST['_wp_http_referer']);
	// 	exit;
	// }



	static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}



	private function __construct() { }

} // Kanban_Settings



