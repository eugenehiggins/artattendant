<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Repeater extends ACA_ACF_Field {

	// Display

	public function get_value( $id ) {
		$sub_field = $this->get_acf_sub_field();

		if ( empty( $sub_field ) ) {
			return false;
		}

		$raw_values = $this->get_raw_value( $id );

		if ( empty( $raw_values ) ) {
			return false;
		}

		$values = new AC_Collection();

		foreach ( $raw_values as $raw_value ) {
			if ( isset( $raw_value[ $sub_field['key'] ] ) ) {
				$values->push( $raw_value[ $sub_field['key'] ] );
			}
		}

		return $this->column->get_formatted_value( $values );
	}

	// Settings

	public function get_dependent_settings() {
		return array(
			new ACA_ACF_Setting_Subfield( $this->column ),
			new AC_Settings_Column_BeforeAfter( $this->column ),
			new AC_Settings_Column_Separator( $this->column ),
		);
	}

	// Helpers

	private function get_acf_sub_field() {
		return ACA_ACF_API::get_field( $this->column->get_setting( 'sub_field' )->get_value() );
	}

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this->column );
	}

}
