<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_CustomField_TitleById extends ACP_Sorting_Model_CustomField {

	public function get_sorting_vars() {
		$setting = $this->column->get_setting( 'post' );

		if ( ! $setting instanceof AC_Settings_Column_Post ) {
			return array();
		}

		$ids = array();

		foreach ( $this->strategy->get_results() as $id ) {
			$title = false;

			if ( $post_ids = ac_helper()->array->get_integers_from_mixed( $this->column->get_raw_value( $id ) ) ) {

				// sort by first post
				$post_id = $post_ids[0];

				$title = $setting->format( $post_id, false );
			}

			$ids[ $id ] = $title;
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}

}
