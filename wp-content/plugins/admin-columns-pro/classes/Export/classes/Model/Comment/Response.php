<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Response (default column) exportability model
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_Comment_Response extends ACP_Export_Model {

	public function get_value( $id ) {
		$comment = get_comment( $id );

		if ( ! $comment->comment_post_ID ) {
			return '';
		}

		return get_the_title( $comment->comment_post_ID );
	}

}
