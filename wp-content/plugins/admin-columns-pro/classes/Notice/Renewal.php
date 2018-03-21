<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Notice_Renewal {

	/**
	 * @param string $basename
	 */
	public function __construct( $basename ) {
		// Add notifications to the plugin screen
		add_action( 'after_plugin_row_' . $basename, array( $this, 'display_plugin_row_notices' ), 11 );

		// Adds notice to update message that a licence is needed
		add_action( 'in_plugin_update_message-' . $basename, array( $this, 'need_license_message' ), 10, 2 );

		// General expiry notice notices
		add_action( 'current_screen', array( $this, 'display_license_expiry_notices' ) );

		add_action( 'wp_ajax_ac_hide_license_expiry_notice', array( $this, 'ajax_hide_license_expiry_notice' ) );

		// check license has been renewed
		add_action( 'wp_ajax_ac_check_license_renewed', array( $this, 'ajax_check_license_renewed' ) );
	}

	/**
	 * Only visible on plugin screen, table screen or AC settings screen
	 *
	 * @return bool
	 */
	private function is_notice_screen() {
		global $pagenow;

		return in_array( $pagenow, array( 'plugins.php', 'edit.php' ) ) || AC()->admin()->is_admin_screen();
	}

	/**
	 * Display notice for license expiry
	 *
	 * @since 3.4.3
	 */
	public function display_license_expiry_notices() {
		if ( ! AC()->user_can_manage_admin_columns() ) {
			return;
		}

		if ( ! $this->is_notice_screen() ) {
			return;
		}

		if ( AC()->suppress_site_wide_notices() ) {
			return;
		}

		/**
		 * Filter the visibility of the Admin Columns renewal notice
		 *
		 * @since 3.4.3
		 *
		 * @param bool $hide Whether to hide the renewal notice. Defaults to false.
		 */
		if ( apply_filters( 'acp/hide_renewal_notice', false ) ) {
			return;
		}

		$hide_license_timeout = get_user_meta( get_current_user_id(), 'cpac_hide_license_notice_timeout', true );
		$hide_license_phase = get_user_meta( get_current_user_id(), 'cpac_hide_license_notice_phase', true );

		if ( $this->is_license_expiry_notice_hideable() ) {
			// Notice was blocked the final time
			if ( $hide_license_phase == 'completed' ) {
				return;
			}

			// Notice was blocked, and timeout hasn't been reached yet
			if ( time() < $hide_license_timeout ) {
				return;
			}
		}

		// First license expiry threshold passed
		$phases = $this->get_hide_license_notice_thresholds();

		$license = new ACP_License();

		if ( $license->get_days_to_expiry() > $phases[ count( $phases ) - 1 ] ) {
			return;
		}

		// Show a renewal message if the license needs renewal
		$message = $this->get_renewal_message();

		if ( $message ) {

			if ( $this->is_license_expiry_notice_hideable() ) : ?>
				<button type="button" class="notice-dismiss" data-hide-notice="license-check"><span class="screen-reader-text">Dismiss this notice.</span></button>
			<?php endif; ?>
			<p>
				<?php echo $message; ?>
				<?php $this->check_license_link(); ?>
			</p>
			<div class="clear"></div>
			<?php
			$message = ob_get_clean();

			AC()->notice( $message, 'error warning', false );
		}
	}

	/**
	 * Whether the license expiry notice should be displayed, regardless of the license timeout
	 *
	 * @since 3.4.3
	 */
	private function is_license_expiry_notice_hideable() {
		return ! AC()->admin()->is_current_page( 'settings' );
	}

	/**
	 * Get the HTML for checking a license
	 *
	 * @since 3.4.3
	 */
	private function check_license_link() {
		wp_enqueue_script( 'acp-license-manager' );
		wp_enqueue_style( 'acp-license-manager' );
		?>

		<a href="#" class="ac-check-license"><?php _e( 'Check my license', 'codepress-admin-columns' ); ?>.</a>

		<?php
	}

	/**
	 * @return string
	 */
	private function get_check_license_link() {
		ob_start();

		$this->check_license_link();

		return ob_get_clean();
	}

	/**
	 * Shows a message below the plugin on the plugins page
	 *
	 * @since 1.0.3
	 */
	public function display_plugin_row_notices() {
		$message = false;

		$license = new ACP_License();

		if ( $license->is_active() ) {
			if ( $message = $this->get_renewal_message() ) {
				$message .= $this->get_check_license_link();
			}
		} else {
			$plugin_details = ACP()->license_manager()->get_plugin_details();

			if ( isset( $plugin_details->version ) && version_compare( ACP()->get_version(), $plugin_details->version, '>=' ) ) {
				$message = $this->get_need_license_message();
			}
		}

		if ( $message ) {
			$notice = new AC_Notice_Plugin( ACP()->get_basename() );
			$notice->set_message( $message )
			       ->display_notice();
		}
	}

	private function get_hide_license_notice_thresholds() {
		return array( 0, 7, 21 );
	}

	/**
	 * Handle an AJAX request for hiding license expiry notices
	 *
	 * @since 3.4.3
	 */
	public function ajax_hide_license_expiry_notice() {
		$hide_license_phase = get_user_meta( get_current_user_id(), 'cpac_hide_license_notice_phase', true );

		$license = new ACP_License();

		if ( $hide_license_phase != 'completed' ) {
			$expiry_date = $license->get_expiry_date();
			$days = $license->get_days_to_expiry();

			$phase = 0;
			$phases = $this->get_hide_license_notice_thresholds();
			foreach ( $phases as $phase => $threshold ) {
				if ( $days <= $threshold ) {
					break;
				}
			}

			$new_phase = $phase - 1;

			if ( $new_phase == -1 ) {
				update_user_meta( get_current_user_id(), 'cpac_hide_license_notice_timeout', 0 );
				update_user_meta( get_current_user_id(), 'cpac_hide_license_notice_phase', 'completed' );
			} else {
				// Expiry date minus x days
				update_user_meta( get_current_user_id(), 'cpac_hide_license_notice_timeout', $expiry_date - $phases[ $new_phase ] * DAY_IN_SECONDS );
				update_user_meta( get_current_user_id(), 'cpac_hide_license_notice_phase', $new_phase );
			}
		}

		wp_send_json_success();
	}

	/**
	 * @since 3.4.3
	 */
	public function ajax_check_license_renewed() {
		// update renewal date
		ACP()->license_manager()->update_license_details();

		$license = new ACP_License();

		// check is license is renewed
		$phases = $this->get_hide_license_notice_thresholds();
		$is_renewed = ( $license->get_days_to_expiry() <= $phases[ count( $phases ) - 1 ] ) ? false : true;

		// create message based on status
		$message = __( 'Your license was successfully renewed!', 'codepress-admin-columns' );
		$type = 'success';

		if ( ! $is_renewed ) {
			$message = $this->get_renewal_message() . ' <strong>' . __( 'Your license has not been renewed yet.', 'codepress-admin-columns' ) . '</strong>';
			$type = 'error';
		}

		$notice = new AC_Notice_Plugin( ACP()->get_basename() );
		$notice->set_message( $message )
		       ->set_type( $type )
		       ->display_notice();

		exit;
	}

	/**
	 * Message to add to update message when you have not activated your license
	 *
	 * @return string
	 */
	private function get_need_license_message() {
		$message = sprintf(
			__( "To enable updates, please enter your license key on the <a href='%s'>Settings</a> page. If you don't have a licence key, please see <a href='%s' target='_blank'>details & pricing</a>.", 'codepress_admin_columns' ),
			AC()->admin()->get_link( 'settings' ),
			ac_get_site_utm_url( 'pricing-purchase', 'plugins' )
		);

		$sanitized_message = wp_kses( $message, array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
				'class'  => array(),
			),
		) );

		return $sanitized_message;
	}

	/**
	 * Message to add to update message when you have not activated your license
	 *
	 * @param  array  $plugin_data
	 * @param  object $r
	 *
	 */
	public function need_license_message( $plugin_data, $r ) {
		if ( empty( $r->package ) ) {
			echo '<br>' . $this->get_need_license_message();
		}
	}

	/**
	 * Get renewal message
	 *
	 * @since 3.4.3
	 */
	private function get_renewal_message() {
		$license = new ACP_License();

		$message = false;
		$days_to_expiry = $license->get_days_to_expiry();

		// renewal date has been set?
		if ( $days_to_expiry !== false ) {
			if ( $days_to_expiry > 0 ) {

				if ( $days_to_expiry < 28 ) { // for plugin page
					$days = sprintf( _n( '1 day', '%s days', $days_to_expiry, 'codepress-admin-columns' ), $days_to_expiry );
					if ( $discount = $license->get_renewal_discount() ) {
						$message = sprintf(
							__( "Your Admin Columns Pro license will expire in %s. %s now and get a %d%% discount!", 'codepress-admin-columns' ),
							'<strong>' . $days . '</strong>',
							ac_helper()->html->link( ac_get_site_utm_url( 'my-account', 'renewal' ), __( 'Renew your license', 'codepress-admin-columns' ) ),
							$discount
						);
					} else {
						$message = sprintf(
							__( "Your Admin Columns Pro license will expire in %s. %s now and get a discount!", 'codepress-admin-columns' ),
							'<strong>' . $days . '</strong>',
							ac_helper()->html->link( ac_get_site_utm_url( 'my-account', 'renewal' ), __( 'Renew your license', 'codepress-admin-columns' ) )
						);
					}
				}
			} else {
				$message = sprintf(
					__( 'Your Admin Columns Pro license has expired on %s! Renew your license now by going to your %s.', 'codepress-admin-columns' ),
					date_i18n( get_option( 'date_format' ), $license->get_expiry_date() ),
					'<a href="' . ac_get_site_utm_url( 'my-account', 'renewal' ) . '">' . __( 'My Account page', 'codepress-admin-columns' ) . '</a>'
				);
			}
		}

		return $message;
	}
}
