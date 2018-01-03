<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting an attachment's URL based on its ID
 *
 * @since 4.1
 */
class ACP_Export_Model_AttachmentURLFromAttachmentId extends ACP_Export_Model {

	public function get_value( $id ) {
		return wp_get_attachment_url( $this->get_column()->get_raw_value( $id ) );
	}

}
