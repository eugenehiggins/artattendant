<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Radio extends ACA_ACF_Editing_Options {

	public function get_view_settings() {
		$data = parent::get_view_settings();

		if ( ! $this->column->get_field()->get( 'multiple' ) ) {
			$data['type'] = 'select';
		}

		return $data;
	}

}
