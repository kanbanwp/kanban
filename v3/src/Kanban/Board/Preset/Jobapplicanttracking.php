<?php



class Kanban_Board_Preset_Jobapplicanttracking extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => __('Job applicant tracking', 'kanban'),
			'description' => __('Collect job applicants, move them through the interview process, and decide who gets the job.', 'kanban'),
			'icon' => 'ei ei-briefcase'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => __('Applied', 'kanban'),
			),
			array (
				'label' => __('Interview 1', 'kanban'),
			),
			array (
				'label' => __('Interview 2', 'kanban'),
			),
			array (
				'label' => __('Offer made', 'kanban'),
			),
			array (
				'label' => __('Rejected', 'kanban'),
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