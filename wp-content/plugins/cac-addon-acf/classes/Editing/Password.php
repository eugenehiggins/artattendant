<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Password extends ACA_ACF_Editing {

	public function get_view_settings() {
		$data = parent::get_view_settings();

		$data['type'] = 'text' == $this->column->get_option( 'password_display' ) ? 'text' : 'password';

		return $data;
	}

}
