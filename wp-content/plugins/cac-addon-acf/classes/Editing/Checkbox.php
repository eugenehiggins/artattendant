<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Checkbox extends ACA_ACF_Editing_Options {

	public function get_view_settings() {
		$data = parent::get_view_settings();

		$data['type'] = 'checklist';

		return $data;
	}

}
