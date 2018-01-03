<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting the column's raw value, but with stripped HTML tags
 *
 * @since 4.1
 */
class ACP_Export_Model_StrippedRawValue extends ACP_Export_Model_RawValue {

	public function get_value( $id ) {
		return strip_tags( parent::get_value( $id ) );
	}

}
