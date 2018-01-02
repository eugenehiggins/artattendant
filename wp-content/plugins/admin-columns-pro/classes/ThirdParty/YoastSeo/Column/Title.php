<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Column_Title extends ACP_ThirdParty_YoastSeo_Column
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Export_Column {

	public function __construct() {
		parent::__construct();

		$this->set_type( 'wpseo-title' );
	}

	public function editing() {
		return new ACP_ThirdParty_YoastSeo_Editing_Title( $this );
	}

	public function filtering() {
		return new ACP_ThirdParty_YoastSeo_Filtering_Title( $this );
	}

	public function export() {
		return new ACP_ThirdParty_YoastSeo_Export_Title( $this );
	}

}
