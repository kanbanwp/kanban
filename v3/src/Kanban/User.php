<?php


class Kanban_User extends Kanban_Abstract {

	protected $current_user;

	public function ajax_get_wp_users() {

		return $this->get_wp_users();

	} // get_wp_users

	public function get_wp_users() {
		$args = array(
			'blog_id' => $GLOBALS['blog_id'],
			'fields'  => 'all',
		);

		$users = get_users( $args );

		$users_by_id = array();
		foreach ( $users as &$user ) {
			$users_by_id[ $user->data->ID ] = $this->format_user_for_app( $user, 'simple' );
		}

		return $users_by_id;

	}

	public function find_mentions_in_string ($content, $return = 'id') {
		preg_match_all( '/data-mention=\"([0-9]*)\"/',
			$content,
			$matches,
			PREG_PATTERN_ORDER
		);

		$user_ids = array();

		if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
			$user_ids = array_filter( array_unique( $matches[1] ) );
		}

		if ( empty($user_ids) ) {
			return $user_ids;
		}

		if ( $return == 'user' ) {
			return $this->get_users($user_ids);
		}

		return $user_ids;
	}

	public function format_user_for_app( $user ) {

		// Remove passwords, just in case.
		unset( $user->user_pass );
		unset( $user->user_activation_key );
		unset( $user->user_registered );
		unset( $user->user_status );
		unset( $user->user_pass );
		unset( $user->user_url );
		unset( $user->user_nicename );
		unset( $user->user_login );

		$user->data->id = $user->ID;

		$user->data->display_name_long = sprintf( '%s (%s)', $user->display_name, $user->user_email );

		$names = preg_split( "/\s+/", $user->display_name );


		$user->data->display_name_short = trim( sprintf(
			'%s %s',
			$names[0], // First word
			count( $names ) > 1 ? substr( end( $names ), 0, 1 ) : '' // First initial of last word, if exists
		) );

		$user->data->initials = trim( sprintf(
			'%s%s',
			substr( $names[0], 0, 1 ), // First letter of first word
			count( $names ) > 1 ? substr( end( $names ), 0, 1 ) : substr( $names[0], 1, 1 ) // First letter of last word, if exists. Otherwise, second letter of first word
		) );

		$user->data->avatar = get_avatar_url(
			$user->ID,
			array(
				'default' => 'blank'
			)
		);

		// Add placeholder to avoid javascript  errors.
		$user->capabilities = (object) array(
			'admin'  => array(),
			'boards' => array(),
		);

		$user->options      = (object) array(
			'app'    => array(),
			'boards' => array(),
		);

		$user->follows = (object) array(
			'cards' => array()
		);

		// Only return data, cos it's all we need.
		return $user->data;
	}

	/**
	 * Get all users, with caps, for a specific board
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function ajax_get_admin() {

		if ( !Kanban_User::instance()->current_user_has_cap('admin-users') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		$boards = Kanban_User_Cap::instance()->get_admin_boards();

		$user_ids = array_keys( $boards );

		$users = $this->get_users( $user_ids, true );

		return $users;
	}

	public function get_users( $user_ids, $with_boards = false, $with_options = false ) {

		if ( empty($user_ids) ) return array();

		$user_ids = array_map( 'intval', $user_ids );

		$args = array(
			'include' => $user_ids,
			'blog_id' => $GLOBALS['blog_id'],
			'fields'  => 'all',
		);

		$users = get_users( $args );

		$users_by_user_id = array();
		foreach ( $users as &$user ) {
			$users_by_user_id[ $user->ID ] = $this->format_user_for_app( $user );
		}

		if ( $with_boards ) {
			$boards = Kanban_User_Cap::instance()->get_boards_by_user_ids( $user_ids );

			$boards_by_user_id = array();
			foreach ( $boards as $board ) {

				if ( ! is_array( $boards_by_user_id[ $board->user_id ] ) ) {
					$boards_by_user_id[ $board->user_id ] = array();
				}

				$boards_by_user_id[ $board->user_id ][ $board->board_id ] = $board;
			}

			foreach ( $users_by_user_id as &$user ) {
				$user = Kanban_User_Cap::instance()->add_caps_to_user( $user, $boards_by_user_id[ $user->id ] );
			}

		}

		if ( $with_options ) {
			$boards = Kanban_User_Option::instance()->get_boards_by_user_ids( $user_ids );

			$boards_by_user_id = array();
			foreach ( $boards as $board ) {

				if ( ! is_array( $boards_by_user_id[ $board->user_id ] ) ) {
					$boards_by_user_id[ $board->user_id ] = array();
				}

				$boards_by_user_id[ $board->user_id ][ $board->board_id ] = $board;
			}

			foreach ( $users_by_user_id as &$user ) {
				$user = Kanban_User_Option::instance()->add_options_to_user( $user, $boards_by_user_id[ $user->id ] );
			}

		}

		// If current user is included, make sure they have full caps.
		if ( isset($users_by_user_id[get_current_user_id()]) ) {
			$users_by_user_id[get_current_user_id()] = $this->get_current();
		}

		return $users_by_user_id;
	} // get_wp_users


	// @todo rename to format_wp_user_for_app

	public function get_current() {

		if ( !isset($this->current_user) ) {

			$user_id = get_current_user_id();

			$current_user = $this->get_user( $user_id, true );

			$current_user->follows->cards = Kanban_Card_User::instance()->get_card_ids_for_current_user();

			// For debugging
			if ( isset( $_GET['caps'] ) ) {
				if ( Kanban_User_Cap::instance()->user_has_cap( $current_user, 'admin' ) ) {
					$caps = explode( ',', $_GET['caps'] );

					if ( ! empty( $caps ) ) {
						$current_user->capabilities->admin = $caps;

//					foreach ( $current_user->capabilities->boards as &$board ) {
//						$board->capabilities = array();
//					}
					}
				}
			}

			$this->current_user = Kanban_User_Option::instance()->add_options_to_user( $current_user );
		}

		return $this->current_user;
	} // format_user_for_app

	public function current_user_has_cap($cap) {

		if ( !is_user_logged_in() ) return false;

		if ( Kanban_User_Cap::instance()->user_has_cap( Kanban_User::instance()->get_current(), $cap ) ) {
			return true;
		}

		return false;
	} // current_user_has_cap

	public function get_user( $user_id, $with_boards = false ) {

		$user = get_user_by( 'ID', $user_id );

		if ( empty( $user ) ) {
			return $this->empty_user();
		}

		$user = $this->format_user_for_app( $user );

		if ( $with_boards ) {
			$user = Kanban_User_Cap::instance()->add_caps_to_user( $user );
		}

		return $user;

	}


	public function empty_user () {
		return (object) array(
			'id'                 => 0,
			'display_name'       => '',
			'display_name_long'  => '',
			'display_name_short' => '',
			'initials'           => '',
			'capabilities'       => (object) array(
				'admin'  => array(),
				'boards' => array()
			),
			'options'            => (object) array(
				'app'    => array(),
				'boards' => array()
			),
			'follows'            => (object) array(
				'cards'    => array()
			)
		);
	}
}