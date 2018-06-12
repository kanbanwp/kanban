<?php



class Kanban_Board_Preset_Salespipeline extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => __('Sales Pipeline', 'kanban'),
			'description' => __('Collect leads, Follow-up with proposals, close more deals.', 'kanban'),
			'icon' => 'ei ei-currency'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => __('New lead', 'kanban'),
			),
			array (
				'label' => __('1st contact', 'kanban'),
			),
			array (
				'label' => __('Proposal out', 'kanban'),
			),
			array (
				'label' => __('Proposal accepted', 'kanban'),
			),
			array (
				'label' => __('Rejected', 'kanban')
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