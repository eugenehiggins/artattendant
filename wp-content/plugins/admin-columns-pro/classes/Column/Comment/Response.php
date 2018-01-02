<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Comment_Response extends AC_Column_Comment_Response
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface, ACP_Export_Column {

	public function filtering() {
		return new ACP_Filtering_Model_Comment_Response( $this );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Comment_Response( $this );
	}

	public function export() {
		return new ACP_Export_Model_Comment_Response( $this );
	}

}
