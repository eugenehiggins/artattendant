<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show a notice when plugin dependencies are not met
 *
 * @version 1.3
 */
final class ACA_ACF_Dependencies {

	/**
	 * Missing dependencies strings
	 *
	 * @var string[]
	 */
	private $missing = array();

	/**
	 * Basename of this plugin
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * @param $basename string
	 */
	public function __construct( $basename ) {
		$this->basename = $basename;

		add_action( 'after_plugin_row_' . $this->basename, array( $this, 'display_notice' ), 5 );
		add_action( 'admin_head', array( $this, 'display_notice_css' ) );
	}

	/**
	 * Get list of missing dependencies
	 *
	 * @return bool
	 */
	public function has_missing() {
		return ! empty( $this->missing );
	}

	/**
	 * Add missing dependency
	 *
	 * @param string $label
	 * @param string $url
	 * @param string $version
	 */
	public function add_missing( $label, $url = null, $version = null ) {
		$label = esc_html( $label );

		if ( $url ) {
			$label = sprintf( '<a href="%s">%s</a>', esc_url( $url ), $label );
		}

		if ( $version ) {
			$label .= ' (' . sprintf( __( 'version %s or later', 'codepress-admin-columns' ), esc_html( $version ) ) . ')';
		}

		$this->missing[] = $label;
	}

	public function is_missing_acp( $version ) {
		if ( function_exists( 'acp_is_version_gte' ) && acp_is_version_gte( $version ) ) {
			return false;
		}

		$this->add_missing( 'Admin Column Pro', 'https://www.admincolumns.com', $version );

		return true;
	}

	/**
	 * URL that performs a search in the WordPress repository
	 *
	 * @param string $keywords
	 *
	 * @return string
	 */
	public function get_search_url( $keywords ) {
		$url = add_query_arg( array(
			'tab' => 'search',
			's'   => $keywords,
		), admin_url( 'plugin-install.php' ) );

		return $url;
	}

	/**
	 * Show a warning when dependencies are not met
	 */
	public function display_notice() {
		if ( ! $this->has_missing() ) {
			return;
		}

		$last = end( $this->missing );
		$missing = str_replace( ', ' . $last, __( ' and ', 'codepress-admin-columns' ) . $last, implode( ', ', $this->missing ) );

		?>

		<tr class="plugin-update-tr active">
			<td colspan="3" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-error notice-alt">
					<p><?php printf( __( 'This add-on requires %s to be installed and activated.', 'codepress-admin-columns' ), $missing ); ?></p>
				</div>
			</td>
		</tr>

		<?php
	}

	public function display_notice_css() {
		if ( ! $this->has_missing() ) {
			return;
		}

		?>

		<style>
			.plugins tr[data-plugin='<?php echo $this->basename; ?>'] th,
			.plugins tr[data-plugin='<?php echo $this->basename; ?>'] td {
				box-shadow: none;
			}
		</style>

		<?php
	}

}
