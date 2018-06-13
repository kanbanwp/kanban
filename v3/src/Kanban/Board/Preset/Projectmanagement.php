<?php



class Kanban_Board_Preset_Projectmanagement extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => 'Project Management',
			'description' => 'Track tasks across projects, assign tasks to users, see who\'s working on what.',
			'icon' => 'ei ei-easel'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => __('Backlog', 'kanban'),
			),
			array (
				'label' => __('Ready', 'kanban'),
			),
			array (
				'label' => __('In progress', 'kanban'),
			),
			array (
				'label' => __('QA', 'kanban'),
			),
			array (
				'label' => __('Done', 'kanban'),
			),
			array (
				'label' => __('Archive', 'kanban'),
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