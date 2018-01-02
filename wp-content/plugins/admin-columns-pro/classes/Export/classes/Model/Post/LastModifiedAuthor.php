<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Last modified author column exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Post_LastModifiedAuthor extends ACP_Export_Model_Value {

	/**
	 * @param AC_Column_Post_LastModifiedAuthor $column
	 */
	public function __construct( AC_Column_Post_LastModifiedAuthor $column ) {
		parent::__construct( $column );
	}

}
