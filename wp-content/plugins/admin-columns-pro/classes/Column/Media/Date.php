<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @see   ACP_Column_Post_Date
 * @since 4.0
 */
class ACP_Column_Media_Date extends AC_Column_Media_Date
	implements ACP_Export_Column {

	public function export() {
		return new ACP_Export_Model_Post_Date( $this );
	}

}
