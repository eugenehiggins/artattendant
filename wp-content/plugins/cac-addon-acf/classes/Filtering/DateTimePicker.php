<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_DateTimePicker extends ACA_ACF_Filtering_DatePicker {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_date_format( 'Y-m-d H:i:s' );
	}

}
