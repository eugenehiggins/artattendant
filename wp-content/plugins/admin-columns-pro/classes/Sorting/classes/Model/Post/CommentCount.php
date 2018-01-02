<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Post_CommentCount extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		$ids = array();

		/* @var AC_Settings_Column_CommentCount $setting */
		$setting = $this->column->get_setting( 'comment_count' );

		foreach ( $this->strategy->get_results() as $id ) {
			$ids[ $id ] = $setting->get_comment_count( $id );
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}

}
