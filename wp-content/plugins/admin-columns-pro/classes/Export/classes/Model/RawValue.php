<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting the column's raw value
 *
 * @since 4.1
 */
class ACP_Export_Model_RawValue extends ACP_Export_Model {

	public function get_value( $id ) {
		$raw_value = $this->get_column()->get_raw_value( $id );

		if ( ! is_scalar( $raw_value ) ) {
			return false;
		}

		return $raw_value;
	}

}
