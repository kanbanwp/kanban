<?php


class Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

//	private $board_id = '';

	public function ajax_get_presets_data () {
		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		return $this->get_presets_data();
	}

	public function ajax_add ($data) {
		if ( !Kanban_User::instance()->current_user_has_cap('board') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		$class = get_class( $this );

		if ( isset($data['board_id']) && !empty($data['board_id']) && is_numeric($data['board_id']) ) {
//			$board = Kanban_Board::instance()->get_row($data['board_id']);
		} else {

			$board = Kanban_Board::instance()->set_row(array(
				'label' => $class::instance()->info()['label']
			));

			$data['board_id'] = $board->id;
		}

		$lanes_order = array();
		foreach ( $class::instance()->lanes() as $class_lane) {
			$lane = Kanban_Lane::instance()->set_row( array(
				'label' => $class_lane['label'],
				'board_id' => $data['board_id']
			) );

			$lanes_order[] = $lane->id;
		}

		$fields_order = array();
		foreach ( $class::instance()->fields() as $class_field) {
			$field = Kanban_Field::instance()->set_row( array(
				'field_type' => $class_field['field_type'],
				'label' => $class_field['label'],
				'options' => $class_field['options'],
				'board_id' => $data['board_id']
			) );

			$fields_order[] = $field->id;
		}

		$board = Kanban_Board::instance()->set_row(array(
			'id' => $data['board_id'],
			'lanes_order' => $lanes_order,
			'fields_order' => $fields_order
		));

		return $board;
	}


	public function get_presets_data () {

		foreach (glob(Kanban::instance()->settings()->path . 'src/Kanban/Board/Preset/*.php') as $filename) {
			 include_once $filename;
		}

		$preset_classes = array();
		foreach (get_declared_classes() as $class) {
			if (is_subclass_of($class, __CLASS__)) {
				$preset_classes[$class] = $class::instance()->info();
				$preset_classes[$class]['class'] = str_replace('Kanban_', '', $class);
			}
		}

		return $preset_classes;
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

			add_action( 'plugins_loaded', array( self::$instance, 'maybe_load_page' ) );
		}

		return self::$instance;
	}
}