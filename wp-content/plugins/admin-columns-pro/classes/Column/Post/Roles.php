<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_Post_Roles extends AC_Column_Post_Roles
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Roles( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Roles( $this );
	}

}
