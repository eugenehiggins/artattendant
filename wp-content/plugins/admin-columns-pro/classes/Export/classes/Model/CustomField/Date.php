<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Export_Model_CustomField_Date extends ACP_Export_Model {

	public function get_value( $id ) {
		$timestamp = ac_helper()->date->strtotime( $this->column->get_raw_value( $id ) );

		if ( ! $timestamp ) {
			return false;
		}

		// Spreadsheet date format
		return date( 'Y-m-d H:i:s', $timestamp );
	}

}
