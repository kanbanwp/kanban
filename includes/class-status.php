<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_Status::init();



class Kanban_Status extends Kanban_Db
{
	// the instance of this object
	private static $instance;

	// the table name of this class
	protected static $table_name = 'statuses';

	// define db table columns and their validation type
	protected static $table_columns = array(
		'title'     => 'text',
		'color_hex' => 'text',
		'board_id'  => 'int',
		'position'  => 'int'
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

		$sql = apply_filters( 'kanban_status_get_all_sql', $sql );

		$records = parent::get_all( $sql );

		foreach ( $records as $key => $record )
		{
			$records[$key]->title = Kanban_Utils::str_for_frontend( $records[$key]->title );
		}

		return apply_filters(
			'kanban_status_get_all_return',
			Kanban_Utils::build_array_with_id_keys ( $records, 'id' )
		);
	}





	static function save_settings()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'kanban-options' ) || ! is_user_logged_in() ) return;

		if ( !isset($_POST['statuses']) ) return;



		do_action( 'kanban_status_save_settings_before', $_POST );



		$current_board = Kanban_Board::get_current(
			isset($_POST['board_id']) ? $_POST['board_id'] : NULL
		);



		$statuses = Kanban_Status::get_all();
		$status_ids = array_keys( $statuses );



		// any statuses to delete?
		if ( isset( $_POST['statuses']['saved'] ) )
		{
			$deleted_statuses = array_diff( $status_ids, array_keys( $_POST['statuses']['saved'] ) );

			if ( ! empty( $deleted_statuses ) )
			{
				foreach ( $deleted_statuses as $key => $id )
				{
					Kanban_Status::delete( array( 'id' => $id ) );
				}
			}
		}



		// add new statuses first
		if ( isset( $_POST['statuses']['new'] ) )
		{
			foreach ( $_POST['statuses']['new'] as $status )
			{
				$status['board_id'] = $current_board->id;
				// save it
				$success = Kanban_Status::replace( $status );

				if ( $success )
				{
					$status_id = Kanban_Status::insert_id();

					// add it to all the statuses to save
					$_POST['statuses']['saved'][$status_id] = $status;
				}
			}
		}



		// now save all statuses with positions
		if ( isset( $_POST['statuses']['saved'] ) )
		{
			foreach ( $_POST['statuses']['saved'] as $status_id => $status )
			{
				$status['id'] = $status_id;
				$status['board_id'] = $current_board->id;

				Kanban_Status::replace( $status );
			}
		}
	}



	// define the db schema
	static function db_table()
	{
		return 'CREATE TABLE ' . self::table_name() . ' (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					title varchar(64) NOT NULL,
					color_hex varchar(7) NOT NULL,
					position bigint(20) NOT NULL,
					board_id bigint(20) NOT NULL DEFAULT 1,
					UNIQUE KEY  (id),
					KEY board_id (board_id)
				)';
	} // db_table



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
