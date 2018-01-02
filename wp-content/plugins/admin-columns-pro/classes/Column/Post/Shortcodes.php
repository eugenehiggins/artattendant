<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Post_Shortcodes extends AC_Column_Post_Shortcodes
	implements ACP_Column_SortingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Shortcodes( $this );
	}

}
