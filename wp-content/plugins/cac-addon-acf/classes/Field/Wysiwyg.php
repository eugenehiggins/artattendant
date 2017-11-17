<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Wysiwyg extends ACA_ACF_Field {

	public function get_dependent_settings() {
		return array(
			new AC_Settings_Column_WordLimit( $this->column ),
		);
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Textarea( $this->column );
	}

	public function sorting() {
		return new ACA_ACF_Sorting( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering( $this->column );
	}

}
