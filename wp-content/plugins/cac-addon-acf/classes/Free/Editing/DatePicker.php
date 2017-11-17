<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Free_Editing_DatePicker extends ACA_ACF_Editing_DatePicker {

	public function save( $id, $value ) {
		$acf_save_format = $this->column->get_field()->get( 'date_format' );
		$format = ac_helper()->date->parse_jquery_dateformat( $acf_save_format );

		if ( $value ) {
			$value = date( $format, strtotime( $value ) );
		}

		parent::save( $id, $value );
	}

	public function get_edit_value( $id ) {
		$value = parent::get_edit_value( $id );

		// null will disable editing
		if ( ! $value ) {
			return false;
		}

		$acf_save_format = $this->column->get_field()->get( 'date_format' );
		$timestamp = ac_helper()->date->get_timestamp_from_format( $value, ac_helper()->date->parse_jquery_dateformat( $acf_save_format ) );

		return date( 'Ymd', $timestamp );
	}

}
