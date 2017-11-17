<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_File extends ACA_ACF_Field {

	public function get_value( $id ) {
		$attachment_id = parent::get_value( $id );

		$value = false;

		if ( $attachment_id ) {
			if ( $attachment = get_attached_file( $attachment_id ) ) {
				$value = ac_helper()->html->link( wp_get_attachment_url( $attachment_id ), esc_html( basename( $attachment ) ), array( 'target' => '_blank' ) );
			}
			else {
				$value = '<em>' . __( 'Invalid attachment', 'codepress-admin-columns' ) . '</em>';
			}
		}

		return $value;
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_File( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_File( $this->column );
	}

}
