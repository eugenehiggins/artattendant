<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Radio extends ACA_ACF_Field_Select {

	public function editing() {
		return new ACA_ACF_Editing_Radio( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_Radio( $this->column );
	}

}
