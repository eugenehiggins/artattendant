<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.1
 */
class ACP_Column_Comment_AuthorAvatar extends AC_Column_Comment_AuthorAvatar
	implements ACP_Export_Column {

	public function export() {
		return new ACP_Export_Model_Comment_AuthorAvatar( $this );
	}

}
