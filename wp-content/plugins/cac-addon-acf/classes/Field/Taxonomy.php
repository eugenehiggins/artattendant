<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Taxonomy extends ACA_ACF_Field {

	public function __construct( $column ) {
		parent::__construct( $column );

		// Checkbox and Multiselect are stored serialized
		$column->set_serialized( in_array( $this->get( 'field_type' ), array( 'checkbox', 'multi_select' ) ) );
	}

	public function get_value( $id ) {
		$term_ids = parent::get_value( $id );

		$values = array();

		foreach ( ac_helper()->taxonomy->get_terms_by_ids( $term_ids, $this->get( 'taxonomy' ) ) as $term ) {
			$values[] = ac_helper()->html->link( get_edit_term_link( $term->term_id, $term->taxonomy ), $term->name );
		}

		return implode( ', ', $values );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Taxonomy( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_Taxonomy( $this->column );
	}

	public function sorting() {
		if ( $this->column->get_field()->get( 'multiple' ) ) {
			return new ACP_Sorting_Model_Value( $this->column );
		}

		return new ACA_ACF_Sorting( $this->column );
	}

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this->column );
	}

}
