<?php


class Kanban_Board_Preset {

	// the instance of this object
	private static $instance;

//	private $board_id = '';

	public function ajax_get_presets_data () {
		if ( !Kanban_User::instance()->current_user_has_cap('admin-board-create') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		return $this->get_presets_data();
	}

	public function ajax_add ($data) {
		if ( !Kanban_User::instance()->current_user_has_cap('admin-board-create') ) {
			header( 'HTTP/1.1 401 Current user does not have cap' );
			return false;
		}

		if ( !isset($data['add']) ) {
			$data['add'] = 'board, lanes and fields';
		}

		// Get the child preset.
		$class = get_class( $this );

		// If board_id is no good, add a new board.
		if ( !isset($data['board_id']) || empty($data['board_id']) || !is_numeric($data['board_id']) ) {

			// If "add" doesn't include "board", then don't add it.
			if ( strpos($data['add'], 'board') === -1 ) {
				return false;
			}

			// Get preset data.
			$preset_data = $class::instance()->info();

			// Create ne wboard.
			$board = Kanban_Board::instance()->set_row( array(
				'label' => $preset_data['label']
			) );

			// Add the new board id to data for saving.
			$data['board_id'] = $board->id;
		}

		// If board_id is no good, return.
		if ( !isset($data['board_id']) || empty($data['board_id']) || !is_numeric($data['board_id']) ) {
			return false;
		}

		// If "add" includes "lane", then add fields.
		if ( strpos($data['add'], 'lane') !== false ) {

			// Add preset lanes.
			$lanes_order = array();
			foreach ( $class::instance()->lanes() as $class_lane ) {

				// Add board to new lane.
				$class_lane['board_id'] = $data['board_id'];

				// Save lane.
				$lane = Kanban_Lane::instance()->set_row( $class_lane );

				// Add it to the list to be added to the board.
				$lanes_order[] = $lane->id;
			}

			// Update the board with new lanes and fields.
			$board = Kanban_Board::instance()->set_row(array(
				'id' => $data['board_id'],
				'lanes_order' => $lanes_order
			));
		}

		// If "add" includes "field", then add fields.
		if ( strpos($data['add'], 'field') !== false ) {

			// Add preset fields.
			$fields_order = array();
			foreach ( $class::instance()->fields() as $class_field ) {

				// Add board to new field.
				$class_field['board_id'] = $data['board_id'];

				// Save field.
				$field = Kanban_Field::instance()->set_row( $class_field );

				// Add it to the list to be added to the board.
				$fields_order[] = $field->id;
			}

			// Update the board with new lanes and fields.
			$board = Kanban_Board::instance()->set_row(array(
				'id' => $data['board_id'],
				'fields_order' => $fields_order
			));
		}

		return $board;
	}

	public function get_info () {
		// Get the child preset.
		$class = get_class( $this );

		$info = $class::instance()->info();
		$info['class'] = str_replace('Kanban_', '', $class);

		$info['lane_labels'] = '';
		$lane_labels = array();
		foreach ( $class::instance()->lanes() as $lane) {
			$lane_labels[]= $lane['label'];
		}
		$info['lane_labels'] = implode(', ', $lane_labels);

		$info['field_labels'] = '';
		$field_labels = array();
		foreach ( $class::instance()->fields() as $field) {
			$field_labels[]= $field['label'];
		}
		$info['field_labels'] = implode(', ', $field_labels);

		return $info;
	}


	public function get_presets_data () {

		foreach (glob(Kanban::instance()->settings()->path . 'src/Kanban/Board/Preset/*.php') as $filename) {
			 include_once $filename;
		}

		$preset_classes = array();
		foreach (get_declared_classes() as $class) {
			if (is_subclass_of($class, __CLASS__)) {
				$preset_classes[$class] = $class::instance()->get_info();
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
		}

		return self::$instance;
	}
}