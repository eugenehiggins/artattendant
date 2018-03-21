<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Filtering_Addon extends AC_Addon {

	public function __construct() {
		AC()->autoloader()->register_prefix( 'ACP_Filtering', $this->get_plugin_dir() . 'classes' );

		add_action( 'ac/column/settings', array( $this, 'settings' ) );
		add_action( 'ac/settings/scripts', array( $this, 'settings_scripts' ) );
		add_action( 'ac/table/list_screen', array( $this, 'table_screen' ) );
		add_action( 'wp_ajax_acp_update_filtering_cache', array( $this, 'ajax_update_dropdown_cache' ) );
	}

	public function ajax_update_dropdown_cache() {
		check_ajax_referer( 'ac-ajax' );

		$input = (object) filter_input_array( INPUT_POST, array(
			'list_screen' => FILTER_SANITIZE_STRING,
			'layout'      => FILTER_SANITIZE_STRING,
		) );

		$list_screen = AC()->get_list_screen( $input->list_screen );

		if ( ! $list_screen ) {
			wp_die();
		}

		$list_screen->set_layout_id( $input->layout );

		$table_screen = $this->table_screen( $list_screen );

		if ( ! $table_screen ) {
			wp_die();
		}

		wp_send_json_success( $table_screen->update_dropdown_cache() );
	}

	/**
	 * @return string
	 */
	protected function get_file() {
		return __FILE__;
	}

	/**
	 * @since 4.0
	 */
	public function get_version() {
		return ACP()->get_version();
	}

	/**
	 * @return ACP_Filtering_Helper
	 */
	public function helper() {
		return new ACP_Filtering_Helper();
	}

	/**
	 * @param AC_Column $column
	 *
	 * @return ACP_Filtering_Model|false
	 */
	public function get_filtering_model( $column ) {
		if ( ! $column instanceof ACP_Column_FilteringInterface ) {
			return false;
		}

		$list_screen = $column->get_list_screen();

		if ( ! $list_screen instanceof ACP_Filtering_ListScreen ) {
			return false;
		}

		$model = $column->filtering();
		$strategy = $list_screen->filtering( $model );

		if ( $model->is_active() && false !== $model->get_filter_value() ) {
			$strategy->handle_request();
		}

		$model->set_strategy( $strategy );

		return $model;
	}

	/**
	 * @param AC_ListScreen $list_screen
	 *
	 * @return array|false
	 */
	private function get_models( AC_ListScreen $list_screen ) {
		if ( ! $list_screen instanceof ACP_Filtering_ListScreen ) {
			return false;
		}

		$models = array();

		foreach ( $list_screen->get_columns() as $column ) {
			$model = $this->get_filtering_model( $column );

			if ( $model ) {
				$models[] = $model;
			}
		}

		return $models;
	}

	/**
	 * @param AC_ListScreen $list_screen
	 *
	 * @return ACP_Filtering_TableScreen|false
	 */
	public function table_screen( AC_ListScreen $list_screen ) {
		$models = $this->get_models( $list_screen );

		if ( ! $models ) {
			return false;
		}

		switch ( true ) {
			case $list_screen instanceof ACP_ListScreen_MSUser :
				return new ACP_Filtering_TableScreen_MSUser( $models );

			case $list_screen instanceof ACP_ListScreen_Post :
			case $list_screen instanceof ACP_ListScreen_Media :
				return new ACP_Filtering_TableScreen_Post( $models );

			case $list_screen instanceof ACP_ListScreen_User :
				return new ACP_Filtering_TableScreen_User( $models );

			case $list_screen instanceof ACP_ListScreen_Comment :
				return new ACP_Filtering_TableScreen_Comment( $models );
		}

		return false;
	}

	public function settings_scripts() {
		wp_enqueue_script( 'acp-filtering-settings', $this->get_plugin_url() . 'assets/js/settings.js', array( 'jquery' ), $this->get_version() );
	}

	/**
	 * Register field settings for filtering
	 *
	 * @param AC_Column $column
	 */
	public function settings( $column ) {
		if ( $model = $this->get_filtering_model( $column ) ) {
			$model->register_settings();
		}
	}

}
