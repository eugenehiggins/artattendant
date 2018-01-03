<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post title (default column) exportability model
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_Post_Title extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_the_title( $id );
	}

}
