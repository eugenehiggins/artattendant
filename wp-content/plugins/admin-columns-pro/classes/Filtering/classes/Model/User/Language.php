<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACP_Column_User_Language $column
 */
class ACP_Filtering_Model_User_Language extends ACP_Filtering_Model_Meta {

	public function __construct( ACP_Column_User_Language $column ) {
		parent::__construct( $column );
	}

	public function get_filtering_data() {
		$options = $this->column->get_language_options( false );

		$options = array( 'cpac_empty' => _x( 'Site Default', 'default site language' ) ) + $options;

		return array(
			'options' => $options,
			'order'   => false,
		);
	}

}
