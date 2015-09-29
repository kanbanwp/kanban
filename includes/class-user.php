<?php



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



Kanban_User::init();



class Kanban_User
{
	static $instance = false;

	static function init()
	{
		self::$instance = self::get_instance();

		add_action( 'wp', array(__CLASS__, 'login') );
		add_action( 'wp', array(__CLASS__, 'request_access') );
	}



	static function request_access()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'request_access') ) return;

		$admin_email = get_option('admin_email');
		$blogname = get_option('blogname');

		$headers = "From: " . $admin_email . "\r\n";

		$current_user_id = get_current_user_id();
		$current_user = get_user_by('id', $current_user_id);

		wp_mail(
			$admin_email,
			__( sprintf(
				'%s: %s has requested access',
				Kanban::$instance->settings->pretty_name,
				Kanban_User::format_user_name ($current_user)
			), Kanban::get_text_domain() ),
			__( sprintf(
				'The following user has requested access. ' . "\n"
				. '%s' . "\n\n"
				. 'To grant them access, please visit this link:' . "\n"
				. '%s' . "\n"
				. 'And select them as an allowed user.',
				Kanban_User::format_user_name ($current_user),
				admin_url('admin.php?page=' . Kanban::$instance->settings->basename)
			), Kanban::get_text_domain() ),
			$headers
		);



		Kanban::$instance->flash->add('success', __( 'Your request has been sent.', Kanban::get_text_domain() ) );



		wp_redirect($_POST['_wp_http_referer']);
		exit;
	}



	static function login()
	{
		if (  !isset( $_POST[Kanban_Utils::get_nonce()] ) || ! wp_verify_nonce( $_POST[Kanban_Utils::get_nonce()], 'login') ) return;



		$user_by_email = get_user_by_email( $_POST['email'] );

		if ( empty($user_by_email) )
		{
			Kanban::$instance->flash->add('danger', __( 'Whoops! We can\'t find an account for that email address.', Kanban::get_text_domain() ) );
			wp_redirect($_POST['_wp_http_referer']);
			exit;
		}



		$creds = array();
		$creds['user_login'] = $user_by_email->user_login;
		$creds['user_password'] = $_POST['password'];
		$creds['remember'] = true;

		$user = wp_signon( $creds, false );



		if ( is_wp_error($user) )
		{
			Kanban::$instance->flash->add('danger', __( 'Whoops! That password is incorrect for this email address.', Kanban::get_text_domain() ) );
			wp_redirect($_POST['_wp_http_referer']);
			exit;
		}



		wp_set_current_user( $user->ID );
	    wp_set_auth_cookie( $user->ID );



		wp_redirect(sprintf('/%s/board', Kanban::$slug));
		exit;


	} // user_login



	static function get_allowed_users ()
	{
		if ( !isset(Kanban_User::$instance->allowed_users) )
		{
			$users_field_name = sprintf('%s_user', Kanban::$instance->settings->basename);
			$allowed_user_ids = Kanban_Settings::get_option($users_field_name, 'allowed_users', array());

			if ( empty($allowed_user_ids) )
			{
				$allowed_user_ids = array(0);
			}

			$pm_users = get_users(array(
				'include' => $allowed_user_ids
			));

			Kanban_User::$instance->allowed_users = Kanban_Utils::build_array_with_id_keys($pm_users);

			foreach (Kanban_User::$instance->allowed_users as $user_id => $user)
			{
				if(self::validate_gravatar($user->data->user_email))
				{
					Kanban_User::$instance->allowed_users[$user_id]->data->avatar = get_avatar($user->data->user_email);
				}

				Kanban_User::$instance->allowed_users[$user_id]->data->long_name_email = Kanban_User::format_user_name ($user);
				Kanban_User::$instance->allowed_users[$user_id]->data->short_name = Kanban_User::format_user_name ($user, TRUE);
				Kanban_User::$instance->allowed_users[$user_id]->data->initials = Kanban_User::get_initials ($user);
			}
		}

		return apply_filters(
			sprintf('%s_after_get_allowed_users', Kanban::$instance->settings->basename),
			Kanban_User::$instance->allowed_users
		);
	}




	static function format_user_name ($user, $short = FALSE)
	{
		if ( $short )
		{
			if ( !empty($user->first_name) )
			{
				return sprintf('%s %s', $user->first_name, substr($user->last_name, 0, 1));
			}
			else
			{
				$parts = explode("@", $user->user_email);
				$username = $parts[0];
				return $username;
			}
		}
		else
		{
			if ( !empty($user->first_name) )
			{
				return sprintf('%s %s (%s)', $user->first_name, $user->last_name, $user->user_email );
			}
			else
			{
				return $user->user_email;
			}
		}
	}



	static function get_initials ($user)
	{
		if ( !empty($user->first_name) )
		{
			$initials = sprintf(
				'%s%s',
				substr($user->first_name, 0, 1),
				substr($user->last_name, 0, 1)
			);
		}
		else
		{
			$initials = substr($user->user_email, 0, 2);
		}

		return strtoupper($initials);
	}



	/**
	 * Utility function to check if a gravatar exists for a given email or id
	 * @link https://gist.github.com/justinph/5197810
	 * @param int|string|object $id_or_email A user ID,  email address, or comment object
	 * @return bool if the gravatar exists or not
	 */

	static function validate_gravatar($id_or_email) {
	  //id or email code borrowed from wp-includes/pluggable.php
		$email = '';
		if ( is_numeric($id_or_email) ) {
			$id = (int) $id_or_email;
			$user = get_userdata($id);
			if ( $user )
				$email = $user->user_email;
		} elseif ( is_object($id_or_email) ) {
			// No avatar for pingbacks or trackbacks
			$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
			if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
				return false;

			if ( !empty($id_or_email->user_id) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_userdata($id);
				if ( $user)
					$email = $user->user_email;
			} elseif ( !empty($id_or_email->comment_author_email) ) {
				$email = $id_or_email->comment_author_email;
			}
		} else {
			$email = $id_or_email;
		}

		$hashkey = md5(strtolower(trim($email)));
		$uri = 'http://www.gravatar.com/avatar/' . $hashkey . '?d=404';

		$data = wp_cache_get($hashkey);
		if (false === $data) {
			$response = wp_remote_head($uri);
			if( is_wp_error($response) ) {
				$data = 'not200';
			} else {
				$data = $response['response']['code'];
			}
		    wp_cache_set($hashkey, $data, $group = '', $expire = 60*5);

		}		
		if ($data == '200'){
			return true;
		} else {
			return false;
		}
	}


	static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}



	private function __construct() { }
}


