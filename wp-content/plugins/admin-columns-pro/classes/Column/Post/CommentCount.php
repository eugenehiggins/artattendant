<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_CommentCount extends AC_Column_Post_CommentCount
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model_Post_CommentCount( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_CommentCount( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_CommentCount( $this );
	}

}
