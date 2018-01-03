<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting the column's raw value, but with stripped HTML tags
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_StrippedRawValue extends ACP_Export_Model {

	public function get_value( $id ) {
		return strip_tags( $this->get_column()->get_raw_value( $id ) );
	}

}
