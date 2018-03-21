<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Strategy {

	/**
	 * @return AC_Column
	 */
	public function get_column() {
		return $this->get_model()->get_column();
	}

	/**
	 * @return ACP_Model
	 */
	public abstract function get_model();

}
