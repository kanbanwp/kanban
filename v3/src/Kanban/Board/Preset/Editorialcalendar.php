<?php



class Kanban_Board_Preset_Editorialcalendar extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => __('Editorial Calendar', 'kanban'),
			'description' => __('Use Kanban to track blog posts or articles, the authors who write them, and when they\'re published.', 'kanban'),
			'icon' => 'ei ei-calendar'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => __('Ideas', 'kanban'),
			),
			array (
				'label' => __('Assigned', 'kanban'),
			),
			array (
				'label' => __('In progress', 'kanban'),
			),
			array (
				'label' => __('Edit', 'kanban'),
			),
			array (
				'label' => __('Publish', 'kanban'),
			),
			array (
				'label' => __('Published', 'kanban')
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