<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ACP_Export_TableScreenOptions {

	public function __construct() {
		add_action( 'ac/screen_options', array( $this, 'get_show_export_button_setting' ) );
		add_action( 'ac/table_scripts', array( $this, 'scripts' ) );
		add_action( 'wp_ajax_acp_export_show_export_button', array( $this, 'update_table_option_show_export_button' ) );
		add_filter( 'ac/table/body_class', array( $this, 'add_hide_export_button_class' ), 10, 2 );
	}

	public function preferences() {
		return new AC_Preferences( 'show_export_button' );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 *
	 * @return bool
	 */
	private function get_export_button_setting( $list_screen ) {
		return $this->preferences()->get( $list_screen->get_key() );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 * @param bool          $value
	 */
	private function set_export_button_setting( $list_screen, $value ) {
		$this->preferences()->set( $list_screen->get_key(), (bool) $value );
	}

	public function update_table_option_show_export_button() {
		check_ajax_referer( 'ac-ajax' );

		$list_screen = AC()->get_list_screen( filter_input( INPUT_POST, 'list_screen' ) );

		if ( ! $list_screen ) {
			wp_die();
		}

		$this->set_export_button_setting( $list_screen, 'true' === filter_input( INPUT_POST, 'value' ) );
		exit;
	}

	/**
	 * Load scripts
	 */
	public function scripts() {
		wp_enqueue_script( 'acp-export-table-screen-options', ac_addon_export()->get_plugin_url() . 'assets/js/table-screen-options.js', array(), ac_addon_export()->get_version() );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function get_show_export_button_setting( $list_screen ) {
		?>

		<label>
			<input type='checkbox' name='acp_export_show_export_button' id="acp_export_show_export_button" value="1" <?php checked( $this->get_export_button_setting( $list_screen ) ); ?> />
			<?php _e( 'Show Export Button', 'codepress-admin-columns' ); ?>
		</label>

		<?php
	}

	/**
	 * @param string         $classes
	 * @param AC_TableScreen $table
	 *
	 * @return string
	 */
	public function add_hide_export_button_class( $classes, $table ) {
		if ( ! $this->get_export_button_setting( $table->get_current_list_screen() ) ) {
			$classes .= ' ac-hide-export-button';
		}

		return $classes;
	}

}
