<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACP_Column_User_Language $column
 */
class ACP_Editing_Model_User_Language extends ACP_Editing_Model_Meta {

	public function __construct( ACP_Column_User_Language $column ) {
		parent::__construct( $column );
	}

	public function get_view_settings() {
		return array(
			'type'    => 'select',
			'options' => $this->column->get_language_options(),
		);
	}

}
