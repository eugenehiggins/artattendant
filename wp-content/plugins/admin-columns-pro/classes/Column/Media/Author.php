<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Media_Author extends AC_Column_Media_Author
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Export_Column {

	public function filtering() {
		return new ACP_Filtering_Model_Media_Author( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Author( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Author( $this );
	}

}
