<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_DatePicker extends ACA_ACF_Field {

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_DatePicker( $this->column );
	}

	public function sorting() {
		$model = new ACP_Sorting_Model_Meta( $this->column );
		$model->set_data_type( 'numeric' );

		return $model;
	}

	public function filtering() {
		return new ACA_ACF_Filtering_DatePicker( $this->column );
	}

	public function export() {
		return new ACA_ACF_Export_Date( $this->column );
	}

	// Settings

	public function get_dependent_settings() {
		return array(
			new ACA_ACF_Setting_Date( $this->column )
		);
	}

}
