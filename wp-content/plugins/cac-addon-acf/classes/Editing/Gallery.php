<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_Gallery extends ACA_ACF_Editing {

	public function get_view_settings() {
		$data = array(
			'type'         => 'media',
			'attachment'   => array(
				'library' => array(
					'type' => 'image',
				),
			),
			'multiple'     => true,
			'store_values' => true,
		);

		if ( $this->column->get_field()->get( 'required' ) ) {
			$data['clear_button'] = true;
		}

		if ( 'uploadedTo' === $this->column->get_field()->get( 'library' ) ) {
			$data['attachment']['library']['uploaded_to_post'] = true;
		}

		return $data;
	}

}
