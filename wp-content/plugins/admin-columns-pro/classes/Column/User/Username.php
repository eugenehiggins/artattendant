<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.1
 */
class ACP_Column_User_Username extends AC_Column_User_Username
	implements ACP_Export_Column {

	public function export() {
		return new ACP_Export_Model_User_Username( $this );
	}

}
