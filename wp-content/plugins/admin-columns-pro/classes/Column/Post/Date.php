<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_Date extends AC_Column_Post_Date
	implements ACP_Column_FilteringInterface, ACP_Column_EditingInterface, ACP_Export_Column {

	public function editing() {
		return new ACP_Editing_Model_Post_Date( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Date( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Date( $this );
	}

}
