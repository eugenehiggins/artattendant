<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NativeTaxonomy extends AC_Column
	implements ACP_Column_FilteringInterface, ACP_Column_EditingInterface, ACP_Column_SortingInterface, ACP_Export_Column {

	public function __construct() {
		$this->set_original( true );
	}

	public function get_taxonomy() {
		return str_replace( 'taxonomy-', '', $this->get_type() );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Taxonomy( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Taxonomy( $this );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Post_Taxonomy( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Taxonomy( $this );
	}

}
