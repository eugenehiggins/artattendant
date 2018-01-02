<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Media_Comments extends AC_Column_Media_Comments
	implements ACP_Column_FilteringInterface, ACP_Export_Column {

	public function filtering() {
		return new ACP_Filtering_Model_Media_Comments( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Comments( $this );
	}

}
