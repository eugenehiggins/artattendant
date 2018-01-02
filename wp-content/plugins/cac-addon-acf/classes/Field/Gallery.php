<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Gallery extends ACA_ACF_Field {

	public function get_value( $id ) {
		$collection = new AC_Collection( (array) $this->get_raw_value( $id ) );
		$removed = $collection->limit( $this->column->get_setting( 'number_of_items' )->get_value() );

		return ac_helper()->html->images( $this->column->get_formatted_value( $collection->all() ), $removed );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Gallery( $this->column );
	}

	public function sorting() {
		return new ACA_ACF_Sorting_Gallery( $this->column );
	}

	// Settings

	public function get_dependent_settings() {
		return array(
			new AC_Settings_Column_Image( $this->column ),
			new AC_Settings_Column_NumberOfItems( $this->column ),
		);
	}

}
