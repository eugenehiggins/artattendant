<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ACP_Sorting_ListScreen {

	/**
	 * @param ACP_Sorting_Model $model
	 *
	 * @return ACP_Sorting_Strategy
	 */
	public function sorting( $model );

}
