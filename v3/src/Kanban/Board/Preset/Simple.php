<?php



class Kanban_Board_Preset_Simple extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => 'Simple',
			'description' => 'A simple 3-lane board TO Track basic tasks as you do them.',
			'lanes' => 'To do, Doing, Done',
			'icon' => 'ei ei-check_alt2'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => 'To do',
			),
			array (
				'label' => 'Doing',
			),
			array (
				'label' => 'Done',
			)
		);
	}

	public function fields () {

		return array (
			array (
				'field_type' => 'title',
				'options' => array(
					'label' => 'Title',
					'placeholder' => 'Add a title'
				)
			),
			array (
				'field_type' => 'text',
				'options' => array(
					'label' => 'Description',
					'placeholder' => 'Add a description'
				)
			)
		);
	}


	/**
	 * construct that can't be overwritten
	 */
	private function __construct() {

	}

	/**
	 * get the instance of this class
	 *
	 * @return object the instance
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();

//			add_action( 'plugins_loaded', array( self::$instance, 'maybe_load_page' ) );
		}

		return self::$instance;
	}
}