<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_User_Email extends AC_Column_User_Email
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Export_Column {

	public function editing() {
		return new ACP_Editing_Model_User_Email( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_User_Email( $this );
	}

	public function export() {
		return new ACP_Export_Model_User_Email( $this );
	}

}
