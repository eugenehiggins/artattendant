<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_PageLink extends ACA_ACF_Field_PostObject {

	public function editing() {
		return new ACA_ACF_Editing_PageLink( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Value( $this->column );
	}

}
