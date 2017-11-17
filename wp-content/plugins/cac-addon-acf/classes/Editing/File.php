<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_File extends ACA_ACF_Editing {

	public function get_view_settings() {
		$data = array(
			'type' => 'attachment',
		);

		if ( ! $this->column->get_field()->get( 'required' ) ) {
			$data['clear_button'] = true;
		}

		return $data;
	}

}
