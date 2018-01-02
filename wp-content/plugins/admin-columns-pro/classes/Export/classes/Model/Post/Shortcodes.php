<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes column exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Post_Shortcodes extends ACP_Export_Model {

	public function get_value( $id ) {
		$raw_value = $this->get_column()->get_raw_value( $id );

		return $raw_value ? implode( ', ', array_keys( $raw_value ) ) : '';
	}

}
