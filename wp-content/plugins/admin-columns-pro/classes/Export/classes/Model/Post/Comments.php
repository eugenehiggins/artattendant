<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comments (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Post_Comments extends ACP_Export_Model {

	public function get_value( $id ) {
		return wp_count_comments( $id )->total_comments;
	}

}
