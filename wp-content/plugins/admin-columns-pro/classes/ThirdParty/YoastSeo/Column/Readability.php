<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Column_Readability extends ACP_ThirdParty_YoastSeo_Column
	implements ACP_Export_Column {

	public function __construct() {
		parent::__construct();

		$this->set_type( 'wpseo-score-readability' );
	}

	public function export() {
		return new ACP_ThirdParty_YoastSeo_Export_Readability( $this );
	}

}
