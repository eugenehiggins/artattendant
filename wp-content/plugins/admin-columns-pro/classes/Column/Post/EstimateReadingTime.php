<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Post_EstimateReadingTime extends AC_Column_Post_EstimatedReadingTime
	implements ACP_Column_SortingInterface, ACP_Export_Column {

	public function sorting() {
		return new ACP_Sorting_Model_Post_EstimateReadingTime( $this );
	}

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this );
	}

}
