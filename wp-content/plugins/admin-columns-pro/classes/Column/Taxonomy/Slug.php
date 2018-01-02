<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Taxonomy_Slug extends AC_Column
	implements ACP_Column_EditingInterface, ACP_Export_Column {

	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'slug' );
	}

	public function editing() {
		return new ACP_Editing_Model_Taxonomy_Slug( $this );
	}

	public function export() {
		return new ACP_Export_Model_Term_Slug( $this );
	}

}
