<?php


class Kanban_Field_Colorpicker extends Kanban_Field {

	public function format_options_for_db($options) {

		$field_options = parent::format_options_for_db($options);

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'view_card_corner':
					$value = $this->format_bool_for_db($value);
					break;
				default:
					unset($options[$key]);
					break;
			}
		}

		$options += $field_options;

		return (array) $options;
	}

	public function format_options_for_app($options) {
		$field_options = parent::format_options_for_app($options);

		foreach ( $options as $key => &$value ) {
			switch ( $key ) {
				case 'view_card_corner':
					$value = $this->format_bool_for_app($value);
					break;
				default:
					unset($options[$key]);
					break;
			}
		}

		$options += $field_options;

		return (array) $options;
	}
}
