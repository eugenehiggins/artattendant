<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink column exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Post_Permalink extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_permalink( $id );
	}

}
