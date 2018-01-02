<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_User_Posts extends AC_Column_User_Posts
	implements ACP_Column_SortingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model_User_PostCount( $this );
	}

	public function export() {
		return new ACP_Export_Model_User_Posts( $this );
	}

}
