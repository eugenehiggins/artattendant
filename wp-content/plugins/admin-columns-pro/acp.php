<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'api.php';

/**
 * The Admin Columns Pro plugin class
 *
 * @since 1.0
 */
final class ACP extends AC_Plugin {

	/**
	 * License manager class instance
	 *
	 * @since 1.0
	 * @var ACP_License_Manager
	 */
	private $license_manager;

	/**
	 * Editing instance
	 *
	 * @since 4.0
	 * @var ACP_Editing_Addon
	 */
	private $editing;

	/**
	 * Filtering instance
	 *
	 * @since 4.0
	 * @var ACP_Filtering_Addon
	 */
	private $filtering;

	/**
	 * Sorting instance
	 *
	 * @since 4.0
	 * @var ACP_Sorting_Addon
	 */
	private $sorting;

	/**
	 * @var ACP_NetworkAdmin
	 */
	private $network_admin;

	/**
	 * @var ACP_Table_ScreenOptions
	 */
	private $screen_options;

	/**
	 * @since 3.8
	 */
	private static $instance = null;

	/**
	 * @since 3.8
	 * @return ACP
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * ACP constructor.
	 */
	private function __construct() {
		AC()->autoloader()->register_prefix( $this->get_prefix(), $this->get_plugin_dir() . 'classes/' );

		$this->editing = new ACP_Editing_Addon();
		$this->sorting = new ACP_Sorting_Addon();
		$this->filtering = new ACP_Filtering_Addon();
		$this->network_admin = new ACP_NetworkAdmin();
		$this->screen_options = new ACP_Table_ScreenOptions();

		ACP_Export_Addon::instance();

		new ACP_ThirdParty_Addon();
		new ACP_LayoutScreen_Columns();
		new ACP_LayoutScreen_Table();

		new ACP_Notice_Renewal( $this->get_basename() );

		add_action( 'init', array( $this, 'localize' ) );

		add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 1, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'add_settings_link' ), 1, 2 );
		add_action( 'ac/settings/after_menu', array( $this, 'display_beta_version_message' ) );

		add_filter( 'ac/show_banner', '__return_false' );
		add_action( 'wp_loaded', array( $this, 'init_license_manager' ) );

		add_action( 'ac/list_screen_groups', array( $this, 'register_list_screen_groups' ) );

		add_action( 'ac/list_screens', array( $this, 'register_list_screens' ) );
		add_action( 'ac/column_types', array( $this, 'register_columns' ) );

		add_action( 'ac/admin_pages', array( $this, 'register_pages' ) );

		add_action( 'ac/table_scripts', array( $this, 'table_scripts' ), 10, 1 );

		// Updater
		add_action( 'init', array( $this, 'install' ) );
	}

	/**
	 * @return string
	 */
	protected function get_file() {
		return ACP_FILE;
	}

	/**
	 * @return string
	 */
	protected function get_version_key() {
		return 'acp_version';
	}

	/**
	 * @return string
	 */
	public function get_prefix() {
		return 'ACP_';
	}

	/**
	 * @since 4.0
	 */
	public function is_beta() {
		$version = $this->get_version();

		return false !== strpos( $version, 'beta' );
	}

	/**
	 * Handle localization
	 *
	 * @since 1.0.1
	 * @uses  load_plugin_textdomain()
	 */
	public function localize() {
		load_plugin_textdomain( 'codepress-admin-columns', false, dirname( $this->get_basename() ) . '/languages/' );
	}

	/**
	 * @param AC_Admin_Pages $pages
	 */
	public function register_pages( $pages ) {
		$pages->register_page( new ACP_Admin_Page_ExportImport() );
	}

	/**
	 * Init License Manager
	 */
	public function init_license_manager() {
		$this->license_manager = new ACP_License_Manager();
	}

	/**
	 * @return ACP_License_Manager
	 */
	public function license_manager() {
		return $this->license_manager;
	}

	/**
	 * @since 4.0
	 */
	public function editing() {
		return $this->editing;
	}

	/**
	 * @since 4.0
	 */
	public function filtering() {
		return $this->filtering;
	}

	/**
	 * @since 4.0
	 */
	public function sorting() {
		return $this->sorting;
	}

	/**
	 * @since 4.0
	 */
	public function layouts( AC_ListScreen $list_screen ) {
		return new ACP_Layouts( $list_screen );
	}

	/**
	 * @since 4.0
	 */
	public function network_admin() {
		return $this->network_admin;
	}

	/**
	 * @since 4.0.12
	 */
	public function screen_options() {
		return $this->screen_options;
	}

	/**
	 * @since 1.0
	 * @see   filter:plugin_action_links
	 */
	public function add_settings_link( $links, $file ) {
		if ( $file === $this->get_basename() ) {
			$url = AC()->admin()->get_link( 'settings' );

			if ( is_network_admin() ) {
				$url = $this->network_admin->get_link();
			}

			array_unshift( $links, ac_helper()->html->link( $url, __( 'Settings' ) ) );
		}

		return $links;
	}

	/**
	 * @return string
	 */
	public function get_network_settings_url() {
		return $this->network_admin()->get_link();
	}

	/**
	 * Get a list of taxonomies supported by Admin Columns
	 *
	 * @since 1.0
	 *
	 * @return array List of taxonomies
	 */
	private function get_taxonomies() {
		$taxonomies = get_taxonomies( array( 'show_ui' => true ) );

		if ( isset( $taxonomies['post_format'] ) ) {
			unset( $taxonomies['post_format'] );
		}

		if ( isset( $taxonomies['link_category'] ) && ! get_option( 'link_manager_enabled' ) ) {
			unset( $taxonomies['link_category'] );
		}

		/**
		 * Filter the post types for which Admin Columns is active
		 *
		 * @since 2.0
		 *
		 * @param array $post_types List of active post type names
		 */
		return (array) apply_filters( 'acp/taxonomies', $taxonomies );
	}

	/**
	 * @param AC_Groups $groups
	 */
	public function register_list_screen_groups( $groups ) {
		$groups->register_group( 'taxonomy', __( 'Taxonomy' ), 15 );
		$groups->register_group( 'network', __( 'Network' ), 5 );
	}

	/**
	 * @since 4.0
	 */
	public function register_list_screens() {
		$list_screens = array();

		// Post types
		foreach ( AC()->get_post_types() as $post_type ) {
			$list_screens[] = new ACP_ListScreen_Post( $post_type );
		}

		$list_screens[] = new ACP_ListScreen_Media();
		$list_screens[] = new ACP_ListScreen_Comment();

		foreach ( $this->get_taxonomies() as $taxonomy ) {
			$list_screens[] = new ACP_ListScreen_Taxonomy( $taxonomy );
		}

		$list_screens[] = new ACP_ListScreen_User();

		if ( is_multisite() ) {

			// Settings UI
			if ( AC()->admin_columns_screen()->is_current_screen() ) {

				// Main site
				if ( is_main_site() ) {
					$list_screens[] = new ACP_ListScreen_MSUser();
					$list_screens[] = new ACP_ListScreen_MSSite();
				}
			} // Table screen
			else {
				$list_screens[] = new ACP_ListScreen_MSUser();
				$list_screens[] = new ACP_ListScreen_MSSite();
			}
		}

		foreach ( $list_screens as $list_screen ) {
			AC()->register_list_screen( $list_screen );
		}
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function register_columns( AC_ListScreen $list_screen ) {
		$this->register_column_native_taxonomies( $list_screen );

		/**
		 * @deprecated 4.1 Use 'ac/column_types'
		 */
		do_action( 'acp/column_types', $list_screen );
	}

	/**
	 * Register Taxonomy columns that are set by WordPress. These native columns are registered
	 * by setting 'show_admin_column' to 'true' as an argument in register_taxonomy();
	 * Only supports Post Types.
	 *
	 * @see register_taxonomy
	 */
	private function register_column_native_taxonomies( AC_ListScreen $list_screen ) {
		if ( ! $list_screen instanceof AC_ListScreenPost ) {
			return;
		}

		$taxonomies = get_taxonomies(
			array(
				'show_ui'           => 1,
				'show_admin_column' => 1,
				'_builtin'          => 0,
			),
			'object'
		);

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $list_screen->get_post_type(), $taxonomy->object_type ) ) {
				$column = new ACP_Column_NativeTaxonomy();
				$column->set_type( 'taxonomy-' . $taxonomy->name );

				$list_screen->register_column_type( $column );
			}
		}
	}

	/**
	 * Beta message
	 */
	public function display_beta_version_message() {
		if ( AC()->suppress_site_wide_notices() ) {
			return;
		}
		if ( ACP()->is_beta() ) : ?>
			<div class="notice notice-warning">
				<p>
					<?php printf( __( "You are using a beta version of %s.", 'codepress-admin-columns' ), 'Admin Columns Pro' ); ?>
					<?php printf( __( "Please provide us with any feedback (bugs, UI or improvements) on the beta by creating a new topic on %s.", 'codepress-admin-columns' ), ac_helper()->html->link( ac_get_site_utm_url( 'forums/forum/beta-feedback/', 'beta-notice' ), __( 'our forum', 'codepress-admin-columns' ), array( 'target' => '_blank' ) ) ); ?>
				</p>
			</div>
			<?php
		endif;
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function table_scripts() {
		wp_enqueue_style( 'acp-table', ACP()->get_plugin_url() . "assets/css/table.css", array(), AC()->get_version() );
	}

}

function ACP() {
	return ACP::instance();
}

// Backwards compatible
function acp_pro() {
	return ACP();
}

ACP();
