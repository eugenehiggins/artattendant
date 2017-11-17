<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Select extends ACA_ACF_Editing_Options {

	public function get_view_settings() {
		$data = parent::get_view_settings();

		$data['type'] = 'select';
		if ( $this->column->get_field()->get( 'multiple' ) ) {
			$data['type'] = 'select2_dropdown';
			$data['multiple'] = true;
		}

		return $data;
	}

}
