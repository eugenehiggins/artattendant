<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author (default column) exportability model
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_Comment_Author extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_comment_author( $id );
	}

}
