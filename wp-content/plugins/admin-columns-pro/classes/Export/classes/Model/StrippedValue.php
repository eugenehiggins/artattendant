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

	/**
	 * Strips all HTML from content. Also, replace <br> with a space for readability.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function get_value( $id ) {
		return strip_tags( str_replace( array( '<br/>', '<br>' ), ' ', parent::get_value( $id ) ) );
	}

}
