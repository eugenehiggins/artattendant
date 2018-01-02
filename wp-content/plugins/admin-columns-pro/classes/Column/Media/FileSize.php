<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Media_FileSize extends AC_Column_Media_FileSize
	implements ACP_Column_SortingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model_Media_FileSize( $this );
	}

	public function export() {
		return new ACP_Export_Model_Value( $this );
	}

}
