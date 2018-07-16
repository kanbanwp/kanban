<?php


class Kanban_Notification {

	private static $instance = false;

	private $from;
	/*
	public function build_card_notifications ($card_id, $subject, $message) {

		// Get card.
		$card = Kanban_Card::instance()->get_row($card_id);

		if ( empty($card) ) return false;

//		$board_id = Kanban_Card::instance()->get_board_id_by_card_id($card_id);
//		$board = Kanban_Board::instance()->get_row($board_id);

		// Get everyone following the card.
		$user_ids = Kanban_Card_User::instance()->get_user_ids_by_card_id($card_id);

		// Remove the user that last modified it, so they don't notify themselves.
//		if (($key = array_search( $card->modified_user_id, $user_ids)) !== false) {
//			unset($user_ids[$key]);
//		}

		// Find mentions in content.
		$mention_user_ids = $this->find_mentions_in_string($content);

		if ( !empty($mention_user_ids) ) {

			// Remove mentioned users from followers.
			$user_ids = array_diff($user_ids, $mention_user_ids);

			// Send emails to mentions.
//			$subject = sprintf(
//				__( 'You were mentioned on the Kanban board "%s"' ),
//				Kanban_Board::instance()->get_label( $board )
//			);

			$message = Kanban_Template::instance()->render(
				Kanban::instance()->settings()->path . '/board/emails/card-mention.php',
				array (
					'field_name' => $field_name,
					'card_name' => $card_id,
					'board_name' => Kanban_Board::instance()->get_label( $board ),
					'card_url' => add_query_arg(
						array(
							'board' => $board_id,
							'modal' => 'card',
							'card' => $card_id
						),
						Kanban_Router::instance()->get_page_uri('board')
					)
				)
			);

			$email = Kanban_Template::instance()->render(
				Kanban::instance()->settings()->path . '/templates/email-inline.php',
				array (
					'subject' => $subject,
					'content' => $message,
					'preview' => $subject,
					'unsubscribe_url' => '' // $unsubscribe_url

				)
			);

			Kanban_Notification::instance()->notify_users( $user_ids, $subject, $message );

			// Add mentions to card users.
		}

		// Send emails to followers.
		if ( ! empty( $user_ids ) ) {

			$message = Kanban_Template::instance()->render(
				Kanban::instance()->settings()->path . '/board/emails/card-follow.php',
				array (
					'card_name' => $card_id,
					'board_name' => Kanban_Board::instance()->get_label( $board ),
					'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
					'card_url' => add_query_arg(
						array(
							'board' => $board_id,
							'modal' => 'card',
							'card' => $card_id
						),
						Kanban_Router::instance()->get_page_uri('board')
					)
				)
			);

			$email = Kanban_Template::instance()->render(
				Kanban::instance()->settings()->path . '/templates/email-inline.php',
				array (
					'subject' => $subject,
					'content' => $message,
					'preview' => $subject,
					'unsubscribe_url' => '' // $unsubscribe_url

				)
			);

			Kanban_Notification::instance()->notify_users( $user_ids, $subject, $email );

		}
	}
	*/

//	public function find_mentions_in_string ($content) {
//		preg_match_all( '/data-mention=\"([0-9]*)\"/',
//			$content,
//			$matches,
//			PREG_PATTERN_ORDER
//		);
//
//		$user_ids = array();
//
//		if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
//			$user_ids = array_filter( array_unique( $matches[1] ) );
//		}
//
//		return $user_ids;
//	}

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

	public function notify_users_with_email_template ($user_ids, $subject, $message) {
		$email = Kanban_Template::instance()->render(
			Kanban::instance()->settings()->path . '/templates/email-inline.php',
			array(
				'subject'         => $subject,
				'content'         => $message,
				'preview'         => $subject,
//				'unsubscribe_url' => '' // $unsubscribe_url
			)
		);

		Kanban_Notification::instance()->notify_users( $user_ids, $subject, $email );
	}

	public function notify_users ($user_ids, $subject, $message, $format = 'html') {

		// Get the new users, with options.
		$users = Kanban_User::instance()->get_users($user_ids, false, true);

		// If a user's do_notifications option is false, remove from notifications.
		foreach ($users as $user_id => $user) {
			if ( isset($user->options->app['do_notifications']) && ! (bool) $user->options->app['do_notifications'] ) {
				unset($users[$user_id]);
			}
		}

		if ( !empty($users) ) {

			$headers = $this->get_from() . "\r\n";
			$headers .= 'Content-Type: text/html; charset=UTF-8;' . "\r\n";


//			if ( $format == 'html' ) {
//				echo 'test' . ' - LINE ' . __LINE__ . "\n<br />";
//
//				add_filter( 'wp_mail_content_type', array($this, 'wpdocs_set_html_mail_content_type') );
//			}

			foreach ($users as $user) {
				$success = wp_mail(
					$user->user_email,
					$subject,
					$message,
					$headers
				);
			}

//			if ( $format == 'html' ) {
//				remove_filter( 'wp_mail_content_type', array($this, 'wpdocs_set_html_mail_content_type') );
//			}
		}

	}

	/**
	 * @link https://developer.wordpress.org/reference/hooks/wp_mail_content_type/#comment-content-777
	 * @return string
	 */
	function wpdocs_set_html_mail_content_type() {
		return 'text/html';
	}

	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

		}

		return self::$instance;
	}
}

