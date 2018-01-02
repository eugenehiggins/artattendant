<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model, which can be attached as an extension to a column. It handles custom
 * behaviour a column should exhibit when being exported
 *
 * @since 1.0
 */
abstract class ACP_Export_Model {

	/**
	 * @var AC_Column
	 */
	protected $column;

	public function __construct( AC_Column $column ) {
		$this->column = $column;
	}

	/**
	 * @return AC_Column
	 */
	public function get_column() {
		return $this->column;
	}

	/**
	 * Retrieve the value to be exported by the column for a specific item
	 *
	 * @since 1.0
	 *
	 * @param int $id Item ID
	 */
	abstract public function get_value( $id );

	/**
	 * @since 1.0
	 * @see   ACP_Model::is_active()
	 */
	public function is_active() {
		return true;
	}

}
