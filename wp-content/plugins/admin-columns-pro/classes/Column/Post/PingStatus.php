<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_Post_PingStatus extends AC_Column_Post_PingStatus
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface, ACP_Column_EditingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model_Post_Field( $this );
		$model->set_field( 'ping_status' );

		return $model;
	}

	public function editing() {
		return new ACP_Editing_Model_Post_PingStatus( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_PingStatus( $this );
	}

}
