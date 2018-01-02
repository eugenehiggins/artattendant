<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.1
 */
class ACP_Column_Taxonomy_Posts extends AC_Column
	implements ACP_Export_Column {

	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'posts' );
	}

	public function export() {
		return new ACP_Export_Model_Term_Posts( $this );
	}

}
