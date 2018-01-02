<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Name (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Term_Description extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_term( $id )->description;
	}

}
