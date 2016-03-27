<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Estimate::init();



class Kanban_Estimate extends Kanban_Db
{
	// the instance of this object
	private static $instance;

	// the table name of this class
	protected static $table_name = 'estimates';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'title'    => 'text',
		'hours'    => 'float',
		'board_id' => 'int',
		'position' => 'int'
	);



	static function init()
	{
		add_action( 'init', array( __CLASS__, 'save_settings' ), 1 );
	}



	static function get_all( $sql = NULL )
	{
		$table_name = self::table_name();

		$sql = "SELECT *
				FROM `{$table_name}`
				ORDER BY `position` ASC
		;";

		$sql = apply_filters( 'kanban_estimate_get_all_sql', $sql );

		$records = parent::get_all( $sql );

		return apply_filters(
			'kanban_estimate_get_all_return',
			Kanban_Utils::build_array_with_id_keys ( $records, 'id' )
		);
	}



	static function save_settings()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-options' ) || ! is_user_logged_in() ) return;

		if ( !isset($_POST['estimates']) ) return;



		do_action( 'kanban_estimate_save_settings_before', $_POST );



		$current_board = Kanban_Board::get_current(
			isset($_POST['board_id']) ? $_POST['board_id'] : NULL
		);



		$estimates = Kanban_Estimate::get_all();
		$estimate_ids = array_keys( $estimates );



		// any estimates to delete?
		if ( isset( $_POST['estimates']['saved'] ) )
		{
			$deleted_estimates = array_diff( $estimate_ids, array_keys( $_POST['estimates']['saved'] ) );

			if ( ! empty( $deleted_estimates ) )
			{
				foreach ( $deleted_estimates as $key => $id )
				{
					Kanban_Estimate::delete( array( 'id' => $id ) );
				}
			}
		}



		// add new estimates first
		if ( isset( $_POST['estimates']['new'] ) )
		{
			foreach ( $_POST['estimates']['new'] as $estimate )
			{
				$estimate['board_id'] = $current_board->id;

				// save it
				$success = Kanban_Estimate::replace( $estimate );

				if ( $success )
				{
					$estimate_id = Kanban_Estimate::insert_id();

					// add it to all the estimates to save
					$_POST['estimates']['saved'][$estimate_id] = $estimate;
				}
			}
		}



		// now save all estimates with positions
		if ( isset( $_POST['estimates']['saved'] ) )
		{
			foreach ( $_POST['estimates']['saved'] as $estimate_id => $estimate )
			{
				$estimate['id'] = $estimate_id;
				$estimate['board_id'] = $current_board->id;

				Kanban_Estimate::replace( $estimate );
			}
		}
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
					id bigint(20) NOT NULL AUTO_INCREMENT,
					title varchar(64) NOT NULL,
					hours decimal(6, 4) NOT NULL,
					position bigint(20) NOT NULL,
					board_id bigint(20) NOT NULL DEFAULT 1,
					UNIQUE KEY  (id),
					KEY board_id (board_id)
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
	private function __construct() { }

}
