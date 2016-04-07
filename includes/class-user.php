<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_User::init();



class Kanban_User
{
	// the instance of this object
	private static $instance;



	static function init()
	{
		add_action( 'wp', array( __CLASS__, 'login' ) );
		add_action( 'wp', array( __CLASS__, 'request_access' ) );
	}



	/**
	 * if a logged in user sees the log in page, and requets access to the kanban board
	 * sends an email to the site admin
	 */
	static function request_access()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'request_access' ) ) return;

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
				Kanban_User::get_username_long ( $current_user )
			),
			sprintf(
				__(
					'The following user has requested access. ' . '\n'
					. '%s' . '\n\n'
					. 'To grant them access, please visit this link:' . '\n'
					. '%s' . '\n'
					. 'And select them as an allowed user.', 'kanban'
				),
				Kanban_User::get_username_long ( $current_user ),
				admin_url( 'admin.php?page=' . Kanban::get_instance()->settings->basename )
			),
			$headers
		);



		Kanban_Flash::flash (
			__( 'Your request has been sent.', 'kanban' ),
			'success'
		);



		wp_redirect( $_POST['_wp_http_referer'] );
		exit;
	}



	/**
	 * custom log in functionality, from custom log in page
	 */
	static function login()
	{
		if ( ! isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'login' ) ) return;



		if ( is_email( $_POST['email'] ) )
		{
			$user = get_user_by( 'email', $_POST['email'] );

			if ( empty( $user ) )
			{
				Kanban_Flash::flash (
					__( 'Whoops! We can\'t find an account for that email address.', 'kanban' ),
					'danger'
				);

				wp_redirect( $_POST['_wp_http_referer'] );
				exit;
			}
		}
		else
		{
			$user = get_user_by( 'login', $_POST['email'] );

			if ( empty( $user ) )
			{
				Kanban_Flash::flash (
					__( 'Whoops! We can\'t find an account for that username.', 'kanban' ),
					'danger'
				);

				wp_redirect( $_POST['_wp_http_referer'] );
				exit;
			}
		}



		$creds = array();
		$creds['user_login'] = $user->user_login;
		$creds['user_password'] = $_POST['password'];
		$creds['remember'] = true;

		$user = wp_signon( $creds, false );



		if ( is_wp_error( $user ) )
		{
			Kanban_Flash::flash (
				__( 'Whoops! That password is incorrect for this email address.', 'kanban' ),
				'danger'
			);

			wp_redirect( $_POST['_wp_http_referer'] );
			exit;
		}



		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );



		wp_redirect( sprintf( '%s/%s/board', site_url(), Kanban::$slug ) );
		exit;


	} // user_login



	/**
	 * get the users that are allowed to access the board
	 * @return array allowed user objects
	 */
	static function get_allowed_users()
	{
		if ( ! isset( Kanban_User::get_instance()->allowed_users ) )
		{
			global $wpdb;

			// get all settings
			$allowed_users = Kanban_Option::get_option( 'allowed_users' );

			// pull out allowed user id's
			$allowed_user_ids = array();

			if ( is_array( $allowed_users ) )
			{
				$allowed_user_ids = $allowed_users;
			}

			if ( empty( $allowed_user_ids ) )
			{
				$allowed_user_ids = array( 0 );
			}

			$allowed_user_ids_str = implode( ',', $allowed_user_ids );

			$users = $wpdb->get_results( "SELECT {$wpdb->users}.ID,
			{$wpdb->users}.user_email,
			first_name.meta_value AS 'first_name',
			last_name.meta_value AS 'last_name'
			FROM {$wpdb->users}
			LEFT JOIN {$wpdb->usermeta} first_name
			ON {$wpdb->users}.ID = first_name.user_id
			AND first_name.meta_key = 'first_name'
			LEFT JOIN {$wpdb->usermeta} last_name
			ON {$wpdb->users}.ID = last_name.user_id
			AND last_name.meta_key = 'last_name'
			WHERE {$wpdb->users}.ID IN ( $allowed_user_ids_str );" );

			// add users to object
			Kanban_User::get_instance()->allowed_users = Kanban_Utils::build_array_with_id_keys( $users, 'ID' );

			// load extra data
			foreach ( Kanban_User::get_instance()->allowed_users as $user_id => $user )
			{
				Kanban_User::get_instance()->allowed_users[$user_id]->caps = array( 'write' );

				// get gravatar
				if ( self::validate_gravatar( $user->user_email ) )
				{
					Kanban_User::get_instance()->allowed_users[$user_id]->avatar = get_avatar( $user->user_email );
				}

				// fancy name formating
				Kanban_User::get_instance()->allowed_users[$user_id]->long_name_email = Kanban_User::get_username_long ( $user );
				Kanban_User::get_instance()->allowed_users[$user_id]->short_name = Kanban_User::get_username_short ( $user, TRUE );
				Kanban_User::get_instance()->allowed_users[$user_id]->initials = Kanban_User::get_initials ( $user );
			}
		}

		return apply_filters( 'kanban_user_get_allowed_users_return', Kanban_User::get_instance()->allowed_users );
	}



	static function get_current_user ()
	{
		$allowed_users = self::get_allowed_users();

		// get the current user from the allowed users
		$current_user_id = get_current_user_id();
		return $allowed_users[$current_user_id];
	}



	/**
	 * build a formatted name for a WordPress user
	 * @param  object $user a WordPress user object
	 * @return string       the formated user name
	 */
	static function get_username_long( $user )
	{
		if ( ! empty( $user->first_name ) )
		{
			return sprintf( '%s %s (%s)', $user->first_name, $user->last_name, $user->user_email );
		}
		else
		{
			return $user->user_email;
		}
	}



	/**
	 * build a formatted name for a WordPress user
	 * @param  object $user a WordPress user object
	 * @return string       the formated user name
	 */
	static function get_username_short( $user )
	{
		if ( ! empty( $user->first_name ) )
		{
			return sprintf( '%s %s', $user->first_name, mb_substr( $user->last_name, 0, 1 ) );
		}
		else
		{
			$parts = explode( '@', $user->user_email );
			$username = $parts[0];
			return $username;
		}
	}



	/**
	 * get the initials of the first and last name for a user, if avaiable
	 * @param  object $user a WordPress user object
	 * @return string       the initials
	 */
	static function get_initials( $user )
	{
		if ( ! empty( $user->first_name ) )
		{
			$initials = sprintf(
				'%s%s',
				mb_substr( $user->first_name, 0, 1 ),
				mb_substr( $user->last_name, 0, 1 )
			);
		}
		else
		{
			$initials = mb_substr( $user->user_email, 0, 2 );
		}

		return strtoupper( $initials );
	}



	/**
	 * Utility function to check if a gravatar exists for a given email or id
	 * @link https://gist.github.com/justinph/5197810
	 * @param  int|string|object $id_or_email A user ID,  email address, or comment object
	 * @return bool                           if the gravatar exists or not
	 */
	static function validate_gravatar( $id_or_email ) {
		//id or email code borrowed from wp-includes/pluggable.php
		$email = '';
		if ( is_numeric( $id_or_email ) )
		{
			$id = (int) $id_or_email;
			$user = get_userdata( $id );
			if ( $user )
			{
				$email = $user->user_email;
			}
		}
		elseif ( is_object( $id_or_email ) )
		{
			// No avatar for pingbacks or trackbacks
			$allowed_comment_types = apply_filters(
				'get_avatar_comment_types',
				array( 'comment' )
			);

			if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
				return false;

			if ( ! empty( $id_or_email->user_id ) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_userdata( $id );
				if ( $user )
					$email = $user->user_email;
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
			wp_cache_set( $hashkey, $data, $group = '', $expire = 60*5);

		}
		if ( $data == '200' ){
			return true;
		} else {
			return false;
		}
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
