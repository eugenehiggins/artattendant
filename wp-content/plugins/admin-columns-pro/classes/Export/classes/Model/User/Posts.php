<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Posts count (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_User_Posts extends ACP_Export_Model {

	public function get_value( $id ) {
		return count_user_posts( $id );
	}

}
