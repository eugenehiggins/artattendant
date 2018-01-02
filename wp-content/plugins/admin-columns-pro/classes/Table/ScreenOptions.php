<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Table_ScreenOptions {

	public function __construct() {
		add_filter( 'screen_settings', array( $this, 'add_screen_settings' ) );
		add_action( 'ac/table_scripts', array( $this, 'scripts' ) );
		add_action( 'wp_ajax_acp_update_table_option_overflow', array( $this, 'update_table_option_overflow' ) );
		add_filter( 'ac/table/body_class', array( $this, 'add_horizontal_scrollable_class' ), 10, 2 );
	}

	/**
	 * @return AC_Preferences
	 */
	public function preferences() {
		return new AC_Preferences( 'show_overflow_table' );
	}

	/**
	 * Handle ajax request
	 */
	public function update_table_option_overflow() {
		check_ajax_referer( 'ac-ajax' );

		$list_screen = AC()->get_list_screen( filter_input( INPUT_POST, 'list_screen' ) );

		if ( ! $list_screen ) {
			wp_die();
		}

		$list_screen->set_layout_id( filter_input( INPUT_POST, 'layout' ) );

		$this->preferences()->set( $list_screen->get_storage_key(), 'true' === filter_input( INPUT_POST, 'value' ) );

		exit;
	}

	/**
	 * @param string $html
	 *
	 * @return string
	 */
	public function add_screen_settings( $html ) {
		$list_screen = AC()->table_screen()->get_current_list_screen();

		if ( $list_screen ) {
			$html .= $this->get_overflow_table_setting( $list_screen );
		}

		return $html;
	}

	/**
	 * @param AC_ListScreen $list_screen
	 *
	 * @return bool
	 */
	private function is_overflow_table( $list_screen ) {
		return (bool) $this->preferences()->get( $list_screen->get_storage_key() );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function delete_overflow_preference( $list_screen ) {
		$this->preferences()->delete( $list_screen->get_storage_key() );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 *
	 * @return string
	 */
	private function get_overflow_table_setting( $list_screen ) {
		ob_start();
		?>

		<fieldset class='acp-screen-option-prefs'>
			<legend>Admin Columns</legend>
			<label>
				<input type='checkbox' name='acp_overflow_list_screen_table' id="acp_overflow_list_screen_table" value="yes"<?php checked( $this->is_overflow_table( $list_screen ) ); ?> />
				<?php _e( 'Horizontal Scrolling', 'codepress-admin-columns' ); ?>
			</label>
			<?php

			/**
			 * @since 4.0.12
			 *
			 * @param AC_ListScreen $list_screen
			 */
			do_action( 'ac/screen_options', $list_screen );

			?>
		</fieldset>

		<?php

		return ob_get_clean();
	}

	/**
	 * Load scripts
	 */
	public function scripts() {
		wp_enqueue_style( 'ac-table-screen-option', acp()->get_plugin_url() . 'assets/css/table-screen-options' . AC()->minified() . '.css', array(), ACP()->get_version() );
		wp_enqueue_script( 'ac-table-screen-option', acp()->get_plugin_url() . 'assets/js/table-screen-options' . AC()->minified() . '.js', array(), ACP()->get_version() );
	}

	/**
	 * @param string         $classes
	 * @param AC_TableScreen $table
	 *
	 * @return string
	 */
	public function add_horizontal_scrollable_class( $classes, $table ) {
		if ( $this->is_overflow_table( $table->get_current_list_screen() ) ) {
			$classes .= ' acp-overflow-table';
		}

		return $classes;
	}

}
