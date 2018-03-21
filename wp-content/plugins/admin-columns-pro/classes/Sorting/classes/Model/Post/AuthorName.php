<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACP_Column_Post_AuthorName $column
 */
class ACP_Sorting_Model_Post_AuthorName extends ACP_Sorting_Model_Post_Field {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_field( 'post_author' );
	}

	protected function format( $value ) {
		/** @var ACP_Settings_Column_User $setting */
		$setting = $this->column->get_setting( 'user' );

		return $setting->get_user_name( $value );
	}

}
