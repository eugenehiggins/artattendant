<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Free_Field_DatePicker extends ACA_ACF_Field_DatePicker {

	public function sorting() {
		return new ACP_Sorting_Model( $this->column );
	}

	public function editing() {
		return new ACA_ACF_Free_Editing_DatePicker( $this->column );
	}

	// Settings

	public function get_dependent_settings() {
		return array(
			new ACA_ACF_Free_Setting_Date( $this->column ),
		);
	}

}
