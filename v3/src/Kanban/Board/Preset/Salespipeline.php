<?php



class Kanban_Board_Preset_Salespipeline extends Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

	public function info () {
		return array(
			'label' => 'Sales Pipeline',
			'description' => 'Collect leads, Follow-up with proposals, close more deals.',
			'lanes' => 'New lead, 1st contact, Proposal out, Proposal accepted, Rejected',
			'icon' => 'ei ei-currency'
		);
	}

	public function lanes () {

		return array (
			array (
				'label' => 'New lead',
			),
			array (
				'label' => '1st contact',
			),
			array (
				'label' => 'Proposal out',
			),
			array (
				'label' => 'Proposal accepted',
			),
			array (
				'label' => 'Rejected',
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