<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @version 1.0
 * @since   1.0
 */
class ACP_License_Manager {

	/**
	 * Option key to store licence data
	 *
	 * @since 1.1
	 */
	const OPTION_KEY = 'cpupdate_cac-pro';

	/**
	 * @since 1.0
	 *
	 * @param array $args [api_url, option_key, file, name, version]
	 */
	public function __construct() {
		// Hook into WP update process
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );

		// Seen when the user clicks "view details" on the plugin listing page
		add_action( 'install_plugins_pre_plugin-information', array( $this, 'plugin_changelog' ), 8 );

		// Activate licence on plugin install
		register_activation_hook( ACP_FILE, array( $this, 'auto_activate_licence' ) );

		// Add UI
		add_filter( 'ac/settings/groups', array( $this, 'settings_group' ) );
		add_action( 'ac/settings/group/addons', array( $this, 'display' ) );

		// Multisite
		add_filter( 'acp/network_settings/groups', array( $this, 'settings_group' ), 10, 2 );
		add_action( 'acp/network_settings/group/addons', array( $this, 'display' ) );

		// licence Requests
		add_action( 'admin_init', array( $this, 'handle_request' ) );

		// Hook into the plugin install process, inject addon download url
		add_action( 'plugins_api', array( $this, 'inject_addon_install_resource' ), 10, 3 );

		// Do check before installing add-on
		add_filter( 'ac/addons/install_request/maybe_error', array( $this, 'maybe_install_error' ), 10, 2 );

