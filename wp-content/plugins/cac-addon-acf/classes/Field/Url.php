<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Url extends ACA_ACF_Field {

	public function get_value( $id ) {
		$url = parent::get_value( $id );

		return ac_helper()->html->link( $url, str_replace( array( 'http://', 'https://' ), '', $url ) );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Url( $this->column );
	}

	public function sorting() {
		return new ACA_ACF_Sorting( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering( $this->column );
	}

}
