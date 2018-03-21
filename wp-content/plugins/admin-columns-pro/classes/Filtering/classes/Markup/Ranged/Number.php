<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Markup_Ranged_Number extends ACP_Filtering_Markup_Ranged {

	public function __construct( $name, $label, $min, $max ) {
		parent::__construct( $name, $label, $min, $max );

		$this->set_input_type( 'number' );
		$this->get_min()->set_attribute( 'placeholder', __( 'Min', 'codepress-admin-columns' ) );
		$this->get_max()->set_attribute( 'placeholder', __( 'Max', 'codepress-admin-columns' ) );
	}

}
