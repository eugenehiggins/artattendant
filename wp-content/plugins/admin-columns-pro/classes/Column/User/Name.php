<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0.7
 */
class ACP_Column_User_Name extends AC_Column_User_Name
	implements ACP_Column_SortingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model_User_Name( $this );
	}

	public function export() {
		return new ACP_Export_Model_User_Name( $this );
	}

}
