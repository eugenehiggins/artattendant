<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Relationship extends ACA_ACF_Field_PostObject {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->column->set_serialized( true );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Relationship( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_PostObject( $this->column );
	}

}
