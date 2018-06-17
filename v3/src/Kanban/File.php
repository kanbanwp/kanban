<?php


class Kanban_File {

	// the instance of this object
	private static $instance;

	private $upload_dir;
	private $files_dir_name = 'kanban-files';


	public function ajax_upload ($card_id ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$file_data = upload_from_post ($card_id);

		if ( !$file_data ) {
			header( 'HTTP/1.1 401 Error uploading file' );
			return false;
		}

		return $file_data;
	}

	public function upload_from_post ($card_id) {

		$dir = $this->get_files_dir();

		$file = $_FILES[ 'kanban-file' ];

		$file_name = sprintf( '%s-%s-%s',
			$card_id,
			time(),
			$file[ 'name' ]
		);

		if ( @move_uploaded_file( $file[ "tmp_name" ], $dir . '/' . $file_name ) ) {

			return (object) array(
				'file_name' => $file_name,
				'base_name' => $file[ 'name' ],
				'path' => $dir . '/' . $file_name,
				'url' => $this->get_files_url() . '/' . $file_name
			);

		} else {
			return false;
		}

	}


	public function get_files_dir() {

		if ( ! isset( $this->upload_dir ) ) {
			$upload_dir = wp_upload_dir();

			$dir = sprintf(
				'%s/%s',
				$upload_dir[ 'basedir' ],
				$this->files_dir_name
			);

			// create dir for task attachments
			if ( ! is_dir( $dir ) ) {
				mkdir( $dir );
			}

			$this->upload_dir = $dir;
		}

		return $this->upload_dir;
	}

	public function get_files_url() {
		$upload_dir = wp_upload_dir();

		return sprintf(
			'%s/%s',
			$upload_dir[ 'baseurl' ],
			$this->files_dir_name
		);
	}

	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

		}

		return self::$instance;
	}
}

