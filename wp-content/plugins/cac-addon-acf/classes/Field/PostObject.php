<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_PostObject extends ACA_ACF_Field {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->column->set_serialized( $this->column->get_acf_field_option( 'multiple' ) );
	}

	// Display

	public function get_value( $id ) {
		return $this->column->get_formatted_value( new AC_Collection( $this->get_raw_value( $id ) ) );
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 */
	public function get_raw_value( $id ) {
		return array_filter( (array) parent::get_raw_value( $id ) );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_PostObject( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Value( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_PostObject( $this->column );
	}

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this->column );
	}

	// Settings

	public function get_dependent_settings() {
		return array(
			new AC_Settings_Column_Post( $this->column ),
		);
	}

}
