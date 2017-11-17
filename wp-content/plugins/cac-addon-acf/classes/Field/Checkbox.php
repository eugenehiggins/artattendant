<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Checkbox extends ACA_ACF_Field_Select {

	public function __construct( $column ) {
		parent::__construct( $column );

		$column->set_serialized( true );
	}

	public function editing() {
		return new ACA_ACF_Editing_Checkbox( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_Checkbox( $this->column );
	}

}
