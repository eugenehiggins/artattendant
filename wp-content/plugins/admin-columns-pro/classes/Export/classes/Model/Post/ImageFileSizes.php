<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.1
 */
class ACP_Export_Model_Post_ImageFileSizes extends ACP_Export_Model {

	public function get_value( $id ) {
		return ac_helper()->file->get_readable_filesize( array_sum( $this->get_column()->get_raw_value( $id ) ) );
	}

}
