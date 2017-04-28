<?php
/**
 * User functions for the Kanban plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }



/**
 * Class Kanban_User
 */
class Kanban_User
{
	/**
	 * The database records.
	 * @var array The database records.
	 */
	protected static $records = array();

	/**
	 * The database records sorted by board.
	 * @var array The database records sorted by board.
	 */
	protected static $records_by_board = array();


	/**
	 * Add all hooks.
	 */
	static function init() {
		add_action( 'init', array( __CLASS__, 'login' ) );
		add_action( 'init', array( __CLASS__, 'request_access' ) );
	}



	/**
	 * If a logged in user sees the log in page, and requets access to the kanban board
	 * sends an email to the site admin.
	 */
	static function request_access() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'request_access' ) ) { return; }

		$admin_email = get_option( 'admin_email' );
		$blogname = get_option( 'blogname' );

		$headers = 'From: ' . $admin_email . '\r\n';

		$current_user_id = get_current_user_id();
		$current_user = get_user_by( 'id', $current_user_id );

		wp_mail(
			$admin_email,
			sprintf(
				__(
					'%s: %s has requested access',
					'kanban'
				),
				Kanban::get_instance()->settings->pretty_name,
				Kanban_User::get_username_long( $current_user )
			),
			sprintf(
				__(
					"The following user has requested access. \n
					%s \n\n
					To grant them access, please visit this link: \n
					%s \n
					And select them as an allowed user.",
					'kanban'
				),
				Kanban_User::get_username_long( $current_user ),
				admin_url( 'admin.php?page=' . Kanban::get_instance()->settings->basename )
			),
			$headers
		);

		Kanban_Flash::flash(
			__( 'Your request has been sent.', 'kanban' ),
			'success'
		);

		wp_redirect( sanitize_text_field( wp_unslash( $_POST[ '_wp_http_referer' ] ) ) );
		exit;
	}



	/**
	 * Custom log in functionality, from custom log in page.
	 */
	static function login() {
		if ( ! isset( $_POST[ Kanban_Utils::get_nonce() ] ) || ! wp_verify_nonce( $_POST[ Kanban_Utils::get_nonce() ], 'login' ) ) { return; }

		if ( is_email( $_POST['email'] ) ) {
			$user = get_user_by( 'email', $_POST['email'] );

			if ( empty( $user ) ) {
				Kanban_Flash::flash(
					__( 'Whoops! We can\'t find an account for that email address.', 'kanban' ),
					'danger'
				);

				wp_redirect( sanitize_text_field( wp_unslash( $_POST[ '_wp_http_referer' ] ) ) );
				exit;
			}
		} else {
			$user = get_user_by( 'login', sanitize_text_field( $_POST['email'] ) );

			if ( empty( $user ) ) {
				Kanban_Flash::flash(
					__( 'Whoops! We can\'t find an account for that username.', 'kanban' ),
					'danger'
				);

				wp_redirect( sanitize_text_field( wp_unslash( $_POST[ '_wp_http_referer' ] ) ) );
				exit;
			}
		}

		$creds = array();
		$creds['user_login'] = $user->user_login;
		$creds['user_password'] = wp_unslash( $_POST['password'] );
		$creds['remember'] = true;

		$user = wp_signon( $creds, false );

		if ( is_wp_error( $user ) ) {
			Kanban_Flash::flash(
				__( 'Whoops! That password is incorrect for this email address.', 'kanban' ),
				'danger'
			);

			wp_redirect( sanitize_text_field( wp_unslash( $_POST[ '_wp_http_referer' ] ) ) );
			exit;
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );

		wp_redirect(
			site_url(
				isset( $_POST['redirect'] ) && filter_var( $_POST['redirect'], FILTER_VALIDATE_URL ) ? wp_unslash( $_POST['redirect'] ) : Kanban_Template::get_uri()
			)
		);
		exit;

	} // user_login


	/**
	 * Check if the current user has the cap.
	 *
	 * @param string $cap The cap to check for.
	 * @return bool
	 */
	static function current_user_has_cap( $cap ) {
		$user = self::get_current_user();
		return in_array( $cap, $user->caps );
	}


	/**
	 * Get the users that are allowed to access a board.
	 *
	 * @param null|int $board_id The id of the board to get users for.
	 * @return array|mixed Allowed user objects.
	 */
	static function get_allowed_users( $board_id = null ) {
		if ( empty( self::$records ) ) {
			global $wpdb;

			// Get all boards.
			$boards = Kanban_Board::get_all();

			// Fill records_by_board with boards and empty arrays.
			self::$records_by_board = array_fill_keys( array_keys( $boards ), array() );

			$query_in = array( 0 );
			foreach ( array_keys( $boards ) as $board_record_id ) {

				// Get all settings.
				$allowed_users = Kanban_Option::get_option( 'allowed_users', $board_record_id );

				// Pull out allowed user id's.
				$allowed_user_ids = array();

				if ( is_array( $allowed_users ) ) {
					$allowed_user_ids = $allowed_users;
				}

				if ( empty( $allowed_user_ids ) ) {
					$allowed_user_ids = array();
				}

				// Build array for querying.
				$query_in = array_merge( $query_in, $allowed_user_ids );

				self::$records_by_board[ $board_record_id ] = array_fill_keys( $allowed_user_ids, (object) array() );

			}

			$query_in = array_unique( $query_in );

			$allowed_user_ids_str = implode( ',', $query_in );

			$users = $wpdb->get_results(
				"SELECT users.ID,
				users.user_email,
				first_name.meta_value AS 'first_name',
				last_name.meta_value AS 'last_name'
	
				FROM {$wpdb->users} users
	
				LEFT JOIN {$wpdb->usermeta} first_name
				ON users.ID = first_name.user_id
				AND first_name.meta_key = 'first_name'
	
				LEFT JOIN {$wpdb->usermeta} last_name
				ON users.ID = last_name.user_id
				AND last_name.meta_key = 'last_name'
	
				WHERE users.ID IN ( $allowed_user_ids_str )
				;"
			);

			// Add users to object.
			self::$records = Kanban_Utils::build_array_with_id_keys( $users, 'ID' );

			// Load extra data.
			foreach ( self::$records as $user_id => $user ) {
				self::$records[ $user_id ]->caps = array( 'write' );

				// Get gravatar.
				if ( get_avatar( $user->user_email ) ) {
					// Make sure it uses double quotes.
					self::$records[ $user_id ]->avatar = addslashes(
						str_replace(
							"'",
							'"',
							get_avatar(
								$user->user_email,
								256,
								'kanbanblank',
								Kanban_User::get_username_long( $user )
							)
						)
					);

					if ( strpos( self::$records[ $user_id ]->avatar, 'kanbanblank') !== FALSE ) {
						unset(self::$records[ $user_id ]->avatar);
					}
				}

				// Fancy name formating.
				self::$records[ $user_id ]->long_name_email = Kanban_User::get_username_long( $user );
				self::$records[ $user_id ]->short_name = Kanban_User::get_username_short( $user, true );
				self::$records[ $user_id ]->initials = Kanban_User::get_initials( $user );
			}

			// Populate the boards.
			foreach ( self::$records_by_board as $board_record_id => $allowed_users ) {
				foreach ( $allowed_users as $user_id => $user ) {
					if ( ! isset( self::$records[ $user_id ] ) ) {
						unset( self::$records_by_board[ $board_record_id ][ $user_id ] );
						continue;
					}
					self::$records_by_board[ $board_record_id ][ $user_id ] = self::$records[ $user_id ];
				}
			}

			self::$records = apply_filters(
				'kanban_user_get_allowed_users_records',
				self::$records
			);

			self::$records_by_board = apply_filters(
				'kanban_user_get_allowed_users_records_by_board',
				self::$records_by_board
			);
		}

		if ( is_null( $board_id ) ) {
			return self::$records;
		}

		return isset( self::$records_by_board[ $board_id ] ) ? self::$records_by_board[ $board_id ] : array();
	}



	/**
	 * Get the current user from the allowed Kanban users.
	 *
	 * @return mixed|object WordPress user object.
	 */
	static function get_current_user() {
		$allowed_users = self::get_allowed_users();

		// Get the current user from the allowed users.
		$current_user_id = get_current_user_id();

		return isset( $allowed_users[ $current_user_id ] ) ? $allowed_users[ $current_user_id ] : (object) array();
	}



	/**
	 * Build a formatted name for a WordPress user.
	 *
	 * @param  object $user A WordPress user object.
	 * @return string       The formated user name.
	 */
	static function get_username_long( $user ) {
		if ( ! empty( $user->first_name ) ) {
			return sprintf( '%s %s (%s)', $user->first_name, $user->last_name, $user->user_email );
		} else {
			return $user->user_email;
		}
	}



	/**
	 * Build a formatted name for a WordPress user.
	 *
	 * @param  object $user A WordPress user object.
	 * @return string       The formated user name.
	 */
	static function get_username_short( $user ) {
		if ( ! empty( $user->first_name ) ) {
			return sprintf( '%s %s', $user->first_name, mb_substr( $user->last_name, 0, 1 ) );
		} else {
			$parts = explode( '@', $user->user_email );
			$username = $parts[0];
			return $username;
		}
	}



	/**
	 * Get the initials of the first and last name for a user, if avaiable
	 *
	 * @param  object $user A WordPress user object.
	 * @return string       The initials.
	 */
	static function get_initials( $user ) {
		if ( ! empty( $user->first_name ) ) {
			$initials = sprintf(
				'%s%s',
				mb_substr( $user->first_name, 0, 1 ),
				mb_substr( $user->last_name, 0, 1 )
			);
		} else {
			$initials = mb_substr( $user->user_email, 0, 2 );
		}

		return strtoupper( $initials );
	}



	/**
	 * Utility function to check if a gravatar exists for a given email or id.
	 *
	 * @link https://gist.github.com/justinph/5197810
	 *
	 * @param  int|string|object $id_or_email A user ID, email address, or comment object.
	 * @return bool If the gravatar exists or not.
	 */
	static function validate_gravatar( $id_or_email ) {
		// Id or email code borrowed from wp-includes/pluggable.php.
		$email = '';
		if ( is_numeric( $id_or_email ) ) {
			$id = (int) $id_or_email;
			$user = get_userdata( $id );
			if ( $user ) {
				$email = $user->user_email;
			}
		} elseif ( is_object( $id_or_email ) ) {
			// No avatar for pingbacks or trackbacks.
			$allowed_comment_types = apply_filters(
				'get_avatar_comment_types',
				array( 'comment' )
			);

			if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) ) {
				return false; }

			if ( ! empty( $id_or_email->user_id ) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_userdata( $id );
				if ( $user ) {
					$email = $user->user_email; }
			} elseif ( ! empty( $id_or_email->comment_author_email ) ) {
				$email = $id_or_email->comment_author_email;
			}
		} else {
			$email = $id_or_email;
		}

		$hashkey = md5( strtolower( trim( $email ) ) );
		$uri = 'http://www.gravatar.com/avatar/' . $hashkey . '?d=404';

		$data = wp_cache_get( $hashkey );
		if ( false === $data ) {
			$response = wp_remote_head( $uri );
			if ( is_wp_error( $response ) ) {
				$data = 'not200';
			} else {
				$data = $response['response']['code'];
			}
			wp_cache_set( $hashkey, $data, $group = '', $expire = 60 * 5 );

		}
		if ( $data == '200' ) {
			return true;
		} else {
			return false;
		}
	}



	/**
	 * Construct that can't be overwritten.
	 */
	private function __construct() { }
}
