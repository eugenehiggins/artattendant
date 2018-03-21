<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ACP_Export_ListScreen {

	/**
	 * @return ACP_Export_Strategy
	 */
	public function export();

}
