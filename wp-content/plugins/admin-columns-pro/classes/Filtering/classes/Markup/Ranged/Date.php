<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Markup_Ranged_Date extends ACP_Filtering_Markup_Ranged {

	public function __construct( $name, $label, $min, $max ) {
		parent::__construct( $name, $label, $min, $max );

		$this->get_min()->set_attribute( 'placeholder', __( 'Start date', 'codepress-admin-columns' ) );
		$this->get_max()->set_attribute( 'placeholder', __( 'End date', 'codepress-admin-columns' ) );
	}

}
