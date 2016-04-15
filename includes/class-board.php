<?php

/**
 * the class for rendering our kanban board
 */



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// instantiate the plugin
Kanban_Board::init();



class Kanban_Board extends Kanban_Db
{
	// the instance of this object
	private static $instance;

	// the common name for this class
	static $slug = 'board';

	// the table name of this class
	protected static $table_name = 'boards';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'title'           => 'text',
		'description'     => 'text',
		'created_dt_gmt'  => 'datetime',
		'modified_dt_gmt' => 'datetime',
		'user_id_author'  => 'int',
		'is_active'       => 'bool',
		'position' => 'int'
	);

	static $boards = array();



	// add actions and filters
	static function init()
	{
		// send board data to the board template
		add_filter( 'template_include', array( __CLASS__, 'send_page_data_to_template' ), 100 ); // must be higher than template

		add_action( 'kanban_board_render_js_templates', array( __CLASS__, 'render_js_templates' ) );
	}



	/**
	 * load data needed for board's javascript
	 * @param  string $template the passed in template path
	 * @return string           the same template path
	 */
	static function send_page_data_to_template( $template )
	{
		// make sure we're looking at the board
		if ( ! isset( Kanban_Template::get_instance()->slug ) || Kanban_Template::get_instance()->slug != self::$slug ) return $template;



		// make sure they don't need to upgrade
		if ( Kanban::get_instance()->settings->records_to_move > 0 )
		{
			?>
			<p>
			<?php echo sprintf( __( 'We\'ve found %s kanban records that need to be migrated for the latest version of Kanban for WordPress! ', 'kanban' ), Kanban::get_instance()->settings->records_to_move ); ?>
			</p>
			<p>
			<?php echo sprintf(
					__(
						'Please visit the <a href="%s">Kanban welcome page</a> to migrate your data.',
						'kanban'
					),
					add_query_arg(
						'page',
						'kanban_welcome',
						admin_url( 'admin.php' )
					)
				);
			?>
			<?php
			exit;
		}



		// get the template data
		global $wp_query;

		// attach our object to the template data
		$wp_query->query_vars['kanban'] = (object) array();
		$wp_query->query_vars['kanban']->board = (object) array();

		// add default filters
		$wp_query->query_vars['kanban']->board->filters = array(
			'user_id_assigned' => (object) array(),
			'project_id'       => (object) array()
		);

		// add passed alert
		$wp_query->query_vars['kanban']->board->alert = ! empty( $_GET['alert'] ) ? stripcslashes( $_GET['alert'] ) : '';

		// get all data for the javascript
		$wp_query->query_vars['kanban']->board->settings = Kanban_Option::get_all();

		$wp_query->query_vars['kanban']->board->text = apply_filters(
			'kanban_board_text',
			include(__DIR__ . '/inc-board-text.php')
		);

		$wp_query->query_vars['kanban']->board->allowed_users = Kanban_User::get_allowed_users();

		$wp_query->query_vars['kanban']->board->estimates = Kanban_Estimate::get_all();
		$wp_query->query_vars['kanban']->board->statuses = Kanban_Status::get_all();

		$wp_query->query_vars['kanban']->board->projects = Kanban_Project::get_all();
		$wp_query->query_vars['kanban']->board->tasks = Kanban_Task::get_all();

		// get the current user from the allowed users
		$wp_query->query_vars['kanban']->board->current_user = Kanban_User::get_current_user();

		// figure out percentages here (easier, quicker than in js)
		$wp_query->query_vars['kanban']->board->col_percent_w = count( $wp_query->query_vars['kanban']->board->statuses ) > 0 ? 100/(count( $wp_query->query_vars['kanban']->board->statuses )) : 100;
		$wp_query->query_vars['kanban']->board->sidebar_w = count( $wp_query->query_vars['kanban']->board->statuses ) > 0 ? 100/(count( $wp_query->query_vars['kanban']->board->statuses )-2) : 0;


		apply_filters(
			'kanban_board_query_vars',
			$wp_query->query_vars['kanban']->board
		);

		return $template;
	}



	static function render_js_templates()
	{
		// Automatically load router files
		$js_templates = glob( Kanban::$instance->settings->path . '/templates/inc/t-*.php' );

		$js_templates = apply_filters(
			'kanban_board_render_js_templates_before',
			$js_templates
		);

		foreach ( $js_templates as $js_template ) : ?>
		<script type="text/html" id="<?php echo basename($js_template, '.php'); ?>">

		<?php include $js_template; ?>

		</script>
		<?php endforeach;
	}



	static function get_all( $sql = NULL )
	{
		if ( empty(self::$boards) )
		{
			$table_name = self::table_name();

			$sql = "SELECT *
					FROM `{$table_name}`
					WHERE `{$table_name}`.`is_active` = 1
			;";

			$sql = apply_filters( 'kanban_board_get_all_sql', $sql );

			$records = parent::get_all( $sql );

			self::$boards = Kanban_Utils::build_array_with_id_keys($records);
		}

		return apply_filters(
			'kanban_board_get_all_return',
			self::$boards
		);
	}



	static function get_current ($board_id = NULL)
	{
		// if one isn't passed, but is set elsewhere
		if ( is_null($board_id) && isset($_REQUEST['board_id']) )
		{
			$board_id = $_REQUEST['board_id'];
		}

		$boards = self::get_all();

		// if the one we want exists
		if ( !is_null($board_id) && isset($boards[$board_id]) )
		{
			return $boards[$board_id];
		}

		// otherwise, pass the first one
		return reset($boards);
	}



	// extend parent, so it's accessible from other classes
	static function replace( $data )
	{
		return self::_replace( $data );
	}



	// extend parent, so it's accessible from other classes
	static function delete( $where )
	{
		return self::_delete( $where );
	}



	// extend parent, so it's accessible from other classes
	static function insert_id()
	{
		return self::_insert_id();
	}



	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					title text NOT NULL,
					description text NOT NULL,
					created_dt_gmt datetime NOT NULL,
					modified_dt_gmt datetime NOT NULL,
					user_id_author bigint(20) NOT NULL,
					position bigint(20) NOT NULL,
					is_active BOOLEAN NOT NULL DEFAULT TRUE,
					UNIQUE KEY id (id),
					KEY is_active (is_active)
				)';
	} // db_table



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
	private function __construct() {}



} // Kanban_Board
