<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Third party field
 */
class ACA_ACF_Field_ImageCrop extends ACA_ACF_Field {

	// Display

	public function get_value( $id ) {
		$value = get_field( $this->column->get_meta_key(), $this->column->get_formatted_id( $id ), true );

		if ( 'object' == $this->column->get_acf_field_option( 'save_format' ) ) {
			$value = $value['url'];
		}

		return $this->column->get_formatted_value( $value );
	}

	// Settings

	public function get_dependent_settings() {
		return array(
			new AC_Settings_Column_Image( $this->column ),
		);
	}

}
