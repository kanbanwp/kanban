<?php



class Kanban_Board_Preset_Projectmanagement extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => 'Project Management',
			'description' => 'Track tasks across projects, assign tasks to users, see who\'s working on what.',
			'lanes' => 'Backlog, Ready, In progress, QA, Done, Archive',
			'icon' => 'ei ei-easel'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => 'Backlog',
			),
			array (
				'label' => 'Ready',
			),
			array (
				'label' => 'In progress',
			),
			array (
				'label' => 'QA',
			),
			array (
				'label' => 'Done',
			),
			array (
				'label' => 'Archive',
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