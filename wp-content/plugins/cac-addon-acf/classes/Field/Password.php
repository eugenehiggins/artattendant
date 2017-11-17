<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Password extends ACA_ACF_Field {

	// Settings

	public function get_dependent_settings() {
		return array(
			new AC_Settings_Column_Password( $this->column ),
		);
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Password( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_Password( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Meta( $this->column );
	}

}
