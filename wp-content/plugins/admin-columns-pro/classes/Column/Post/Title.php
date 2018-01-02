<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_Title extends AC_Column_Post_Title
	implements ACP_Column_EditingInterface, ACP_Export_Column {

	public function editing() {
		return new ACP_Editing_Model_Post_Title( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Title( $this );
	}

}
