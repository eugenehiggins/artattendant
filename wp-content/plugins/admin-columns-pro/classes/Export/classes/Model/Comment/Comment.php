<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Comment_Comment extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_comment_text( $id );
	}

}
