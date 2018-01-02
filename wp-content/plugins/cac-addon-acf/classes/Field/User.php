<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_User extends ACA_ACF_Field {

	public function get_value( $id ) {
		return $this->column->get_formatted_value( new AC_Collection( $this->get_raw_value( $id ) ) );
	}

	public function get_raw_value( $id ) {
		return array_filter( (array) parent::get_raw_value( $id ) );
	}

	// Settings

	public function get_dependent_settings() {
		return array(
			new AC_Settings_Column_User( $this->column ),
		);
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_User( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Value( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_User( $this->column );
	}

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this->column );
	}

}