		// add scripts, after settings page is set.
		add_action( 'admin_init', array( $this, 'register_admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'scripts' ), 20 );
		add_action( 'network_admin_menu', array( $this, 'network_scripts' ), 20 );

		// check subscription renewal status on a schedule
		add_action( 'shutdown', array( $this, 'do_scheduled_renewal_check' ) );
	}

	/**
	 * Update expiration date & renewal discount
	 *
	 * @since 3.4.3
	 */
	public function update_license_details() {
		$license = new ACP_License;

		$api = new ACP_License_API();
		$response = $api->request( 'licensedetails', array(
			'license_key' => $license->get_key(),
		) );

		if ( isset( $response->expiry_date ) ) {
			$license->set_expiry_date( $response->expiry_date );
		}

		if ( isset( $response->renewal_discount ) ) {
			$license->set_renewal_discount( $response->renewal_discount );
		}

		$license->save();
	}

	/**
	 * Purge all plugin transients
	 */
	public function purge_plugin_transients() {
		delete_site_transient( 'update_plugins' );
		delete_site_transient( 'admin-columns-pro_acppluginupdate' );

		// Integrations
		foreach ( AC()->addons()->get_addons() as $addon ) {
			delete_site_transient( $addon->get_slug() . '_acppluginupdate' );
		}
	}

	/**
	 * HTML changelog
	 *
	 * @since 1.0
	 * @return void
	 */
	public function plugin_changelog() {
		$basename = false;

		$plugin = filter_input( INPUT_GET, 'plugin' );

		// Pro
		if ( $plugin === dirname( ACP()->get_basename() ) ) {
			$basename = $plugin;
		}

		// Addons
		if ( AC()->addons()->get_addon( $plugin ) ) {
			$basename = $plugin;
		}

		if ( $basename ) {
			$api = new ACP_License_API();
			$response = $api->request( 'pluginchangelog', array(
				'plugin_name' => $basename,
			), 'html' );

			if ( is_wp_error( $response ) ) {
				$response = $response->get_error_message();
			}

			echo $response;
			exit;
		}
	}

	/**
	 * @since 1.1
	 * @return mixed
	 */
	public function get_plugin_install_data( $plugin_name, $clear_cache = false ) {
		if ( $clear_cache ) {
			delete_site_transient( self::OPTION_KEY . '_plugininstall' );
		}

		$plugin_install = get_site_transient( self::OPTION_KEY . '_plugininstall' );

		// no cache, get data
		if ( ! $plugin_install ) {
			$license = new ACP_License();

			$api = new ACP_License_API();
			$plugin_install = $api->request( 'plugininstall', array(
				'licence_key' => $license->get_key(),
				'plugin_name' => $plugin_name,
			) );

			// flatten wp_error object for transient storage
			if ( is_wp_error( $plugin_install ) ) {
				$plugin_install = $this->flatten_wp_error( $plugin_install );
			}
		}

		/*
			We need to set the transient even when there's an error,
			otherwise we'll end up making API requests over and over again
			and slowing things down big time.
		*/
		set_site_transient( self::OPTION_KEY . '_plugininstall', $plugin_install, 60 * 15 ); // 15 min.

		// Maybe create wp_error object
		$plugin_install = $this->maybe_unflatten_wp_error( $plugin_install );

		return $plugin_install;
	}

	/**
	 * @since 1.1
	 * @return
	 */
	public function get_plugin_update_data( $plugin_name, $version ) {
		$plugin_update = get_site_transient( $plugin_name . '_acppluginupdate' );

		// no cache, get data
		if ( ! $plugin_update ) {
			$license = new ACP_License();

			$api = new ACP_License_API();
			$plugin_update = $api->request( 'pluginupdate', array(
				'licence_key' => $license->get_key(),
				'plugin_name' => $plugin_name,
				'version'     => $version,
			) );

			// flatten wp_error object for transient storage
			if ( is_wp_error( $plugin_update ) ) {
				$plugin_update = $this->flatten_wp_error( $plugin_update );
			}
		}

		/*
			We need to set the transient even when there's an error,
			otherwise we'll end up making API requests over and over again
			and slowing things down big time.
		*/
		set_site_transient( $plugin_name . '_acppluginupdate', $plugin_update, HOUR_IN_SECONDS );

		$plugin_update = $this->maybe_unflatten_wp_error( $plugin_update );

		return $plugin_update;
	}

	/**
	 * @since 1.1
	 * @return
	 */
	public function get_plugin_details() {
		$plugin_details = get_site_transient( self::OPTION_KEY . '_plugindetails' );

		// no cache, get data
		if ( ! $plugin_details ) {
			$api = new ACP_License_API();
			$plugin_details = $api->request( 'plugindetails', array(
				'plugin_name' => ACP()->get_basename(),
			) );

			// flatten wp_error object for transient storage
			if ( is_wp_error( $plugin_details ) ) {
				$plugin_details = $this->flatten_wp_error( $plugin_details );
			}
		}

		/*
			We need to set the transient even when there's an error,
			otherwise we'll end up making API requests over and over again
			and slowing things down big time.
		*/
		set_site_transient( self::OPTION_KEY . '_plugindetails', $plugin_details, DAY_IN_SECONDS );

		$plugin_details = $this->maybe_unflatten_wp_error( $plugin_details );

		return $plugin_details;
	}

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * @uses api_request()
	 *
	 * @param object $transient Update array build by Wordpress.
	 *
	 * @return stdClass Modified update array with custom plugin data.
	 */
	public function update_check( $transient ) {
		$license = new ACP_License();

		if ( ! $license->is_active() || $license->is_expired() ) {
			return $transient;
		};

		// Addons
		if ( $addons = $this->get_addons_update_data() ) {
			foreach ( $addons as $addon ) {
				$plugin_data = $this->get_plugin_update_data( dirname( $addon['plugin'] ), $addon['version'] );
				if ( ! is_wp_error( $plugin_data ) && ! empty( $plugin_data->new_version ) && version_compare( $plugin_data->new_version, $addon['version'] ) > 0 ) {
					$transient->response[ $addon['plugin'] ] = $plugin_data;
				}
			}
		}

		// Main plugin
		$plugin_data = $this->get_plugin_update_data( dirname( ACP()->get_basename() ), ACP()->get_version() );
		if ( ! is_wp_error( $plugin_data ) && ! empty( $plugin_data->new_version ) && version_compare( $plugin_data->new_version, ACP()->get_version() ) > 0 ) {
			$transient->response[ ACP()->get_basename() ] = $plugin_data;
		}

		return $transient;
	}

	/**
	 * @since 1.0
	 * @return void
	 */
	public function auto_activate_licence() {
		$license = new ACP_License();

		if ( $license->is_active() && $license->get_key() ) {
			$license->set_status( 'active' )
			        ->save();
		}
	}

	/**
	 * Get the plugin's header info from the installed plugins list.
	 *
	 * @since 1.1
	 */
	public function get_plugin_info( $field ) {
		if ( ! is_admin() ) {
			return false;
		}

		$plugins = get_plugins();

		if ( ! isset( $plugins[ ACP()->get_basename() ][ $field ] ) ) {
			return false;
		}

		return $plugins[ ACP()->get_basename() ][ $field ];
	}

	/**
	 * Check if the license for this plugin is managed per site or network
	 *
	 * @since 3.6
	 * @return boolean
	 */
	private function is_network_managed_license() {
		return is_multisite() && is_plugin_active_for_network( ACP()->get_basename() );
	}

	public function get_masked_licence_key() {
		$license = new ACP_License();

		return str_repeat( '*', 28 ) . substr( $license->get_key(), -4 );
	}

	/**
	 * Flatten WP_Error object for storage in transient
	 *
	 * @param object $wp_error WP_Error object
	 *
	 * @return $error Error Object
	 */
	public function flatten_wp_error( $wp_error ) {
		$error = false;

		if ( is_wp_error( $wp_error ) ) {
			$error = (object) array(
				'error'   => 1,
				'time'    => time(),
				'code'    => $wp_error->get_error_code(),
				'message' => $wp_error->get_error_message(),
			);
		}

		return $error;
	}

	/**
	 * Maybe unflatten error
	 *
	 * @param mixed $maybe_error stdClass
	 *
	 * @return $wp_error WP_Error Object
	 */
	public function maybe_unflatten_wp_error( $maybe_error ) {
		if ( isset( $maybe_error->error ) && isset( $maybe_error->message ) ) {
			$maybe_error = new WP_Error( $maybe_error->code, $maybe_error->message );
		}

		return $maybe_error;
	}

	/**
	 * @since 3.4.3
	 */
	public function do_scheduled_renewal_check() {
		if ( get_transient( '_cpac_renewal_check' ) ) {
			return;
		}

		$this->update_license_details();

		set_transient( '_cpac_renewal_check', 1, WEEK_IN_SECONDS );
	}

	/**
	 * @since 3.1.2
	 */
	public function scripts() {
		if ( AC()->admin()->is_current_page( 'settings' ) ) {
			add_action( "admin_print_scripts-" . AC()->admin()->get_hook_suffix(), array( $this, 'admin_scripts' ) );
		}
	}

	public function network_scripts() {
		if ( ac_is_pro_active() ) {
			add_action( "admin_print_scripts-" . ACP()->network_admin()->get_hook_suffix(), array( $this, 'admin_scripts' ) );
		}
	}

	public function register_admin_scripts() {
		wp_register_style( 'acp-license-manager', ACP()->get_plugin_url() . "assets/css/license-manager.css", array(), ACP()->get_version() );
		wp_register_script( 'acp-license-manager', ACP()->get_plugin_url() . "assets/js/license-manager.js", array( 'jquery' ), ACP()->get_version() );
	}

	/**
	 * @since 3.1.2
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'acp-license-manager' );
		wp_enqueue_style( 'acp-license-manager' );
	}

	/**
	 * @since 1.0
	 */
	public function maybe_install_error( $error, $plugin_name ) {
		$license = new ACP_License();

		if ( ! $license->is_active() ) {
			$error = sprintf( __( "Licence not active. Enter your licence key on <a href='%s'>the settings page</a>.", 'codepress-admin-columns' ), $this->get_license_page_url() );
		}

		$install_data = $this->get_plugin_install_data( $plugin_name, $clear_cache = true ); // get remote add-on info

		if ( is_wp_error( $install_data ) ) {
			$error = $install_data->get_error_message();
		}

		return $error;
	}

	/**
	 * Get addons data for the update process
	 *
	 * @since 1.0.0
	 */
	public function get_addons_update_data() {
		$addons_update_data = array();

		foreach ( AC()->addons()->get_addons() as $addon ) {
			$basename = $addon->get_basename();

			if ( ! $basename ) {
				continue;
			}

			$addons_update_data[] = array(
				'plugin'  => $basename,
				'version' => $addon->get_version(),
			);
		}

		return $addons_update_data;
	}

	/**
	 * Add addons to install process, not the update process.
	 *
	 * @since 1.0
	 */
	public function inject_addon_install_resource( $result, $action, $args ) {
		if ( 'plugin_information' != $action || empty( $args->slug ) ) {
			return $result;
		}

		if ( ! AC()->addons()->get_addon( $args->slug ) ) {
			return $result;
		}

		$install_data = $this->get_plugin_install_data( $args->slug, true );

		if ( ! $install_data ) {
			return $result;
		}

		return $install_data;
	}

	/**
	 * @return string|WP_Error Success message
	 */
	private function activate_license() {
		$licence_key = sanitize_text_field( filter_input( INPUT_POST, 'license' ) );

		if ( empty( $licence_key ) ) {
			return new WP_Error( 'empty-license', __( 'Empty licence.', 'codepress-admin-columns' ) );
		}

		$license = new ACP_License();
		$license->delete();

		$api = new ACP_License_API();
		$response = $api->request( 'activation', array(
			'licence_key' => $licence_key,
			'site_url'    => site_url(),
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'error', $response->get_error_message() );
		}

		if ( ! isset( $response->activated ) ) {
			return new WP_Error( 'error', __( 'Wrong response from API.', 'codepress-admin-columns' ) );
		}

		if ( isset( $response->expiry_date ) ) {
			$license->set_expiry_date( $response->expiry_date );
		}

		if ( isset( $response->renewal_discount ) ) {
			$license->set_renewal_discount( $response->renewal_discount );
		}

		$license->set_key( $licence_key )
		        ->set_status( 'active' )
		        ->save();

		$this->purge_plugin_transients();

		return $response->message;
	}

	/**
	 * @return string|WP_Error Success message
	 */
	private function deactivate_license() {
		$license = new ACP_License();

		$api = new ACP_License_API();
		$response = $api->request( 'deactivation', array(
			'licence_key' => $license->get_key(),
			'site_url'    => site_url(),
		) );

		$license->delete();
		$this->purge_plugin_transients();

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'error', __( 'Wrong response from API.', 'codepress-admin-columns' ) . ' ' . $response->get_error_message() );
		}

