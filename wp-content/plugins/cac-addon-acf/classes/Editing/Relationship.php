<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Relationship extends ACA_ACF_Editing_PostObject {

	public function get_view_settings() {
		$data = parent::get_view_settings();

		$data['multiple'] = true;

		return $data;
	}

}
