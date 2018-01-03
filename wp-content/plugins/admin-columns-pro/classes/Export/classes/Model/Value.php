<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting the column's output value
 *
 * @since 4.1
 */
class ACP_Export_Model_Value extends ACP_Export_Model {

	public function get_value( $id ) {
		$value = $this->get_column()->get_value( $id );

		if ( $value === $this->get_column()->get_empty_char() ) {
			$value = $this->get_empty_char();
		}

		return $value;
	}

	/**
	 * What to return for an empty char
	 *
	 * @return string
	 */
	public function get_empty_char() {
		return '';
	}

}
