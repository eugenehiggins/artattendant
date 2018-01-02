<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Export_FocusKW extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_post_meta( $id, '_yoast_wpseo_focuskw', true );
	}

}
