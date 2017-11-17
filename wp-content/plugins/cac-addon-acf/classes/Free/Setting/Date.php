<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACA_ACF_Column $column
 */
class ACA_ACF_Free_Setting_Date extends ACA_ACF_Setting_Date
	implements AC_Settings_FormatValueInterface {

	protected function get_acf_date_format() {
		return ac_helper()->date->parse_jquery_dateformat( $this->column->get_field()->get( 'display_format' ) );
	}

	public function format( $value, $original_value ) {
		if ( ! $value ) {
			return false;
		}

		$acf_save_format = $this->column->get_field()->get( 'date_format' );
		$timestamp = ac_helper()->date->get_timestamp_from_format( $value, ac_helper()->date->parse_jquery_dateformat( $acf_save_format ) );

		return parent::format( date( 'c', $timestamp ), $original_value );
	}

}
