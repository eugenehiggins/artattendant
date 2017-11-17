<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_TrueFalse extends ACA_ACF_Field {

	public function get_value( $id ) {
		$value = parent::get_value( $id );

		return ac_helper()->icon->yes_or_no( '1' == $value );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_TrueFalse( $this->column );
	}

	public function sorting() {
		return new ACA_ACF_Sorting( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_TrueFalse( $this->column );
	}

}
