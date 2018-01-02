<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ACP_Export_Column {

	/**
	 * @return ACP_Export_Model
	 */
	public function export();

}
