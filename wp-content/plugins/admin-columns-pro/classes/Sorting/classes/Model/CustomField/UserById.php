<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_CustomField_UserById extends ACP_Sorting_Model_CustomField {

	public function get_sorting_vars() {
		$setting = $this->column->get_setting( 'user' );

		if ( ! $setting instanceof AC_Settings_Column_User ) {
			return array();
		}

		$ids = array();

		foreach ( $this->strategy->get_results() as $id ) {
			$name = false;

			if ( $user_ids = ac_helper()->array->get_integers_from_mixed( $this->column->get_raw_value( $id ) ) ) {

				// sort by first user
				$name = $setting->get_user_name( $user_ids[0] );
			}

			$ids[ $id ] = $name;
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}

}
