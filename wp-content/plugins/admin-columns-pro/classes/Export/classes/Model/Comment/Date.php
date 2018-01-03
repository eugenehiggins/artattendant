<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date (default column) exportability model
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_Comment_Date extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_comment( $id )->comment_date;
	}

}
