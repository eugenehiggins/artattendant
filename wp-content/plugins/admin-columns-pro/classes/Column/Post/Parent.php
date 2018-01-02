<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_Post_Parent extends AC_Column_Post_Parent
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model_Post_Parent( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Parent( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Parent( $this );
	}

	public function export() {
		return new ACP_Export_Model_PostTitleFromPostId( $this );
	}

}
