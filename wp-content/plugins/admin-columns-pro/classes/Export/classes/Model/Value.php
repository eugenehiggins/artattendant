<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting the column's output value
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_Value extends ACP_Export_Model {

	public function get_value( $id ) {
		return $this->get_column()->get_value( $id );
	}

}