		if ( ! isset( $response->deactivated ) ) {
			return new WP_Error( 'error', __( 'Wrong response from API.', 'codepress-admin-columns' ) );
		}

		return $response->message;
	}

	/**
	 * Handle requests for license activation and deactivation
	 *
	 * @since 1.0
	 */
	public function handle_request() {
		if ( ! current_user_can( 'manage_admin_columns' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_acnonce' ), 'acp-license' ) ) {
			return;
		}

		switch ( filter_input( INPUT_POST, 'action' ) ) {
			case 'activate' :
				$result = $this->activate_license();

				if ( is_wp_error( $result ) ) {
					AC()->notice( $result->get_error_message(), 'error' );
				} else {
					AC()->notice( $result );
				}

				break;
			case 'deactivate' :
				$result = $this->deactivate_license();

				if ( is_wp_error( $result ) ) {
					AC()->notice( $result->get_error_message(), 'error' );
				} else {
					AC()->notice( $result );
				}

				break;
		}
	}

	/**
	 * Add settings group to Admin Columns settings page
	 *
	 * @since 1.0
	 *
	 * @param array $groups Add group to ACP settings screen
	 *
	 * @return array Settings group for ACP
	 */
	public function settings_group( $groups ) {
		if ( isset( $groups['addons'] ) ) {
			return $groups;
		}

		$groups['addons'] = array(
			'title'       => __( 'Updates', 'codepress-admin-columns' ),
			'description' => __( 'Enter your licence code to receive automatic updates.', 'codepress-admin-columns' ),
		);

		return $groups;
	}

	/**
	 * Get the URL to manage your license based on network or site managed license
	 *
	 * @return string
	 */
	public function get_license_page_url() {
		$url = AC()->admin()->get_link( 'settings' );

		if ( $this->is_network_managed_license() ) {
			$url = ACP()->get_network_settings_url();
		}

		return $url;
	}

	/**
	 * Display licence field
	 *
	 * @since 1.0
	 * @return void
	 */
	public function display() {
		// When the plugin is network activated, the license is managed globally
		if ( $this->is_network_managed_license() && ! is_network_admin() ) {
			?>
			<p>
				<?php
				$page = __( 'network settings page', 'codepress-admin-columns' );

				if ( current_user_can( 'manage_network_options' ) ) {
					$page = ac_helper()->html->link( network_admin_url( 'settings.php?page=codepress-admin-columns' ), $page );
				}

				printf( __( 'The license can be managed on the %s.', 'codepress-admin-columns' ), $page );
				?>
			</p>
			<?php
		} else {
			/**
			 * Hook is used for hiding the license form from the settings page
			 *
			 * @param bool false Show license input fields
			 */
			$show_license = apply_filters( 'acp/display_licence', true );

			if ( ! $show_license ) {
				return;
			}

			$license = new ACP_License();

			?>

			<form id="licence_activation" action="" method="post">
				<?php wp_nonce_field( 'acp-license', '_acnonce' ); ?>

				<?php if ( $license->is_active() ) : ?>
					<input type="hidden" name="action" value="deactivate">

					<p>
						<span class="dashicons dashicons-yes"></span>
						<?php _e( 'Automatic updates are enabled.', 'codepress-admin-columns' ); ?>
						<input type="submit" class="button" value="<?php _e( 'Deactivate licence', 'codepress-admin-columns' ); ?>">
					</p>
				<?php else : ?>
					<input type="hidden" name="action" value="activate">
					<input type="password" value="<?php echo esc_attr( $license->get_key() ); ?>" name="license" size="30" placeholder="<?php echo esc_attr( __( 'Enter your licence code', 'codepress-admin-columns' ) ); ?>">
					<input type="submit" class="button" value="<?php _e( 'Update licence', 'codepress-admin-columns' ); ?>">
					<p class="description">
						<?php printf( __( 'You can find your license key on your %s.', 'codepress-admin-columns' ), '<a href="' . ac_get_site_utm_url( 'my-account', 'license-activation' ) . '" target="_blank">' . __( 'account page', 'codepress-admin-columns' ) . '</a>' ); ?>
					</p>
				<?php endif; ?>
			</form>

			<?php
		}
	}

}
