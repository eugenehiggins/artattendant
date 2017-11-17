<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Email extends ACA_ACF_Field {

	public function get_value( $id ) {
		$email = parent::get_value( $id );

		if ( ! $email ) {
			 return false;
		}

		return ac_helper()->html->link( 'mailto:' . $email, $email );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Email( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Meta( $this->column );
	}

}
