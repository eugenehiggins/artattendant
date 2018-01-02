<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Menu extends AC_Column_Menu
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Menu( $this );
	}

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this );
	}

}
