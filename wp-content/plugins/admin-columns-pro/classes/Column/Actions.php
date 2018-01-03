<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.1
 */
class ACP_Column_Actions extends AC_Column_Actions
	implements ACP_Export_Column {

	public function export() {
		return new ACP_Export_Model_Disabled( $this );
	}

}
