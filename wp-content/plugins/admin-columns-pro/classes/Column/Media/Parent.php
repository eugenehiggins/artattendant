<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Media_Parent extends AC_Column_Media_Parent
	implements ACP_Column_FilteringInterface, ACP_Export_Column {

	public function filtering() {
		return new ACP_Filtering_Model_Post_Parent( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Parent( $this );
	}

}
