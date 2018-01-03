<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Media_ExifData extends AC_Column_Media_ExifData
	implements ACP_Column_SortingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model_Value( $this );
	}

	public function export() {
		return new ACP_Export_Model_Value( $this );
	}

}
