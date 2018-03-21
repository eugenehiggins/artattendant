<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_UserCustomField extends ACP_Settings_Column_CustomField {

	protected function get_post_type() {
		return false;
	}

	protected function get_meta_type() {
		return 'user';
	}

}
