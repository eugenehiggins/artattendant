<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_DatePicker extends ACP_Filtering_Model_MetaDate {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_date_format( 'Ymd' );
	}

}
