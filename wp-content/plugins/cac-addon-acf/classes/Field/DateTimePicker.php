<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_DateTimePicker extends ACA_ACF_Field {

	// Pro

	public function sorting() {
		$model = new ACP_Sorting_Model_Meta( $this->column );
		$model->set_data_type( 'date' );

		return $model;
	}

	public function filtering() {
		return new ACA_ACF_Filtering_DateTimePicker( $this->column );
	}

	public function editing() {
		return new ACA_ACF_Editing_DateTimePicker( $this->column );
	}

	// Settings

	public function get_dependent_settings() {
		return array(
			new ACA_ACF_Setting_Date( $this->column ),
		);
	}

}
