<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting the column's output value
 *
 * @since 4.1
 */
class ACP_Export_Model_StrippedValue extends ACP_Export_Model_Value {

	public function get_value( $id ) {
		return strip_tags( parent::get_value( $id ) );
	}

}
