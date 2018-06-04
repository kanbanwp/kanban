<?php


class Kanban_Notification {

	private static $instance = false;

	private $from;

	public function get_from () {

		if ( !isset($this->from) ) {

			$app = Kanban_App_Option::instance()->get_row();

			$email = get_bloginfo( 'admin_email' );
			if ( isset($app->options['notification_from_email']) ) {
				$email = $app->options['notification_from_email'];
			}

			$name = get_bloginfo( 'name' );
			if ( isset($app->options['notification_from_name']) ) {
				$name = $app->options['notification_from_name'];
			}

			$this->from = sprintf(
				'From: %s <%s>',
				apply_filters( 'kanban_notification_from_name', $name ),
				apply_filters( 'kanban_notification_from_email', $email )
			);

		}

		return $this->from;
	}

	public function notify_users ($user_ids, $subject, $message) {

		$users = Kanban_User::instance()->get_users($user_ids);

		if ( !empty($users) ) {

			$headers = $this->get_from() . "\r\n";

			foreach ($users as $user) {
				$success = wp_mail(
					$user->user_email,
					$subject,
					$message,
					$headers
				);
			}
		}

	}

	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

		}

		return self::$instance;
	}
}

