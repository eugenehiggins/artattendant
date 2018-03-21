<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Inline Edit Addon plugin class
 *
 * @since 1.0
 */
class ACP_Editing_Addon extends AC_Addon {

	/**
	 * @var ACP_Editing_TableScreen
	 */
	private $table_screen;

	/**
	 * @since 4.0
	 */
	function __construct() {
		AC()->autoloader()->register_prefix( 'ACP_Editing', $this->get_plugin_dir() . 'classes' );

		// Settings screen
		add_action( 'ac/column/settings', array( $this, 'register_column_settings' ) );
		add_action( 'ac/settings/general', array( $this, 'register_general_settings' ) );

		// Table screen
		$this->table_screen = new ACP_Editing_TableScreen();
	}

	protected function get_file() {
		return __FILE__;
	}

	public function table_screen() {
		return $this->table_screen;
	}

	/**
	 * @since 4.0
	 */
	public function get_version() {
		return ACP()->get_version();
	}

	public function helper() {
		return new ACP_Editing_Helper();
	}

	/**
	 * @param AC_Column $column
	 *
	 * @return ACP_Editing_Model|false
	 */
	public function get_editing_model( $column ) {
		if ( ! $column instanceof ACP_Column_EditingInterface ) {
			return false;
		}

		$list_screen = $column->get_list_screen();

		if ( ! $list_screen instanceof ACP_Editing_ListScreen ) {
			return false;
		}

		$model = $column->editing();

		return $model->set_strategy( $list_screen->editing( $model ) );
	}

	/**
	 * @since 3.1.2
	 *
	 * @param AC_Admin_Page_Settings $settings
	 */
	public function register_general_settings( $settings ) {
		$settings->single_checkbox( array(
			'name'         => 'custom_field_editable',
			'label'        => __( 'Enable inline editing for Custom Fields. Default is <code>off</code>', 'codepress-admin-columns' ),
			'instructions' => sprintf(
				'<p>%s</p><p>%s</p>',
				__( 'Inline edit will display all the raw values in an editable text field.', 'codepress-admin-columns' ),
				sprintf(
					__( "Please read <a href='%s'>our documentation</a> if you plan to use these fields.", 'codepress-admin-columns' ),
					ac_get_site_utm_url( 'documentation/faq/enable-inline-editing-custom-fields/', 'general-settings' )
				)
			),
		) );
	}

	/**
	 * Register setting for editing
	 *
	 * @param AC_Column|ACP_Column_EditingInterface $column
	 */
	public function register_column_settings( $column ) {
		if ( $model = $this->get_editing_model( $column ) ) {
			$model->register_settings();
		}
	}

}
