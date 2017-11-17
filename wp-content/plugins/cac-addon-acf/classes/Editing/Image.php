<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Image extends ACA_ACF_Editing {

	public function get_view_settings() {
		$data = array(
			'type' => 'media',
		);

		$data['attachment']['library']['type'] = 'image';

		if ( ! $this->column->get_field()->get( 'required' ) ) {
			$data['clear_button'] = true;
		}

		if ( 'uploadedTo' === $this->column->get_field()->get( 'library' ) ) {
			$data['attachment']['library']['uploaded_to_post'] = true;
		}

		return $data;
	}

}
