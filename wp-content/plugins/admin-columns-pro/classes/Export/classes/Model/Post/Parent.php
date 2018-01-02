<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parent (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Post_Parent extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_the_title( wp_get_post_parent_id( $id ) );
	}

}
