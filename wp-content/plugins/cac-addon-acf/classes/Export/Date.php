<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.2
 */
class ACA_ACF_Export_Date extends ACP_Export_Model {

	public function get_value( $id ) {
		$value = $this->column->get_raw_value( $id );

		if ( empty( $value ) || ! is_numeric( $value ) || 8 !== strlen( $value ) ) {
			return false;
		}

		$year = substr( $value, 0, 4 );
		$month = substr( $value, 4, 2 );
		$day = substr( $value, 6, 2 );

		return "$year-$month-$day";
	}

}
