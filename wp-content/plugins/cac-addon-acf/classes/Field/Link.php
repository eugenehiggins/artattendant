<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Link extends ACA_ACF_Field {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->column->set_serialized( true );
	}

	public function get_value( $id ) {
		$link = parent::get_value( $id );

		if ( empty( $link ) ) {
			return $link;
		}

		$label = $link['title'];

		if ( ! $label ) {
			$label = str_replace( array( 'http://', 'https://' ), '', $link['url'] );
		}

		if ( '_blank' === $link['target'] ) {
			$label .= '<span class="dashicons dashicons-external" style="font-size: 1em;"></span>';
		}

		return ac_helper()->html->link( $link['url'], $label );
	}

	// Pro

	public function editing() {
		return new ACA_ACF_Editing_Link( $this->column );
	}

	public function filtering() {
		return new ACA_ACF_Filtering_Link( $this->column );
	}
}
