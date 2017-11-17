<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Number extends ACA_ACF_Editing {

	public function get_view_settings() {
		$data = parent::get_view_settings();

		$data['type'] = 'number';

		if ( ! isset( $data['range_step'] ) ) {
			$data['range_step'] = 'any';
		}

		return $data;
	}

}
