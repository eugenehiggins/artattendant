<?php
/*
Plugin Name: Admin Columns Pro
Version: 4.2.6
Description: Customize columns on the administration screens for post(types), users and other content. Filter and sort content, and edit posts directly from the posts overview. All via an intuitive, easy-to-use drag-and-drop interface.
Author: Admin Columns
Author URI: https://www.admincolumns.com
Plugin URI: https://www.admincolumns.com
Text Domain: codepress-admin-columns
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACP_FILE', __FILE__ );

// Only run plugin in the admin interface
if ( ! is_admin() ) {
	return false;
}

/**
 * Loads Admin Columns and Admin Columns Pro
 *
 * @since 3.0.6
 */
final class ACP_Full {

	public function __construct() {
		// Can not be auto loaded this early
		require_once plugin_dir_path( __FILE__ ) . 'classes/PluginActions.php';

		// Only load Admin Columns if it hasn't been loaded already (in which case it is automatically deactivated by maybe_deactivate_admincolumns())
		if ( ! $this->maybe_deactivate_admincolumns() ) {
			require_once dirname( __FILE__ ) . '/admin-columns/codepress-admin-columns.php';
			require_once dirname( __FILE__ ) . '/acp.php';

			// Non compatible add-ons will be deactivated
			$this->deactivate_incompatible_addons();

			// Set capabilities
			register_activation_hook( __FILE__, array( AC(), 'set_capabilities' ) );
		}
	}

	/**
	 * Disable the Admin Columns base plugin if it is active
	 *
	 * @since 3.0
	 *
	 * @return bool Whether the base plugin was deactivated
	 */
	public function maybe_deactivate_admincolumns() {
		$deactivated = false;

		$actions = new ACP_PluginActions( 'codepress-admin-columns/codepress-admin-columns.php' );

		if ( $actions->deactivate() ) {
			$deactivated = true;
		}

		$actions = new ACP_PluginActions( 'cac-addon-pro/cac-addon-pro.php' );

		if ( $actions->deactivate() ) {
			$deactivated = true;
		}

		return $deactivated;
	}

	/**
	 * Non compatible add-ons will be deactivated
	 */
	public function deactivate_incompatible_addons() {
		$addons = array(
			'ac-addon-acf/ac-addon-acf.php'                         => '2.2.2',
			'ac-addon-buddypress/ac-addon-buddypress.php'           => '1.2',
			'ac-addon-events-calendar/ac-addon-events-calendar.php' => '1.1',
			'ac-addon-ninjaforms/ac-addon-ninjaforms.php'           => '1.1',
			'ac-addon-pods/ac-addon-pods.php'                       => '1.1',
			'ac-addon-types/ac-addon-types.php'                     => '1.2',
			'ac-addon-woocommerce/ac-addon-woocommerce.php'         => '3.0.3',

			// Deprecated
			'cac-addon-acf/cac-addon-acf.php'                       => '2.2.2',
			'cac-addon-woocommerce/cac-addon-woocommerce.php'       => '3.0.3',
		);

		foreach ( $addons as $file => $version ) {
			$this->deactivate_incompatible_plugin( $file, $version );
		}
	}

	/**
	 * @param string $file
	 * @param string $required_version
	 */
	private function deactivate_incompatible_plugin( $file, $version ) {
		$plugin = new ACP_PluginActions( $file );

		$plugin->set_required_version( $version );

		if ( ! $plugin->is_compatible() ) {
			$plugin->deactivate();
			$plugin->notice();
		}
	}

}

new ACP_Full();