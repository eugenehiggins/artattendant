<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Post_Date extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_post( $id )->post_date;
	}

}
