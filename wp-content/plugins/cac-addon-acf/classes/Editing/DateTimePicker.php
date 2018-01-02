<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_DateTimePicker extends ACA_ACF_Editing {

	public function get_view_settings() {
		$data = parent::get_view_settings();
		$field = $this->column->get_field();

		$data['type'] = 'date_time';
		$data['weekstart'] = $field->get( 'first_day' );

		if ( ! $field->get( 'required' ) ) {
			$data['clear_button'] = true;
		}

		return $data;
	}

}
