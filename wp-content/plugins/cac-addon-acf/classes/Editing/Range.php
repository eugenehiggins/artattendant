<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Range extends ACA_ACF_Editing {

	public function get_view_settings() {
		$data = parent::get_view_settings();
		$data['type'] = 'acf_range';

		$field = $this->column->get_field();

		if ( $field->get( 'default_value' ) ) {
			$data['default_value'] = $field->get( 'default_value' );
		}
		if ( ! isset( $data['range_step'] ) ) {
			$data['range_step'] = 'any';
		}

		return $data;
	}

}
