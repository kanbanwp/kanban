<?php


class Kanban_Field_Todo extends Kanban_Field {

	public function format_options_for_db($options) {

//		$field_options = parent::format_options_for_db($options);
//
//		foreach ( $options as $key => &$value ) {
//			switch ( $key ) {
//				case 'step':
//					$value = $this->format_float_for_db($value);
//					break;
//				case 'show_estimate':
//					$value = $this->format_bool_for_db($value);
//					break;
//				default:
//					unset($options[$key]);
//					break;
//			}
//		}
//
//		$options += $field_options;

		return (array) $options;
	}

	public function format_options_for_app($options) {
//		$field_options = parent::format_options_for_app($options);
//
//		foreach ( $options as $key => &$value ) {
//			switch ( $key ) {
//				case 'step':
//					$value = $this->format_float_for_app($value);
//					break;
//				case 'show_estimate':
//					$value = $this->format_bool_for_app($value);
//					break;
//				default:
//					unset($options[$key]);
//					break;
//			}
//		}
//
//		$options += $field_options;

		return (array) $options;
	}

	public function format_content_for_db($content) {

		$content = $this->format_json_for_db($content);

		return $content;
	}

	public function format_content_for_app($content) {

		$content = $this->format_json_for_app($content);

		return $content;
	}
}