<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Gallery extends ACA_ACF_Field {

	public function editing() {
		return new ACA_ACF_Editing_Gallery( $this->column );
	}

	public function sorting() {
		return new ACA_ACF_Sorting_Gallery( $this->column );
	}

	public function get_dependent_settings() {
		return array(
			new AC_Settings_Column_Images( $this->column )
		);
	}

}
