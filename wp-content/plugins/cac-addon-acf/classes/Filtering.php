<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACA_ACF_Column $column
 */
class ACA_ACF_Filtering extends ACP_Filtering_Model_Meta {

	public function __construct( ACA_ACF_Column $column ) {
		parent::__construct( $column );
	}

}
