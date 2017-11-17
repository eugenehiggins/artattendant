<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Url extends ACA_ACF_Editing {

	public function get_view_settings() {
		$data = parent::get_view_settings();

		$data['type'] = 'url';

		return $data;
	}

}
