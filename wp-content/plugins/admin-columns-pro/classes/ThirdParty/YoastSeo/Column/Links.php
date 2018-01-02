<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Column_Links extends ACP_ThirdParty_YoastSeo_Column
	implements ACP_Export_Column {

	public function __construct() {
		parent::__construct();

		$this->set_type( 'wpseo-links' );
	}

	public function export() {
		return new ACP_Export_Model_Disabled( $this );
	}

}
