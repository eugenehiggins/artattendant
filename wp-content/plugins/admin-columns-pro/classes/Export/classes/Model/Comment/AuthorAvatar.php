<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since NEWVERSION
 */
class ACP_Export_Model_Comment_AuthorAvatar extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_avatar_url( get_comment( $id ) );
	}

}
