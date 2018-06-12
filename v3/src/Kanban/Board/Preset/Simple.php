<?php



class Kanban_Board_Preset_Simple extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => __('Simple', 'kanban'),
			'description' => __('A simple 3-lane board to track basic tasks as you do them.', 'kanban'),
			'icon' => 'ei ei-check_alt2'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => __('To do', 'kanban'),
				'options' => array(
					'color' => '#fdd037'
				)
			),
			array (
				'label' => __('Doing', 'kanban'),
				'options' => array(
					'color' => '#55aa55'
				)
			),
			array (
				'label' => __('Done', 'kanban'),
				'options' => array(
					'color' => '#2a7fd4'
				)
			)
		);
	}

	public function fields () {

		return array (
			array (
				'field_type' => 'title',
				'label' => __('Title', 'kanban'),
				'options' => array(
					'placeholder' => __('Add a title', 'kanban')
				)
			),
			array (
				'field_type' => 'text',
				'label' => __('Description', 'kanban'),
				'options' => array(
					'placeholder' => __('Add a description', 'kanban')
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