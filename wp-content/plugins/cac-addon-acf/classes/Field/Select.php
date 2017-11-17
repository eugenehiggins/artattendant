<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Select extends ACA_ACF_Field {

	public function get_value( $id ) {
		$value = parent::get_value( $id );
		$choices = $this->column->get_field()->get( 'choices' );

		$options = array();
		foreach ( (array) $value as $value ) {
			if ( isset( $choices[ $value ] ) ) {
				$options[] = $choices[ $value ];
			}
		}

		return ac_helper()->html->implode( $options );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Select( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_Options( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Meta( $this->column );
	}

}
