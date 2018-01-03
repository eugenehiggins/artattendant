<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Count exportability model
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_Post_CommentCount extends ACP_Export_Model {

	public function get_value( $id ) {
		$setting = $this->column->get_setting( 'comment_count' );
		$value = false;

		if ( $setting && $setting instanceof AC_Settings_Column_CommentCount ) {
			$value = $setting->get_comment_count( $id );
		}

		return $value;
	}

}
