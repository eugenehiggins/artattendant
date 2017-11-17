<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Text extends ACA_ACF_Field {

	// Settings

	public function get_dependent_settings() {
		return array(
			new AC_Settings_Column_CharacterLimit( $this->column ),
		);
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Text( $this->column );
	}

	public function sorting() {
		return new ACA_ACF_Sorting( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering( $this->column );
	}

}
