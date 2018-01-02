<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_Range extends ACA_ACF_Field {

	public function editing() {
		return new ACA_ACF_Editing_Range( $this->column );
	}

	public function sorting() {
		$model = new ACP_Sorting_Model_Meta( $this->column );
		$model->set_data_type( 'numeric' );

		return $model;
	}

	public function filtering() {
		return new ACA_ACF_Filtering_Number( $this->column );
	}

}
