<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Comment_Date extends AC_Column_Comment_Date
	implements ACP_Column_FilteringInterface, ACP_Export_Column {

	public function filtering() {
		return new ACP_Filtering_Model_Comment_Date( $this );
	}

	public function export() {
		return new ACP_Export_Model_Comment_Date( $this );
	}

}
