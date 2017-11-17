<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Oembed extends ACA_ACF_Field {

	public function editing() {
		return new ACA_ACF_Editing_Text( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering( $this->column );
	}

	public function get_dependent_settings() {
		return array(
			new ACA_ACF_Setting_Oembed( $this->column ),
		);
	}

}
