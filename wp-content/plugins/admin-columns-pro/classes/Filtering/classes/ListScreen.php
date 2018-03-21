<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ACP_Filtering_ListScreen {

	/**
	 * @param ACP_Filtering_Model $model
	 *
	 * @return ACP_Filtering_Strategy
	 */
	public function filtering( $model );

}
