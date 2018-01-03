<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting the column's output value
 *
 * @since 4.1
 */
class ACP_Export_Model_Disabled extends ACP_Export_Model {

	public function is_active() {
		return false;
	}

	public function get_value( $id ) {
		return false;
	}

}
