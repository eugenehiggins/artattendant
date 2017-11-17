<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACA_ACF_Column $column
 */
class ACA_ACF_Sorting extends ACP_Sorting_Model_Meta {

	public function __construct( ACA_ACF_Column $column ) {
		parent::__construct( $column );
	}

}
