<?php


class Kanban_User_Admin extends Kanban_User_Board {

	public function ajax_add( $data ) {

		$user = get_user_by( 'id', $data['user_id'] );

		if ( empty( $user ) ) {
			header( 'HTTP/1.1 401 user not found' );

			return false;
		}

		$this->set_row( array(
			'board_id'     => 0,
			'is_admin'     => true,
			'user_id'      => $data['user_id'],
			'capabilities' => isset($data['capabilities']) && is_array($data['capabilities']) ? $data['capabilities'] : array('admin-board-create') // Default capabilities.
		) );

		$user = $this->get_user_by_id( $data['user_id'] );

		return $user;
	} // add_to_board




	public function ajax_replace( $data ) {

		$board = $this->get_user_board( $data['user_id'] );

		if ( !isset($board->id) ) {
			header( 'HTTP/1.1 401 user not found' );
			return false;
		}

		$data['id'] = $board->id;

		$row = $this->set_row( $data );

		return $row;
	}


	/**
	 * @param $user_id
	 * @param int $board_id Left in just for consistency
	 * @param null $since_dt
	 *
	 * @return object
	 */
	public function get_user_board ( $user_id, $board_id = 0, $since_dt = null ) {
		global $wpdb;

		$user_id = intval( $user_id );

		// Not used, but replicates User Board
		$board_id = intval( $board_id );

		$table = Kanban_Db::instance()->prefix() . $this->get_table();

		$since = '';
		if ( DateTime::createFromFormat( 'Y-m-d H:i:s', $since_dt ) !== false ) {

			$since = "
			AND $table.`modified_dt_gmt` > '$since_dt' 
			";

//			if ( is_user_logged_in() ) {
//				$since .= sprintf(
//					" AND $table.`modified_user_id` != %d ",
//					get_current_user_id()
//				);
//			}
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $table.user_id,
					$table.`board_id`,
					$table.capabilities,
					$table.options
 
					FROM $table
					WHERE 1=1
					AND $table.user_id = %d
					AND $table.is_admin = 1
					AND $table.is_active = 1
					$since
				",
				$user_id,
				$board_id
			),
			OBJECT
		);

		if ( empty( $row ) ) {
			return (object) array();
		}

		$row = $this->format_board_for_user( $row );

		return $row;

	}




}