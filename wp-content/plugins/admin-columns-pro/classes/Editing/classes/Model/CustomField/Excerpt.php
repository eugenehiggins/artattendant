<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_CustomField_Excerpt extends ACP_Editing_Model_CustomField {

	public function get_view_settings() {
		/* @var ACP_Editing_Settings_Excerpt $setting */
		$setting = $this->column->get_setting( 'edit' );

		return array(
			'type' => $setting->get_editable_type()
		);
	}

	public function register_settings() {
		$this->column->add_setting( new ACP_Editing_Settings_Excerpt( $this->column ) );
	}

}
