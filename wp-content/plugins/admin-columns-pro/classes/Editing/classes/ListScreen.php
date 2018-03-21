<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ACP_Editing_ListScreen {

	/**
	 * @param ACP_Editing_Model $model
	 *
	 * @return ACP_Editing_Strategy
	 */
	public function editing( $model );

}
