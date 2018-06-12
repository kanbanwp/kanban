<?php



class Kanban_Board_Preset_Editorialcalendar extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => 'Editorial Calendar',
			'description' => 'Use Kanban to track blog posts or articles, the authors who write them, and when they\'re published.',
			'lanes' => 'Ideas, Assigned, In progress, Edit, To publish, Published',
			'icon' => 'ei ei-calendar'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => 'Ideas',
			),
			array (
				'label' => 'Assigned',
			),
			array (
				'label' => 'In progress',
			),
			array (
				'label' => 'Edit',
			),
			array (
				'label' => 'Publish',
			),
			array (
				'label' => 'Published'
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