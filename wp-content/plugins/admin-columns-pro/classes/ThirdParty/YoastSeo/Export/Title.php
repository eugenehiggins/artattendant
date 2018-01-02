<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Export_Title extends ACP_Export_Model {

	public function get_value( $id ) {
		$title = get_post_meta( $id, '_yoast_wpseo_title', true );

		// If no specific
		if ( ! $title ) {
			$title = get_the_title( $id );
		}

		return $title;
	}

}
