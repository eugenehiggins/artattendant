<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_Comments extends AC_Column_Post_Comments
	implements ACP_Export_Column {

	public function export() {
		return new ACP_Export_Model_Post_Comments( $this );
	}

}
