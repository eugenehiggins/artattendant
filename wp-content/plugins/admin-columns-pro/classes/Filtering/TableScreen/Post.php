<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_TableScreen_Post extends ACP_Filtering_TableScreen {

	public function __construct( array $models ) {
		parent::__construct( $models );

		add_action( 'restrict_manage_posts', array( $this, 'render_markup' ), 11 );
	}

	public function hide_default_dropdowns() {
		parent::hide_default_dropdowns();

		foreach ( $this->models as $model ) {
			if ( $model instanceof ACP_Filtering_Model_Post_Date && $model->is_active() && 'monthly' === $model->get_filter_format() ) {
				add_filter( 'disable_months_dropdown', '__return_true' );
			}
		}
	}

}
