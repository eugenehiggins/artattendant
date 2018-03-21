<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ListScreen_User extends AC_ListScreen_User
	implements ACP_Sorting_ListScreen, ACP_Editing_ListScreen, ACP_Filtering_ListScreen, ACP_Export_ListScreen {

	public function sorting( $model ) {
		return new ACP_Sorting_Strategy_User( $model );
	}

	public function editing( $model ) {
		return new ACP_Editing_Strategy_User( $model );
	}

	public function filtering( $model ) {
		return new ACP_Filtering_Strategy_User( $model );
	}

	public function export() {
		return new ACP_Export_Strategy_User( $this );
	}

	protected function register_column_types() {
		parent::register_column_types();

		$this->register_column_type( new ACP_Column_CustomField );
		$this->register_column_type( new ACP_Column_Actions );

		$this->register_column_types_from_dir( ACP()->get_plugin_dir() . 'classes/Column/User', ACP()->get_prefix() );
	}

}
