<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.1
 */
class ACP_Column_Comment_Post extends AC_Column_Comment_Post
	implements ACP_Export_Column {

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this );
	}

}
