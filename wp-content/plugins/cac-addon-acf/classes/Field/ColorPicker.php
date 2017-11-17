<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_ColorPicker extends ACA_ACF_Field {

	public function get_value( $id ) {
		return ac_helper()->string->get_color_block( parent::get_value( $id ) );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_ColorPicker( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Meta( $this->column );
	}

}
