<?php

/**
 * for all interactions w the WordPress admin
 */



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// instantiate the class
Kanban_Admin::init();



class Kanban_Admin extends Kanban_Db
{
	private static $instance;



	static function init ()
	{
		// redirect to welcome screen on activation
		add_action( 'admin_init',array(__CLASS__, 'welcome_screen_do_activation_redirect') );

		// Remove Admin bar
		if ( strpos($_SERVER['REQUEST_URI'], sprintf('/%s/', Kanban::$slug)) !== FALSE )
		{
			add_filter('show_admin_bar', '__return_false');
		}

		// add custom pages to admin
		add_action( 'admin_menu', array(__CLASS__, 'admin_menu') );

		// if migrating from older version, show upgrade notice with progress bar
		add_action( 'admin_notices', array(__CLASS__, 'render_upgrade_notice') );
	}



	/**
	 * show upgrade notice with progress bar
	 */
	static function render_upgrade_notice ()
	{
		// make sure something needs to be upgraded
		if ( Kanban::get_instance()->settings->records_to_move <= 0 ) return;


		?>
		<div class="updated error">
			<p style="font-size: 1.236em;">
				<b><?php echo __('Warning!', Kanban::get_text_domain()) ?></b>
				<?php echo sprintf(__('We\'ve found %s kanban records that need to be migrated for the latest version of Kanban for WordPress!', Kanban::get_text_domain()), Kanban::get_instance()->settings->records_to_move) ?>
			</p>
			<p>
				<?php echo __('Please click "migrate" below and we\'ll move them for you.', Kanban::get_text_domain()) ?>
			</p>
			<p>
				<button type="button" class="button-primary" id="kanban-migrate-start">
					<?php echo __('Migrate', Kanban::get_text_domain()) ?>
				</button>
			</p>

			<div id="kanban-migrate-progress">
				<?php echo __('Migration Progress', Kanban::get_text_domain()) ?>
				<?php echo __('(Please do not browse away from the page until migration is complete)', Kanban::get_text_domain()) ?>:
				<div id="kanban-migrate-progress-outer">
					<div id="kanban-migrate-progress-inner">
						&nbsp;
					</div><!-- inner -->
				</div><!-- outer -->
				<div id="kanban-migrate-message"></div>
			</div><!-- progress -->
		</div>
		<style>
		#kanban-migrate-progress {
			display: none;
			margin-bottom: 20px;
		}
		#kanban-migrate-progress-outer {
			background: #0073aa;
			padding: 3px;
			width: 100%;

		}
		#kanban-migrate-progress-inner {
			background: #00b9eb;
			font-size: 20px;
			overflow: hidden;
			position: relative;
			width: 1%;

			-webkit-transition: width 1s ease-in-out;
			-moz-transition: width 1s ease-in-out;
			-o-transition: width 1s ease-in-out;
			transition: width 1s ease-in-out;
		}
		#kanban-migrate-progress-inner:after {
		  content: "";
		  position: absolute;
		  top: 0; left: 0; bottom: 0; right: 0;
		  background-image: linear-gradient(
		    -45deg,
		    rgba(255, 255, 255, .2) 25%,
		    transparent 25%,
		    transparent 50%,
		    rgba(255, 255, 255, .2) 50%,
		    rgba(255, 255, 255, .2) 75%,
		    transparent 75%,
		    transparent
		  );
		  z-index: 1;
		  background-size: 50px 50px;
		  animation: move 2s linear infinite;
		  border-top-right-radius: 8px;
		  border-bottom-right-radius: 8px;
		  border-top-left-radius: 20px;
		  border-bottom-left-radius: 20px;
		  overflow: hidden;
		}

		@keyframes move {
			0% {
			background-position: 0 0;
			}
			100% {
			background-position: 50px 50px;
			}
		}
		</style>
		<script>
		jQuery(function($)
		{
			// start with how many records need to be migrated
			var records_to_move = <?php echo Kanban::get_instance()->settings->records_to_move ?>;

			// if migration fails, show alert
			function migration_failed ()
			{
				alert('Migration failed. Please refresh the page and try again.');
			}

			// the loop to continually do migration, and update progress bar
			function do_migrate ()
			{
				$.post(
					ajaxurl,
					{
						action: 'kanban_migrate_db'
					}
				)
				.done(function(data)
				{
					try
					{
						var percentage = 100-((100 * parseInt(data.data.posts_remaining))/records_to_move);

						if ( percentage > 100 )
						{
							percentage = 100;
						}

						if ( percentage < 1 )
						{
							percentage = 1;
						}

						$('#kanban-migrate-progress-inner').css('width', percentage + '%');
					}
					catch (err)
					{
						migration_failed();
					}

					// update the returned message
					try
					{
						$('#kanban-migrate-message').text(data.data.message);
					}
					catch (err) {}

					// wait 2 seconds and do it again
					try
					{
						if ( data.data.continue )
						{
							setTimeout(function()
							{
								do_migrate();
							}, 2000);
						}
						else
						{
							if ( data.data.done )
							{
								$('#kanban-migrate-progress').html('<b style="font-size: 1.618em;">Migration has completed! <a href="<?php echo site_url() ?>/kanban/board" class="button button-primary" target="_blank">Go to your board</a>.</b>');
							}
							else
							{
								migration_failed();
							}
						}
					}
					catch (err)
					{
						migration_failed();
					}
				})
				.fail(function(data)
				{
					migration_failed();
					return false;
				});
			}; // do_migrate

			$('#kanban-migrate-start').on(
				'click',
				function()
				{
					$(this).hide();
					$('#kanban-migrate-progress').show();

					do_migrate();
				}
			)
		});
		</script>
		<?php
	} // render_upgrade_notice



	/**
	 * render the welcome page
	 */
	static function welcome_page()
	{
		$template = Kanban_Template::find_template('admin/welcome');

		include_once $template;
	}



	/**
	 * add pages to admin menu, including custom icon
	 * @return [type] [description]
	 */
	static function admin_menu()
	{
		// Base 64 encoded SVG image.
		$icon_svg = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAPCAYAAAAGRPQsAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkRFMDQwQTg1NUFFMTFFNUJBRDdBMjA0MjA4NTJFNzEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkRFMDQwQTk1NUFFMTFFNUJBRDdBMjA0MjA4NTJFNzEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCREUwNDBBNjU1QUUxMUU1QkFEN0EyMDQyMDg1MkU3MSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCREUwNDBBNzU1QUUxMUU1QkFEN0EyMDQyMDg1MkU3MSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PokTEeYAAABuSURBVHjaYvz//z8DCMw8fBXCwAHSbbUZgRReNUwMVASD1zAWGGPLuTvWDIwMf2GBwsiACKJ//xn/AcOMYfq+C5r/GRj//QPJM8JU/AfTf/4x/GccjYBBEpudm48zEogABqWyOXjVjMYm6QAgwADj+y/EHS5dLQAAAABJRU5ErkJggg==';

		// add the base slug and page
		add_menu_page(
			Kanban::get_instance()->settings->pretty_name,
			Kanban::get_instance()->settings->pretty_name,
			'manage_options',
			sprintf('%s_welcome', Kanban::get_instance()->settings->basename),
			null,
			$icon_svg
		);



		// redeclare same page to change name to settings
		// @link https://codex.wordpress.org/Function_Reference/add_submenu_page#Inside_menu_created_with_add_menu_page.28.29
		add_submenu_page(
			sprintf('%s_welcome', Kanban::get_instance()->settings->basename),
			'Welcome',
			'Welcome',
			'manage_options',
			sprintf('%s_welcome', Kanban::get_instance()->settings->basename),
			array(__CLASS__, 'welcome_page')
		);

		// add the settings admin page
		add_submenu_page(
			sprintf('%s_welcome', Kanban::get_instance()->settings->basename),
			'Settings',
			'Settings',
			'manage_options',
			sprintf('%s_settings', Kanban::get_instance()->settings->basename),
			array('Kanban_Option', 'settings_page')
		);

	} // admin_menu



	// add the settings page link on the plugins page
	static function add_plugin_settings_link( $links )
	{
		$url = admin_url(
			sprintf(
				'admin.php?page=%s',
				sprintf(
					'%s_settings',
					Kanban::get_instance()->settings->basename
				)
			)
		);

		$mylinks = array(
			sprintf('<a href="%s">Settings</a>', $url)
		);

		return array_merge( $links, $mylinks );
	}



	// redirect to welcome page
	// @link http://premium.wpmudev.org/blog/tabbed-interface/
	static function welcome_screen_do_activation_redirect()
	{
		// Bail if no activation redirect
		if ( ! get_transient( sprintf('_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename) ) )
		{
			return;
		}

		// Delete the redirect transient
		delete_transient( sprintf('_%s_welcome_screen_activation_redirect', Kanban::get_instance()->settings->basename) );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
		{
			return;
		}

		// Redirect to about page
		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => sprintf('%s_welcome', Kanban::get_instance()->settings->basename),
					'activation' => '1'
				),
				admin_url( 'admin.php' )
			)
		);
	}



	/**
	 * get the instance of this class
	 * @return	object	the instance
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




} // Kanban_Admin



