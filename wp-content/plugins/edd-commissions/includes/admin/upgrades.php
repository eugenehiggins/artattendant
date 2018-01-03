<?php
/**
*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Shows upgrade notices
 *
 * @since       2.8
 * @return      void
 */
function eddc_upgrade_notices() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( ! empty( $_GET['page'] ) && 'edd-upgrades' == $_GET['page'] ) {
		return;
	}

	$version = get_option( 'eddc_version' );

	if ( ! $version || version_compare( $version, '2.8', '<' ) ) {
		printf(
			'<div class="updated"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the commission records, click %shere%s to start the upgrade.', 'eddc' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_commission_statuses' ) ) . '">',
			'</a>'
		);
	}

	if ( ! edd_has_upgrade_completed( 'migrate_commissions' ) ) {


		// Check to see if we have commissions in the Database
		$results         = $wpdb->get_row( "SELECT count(ID) as has_commissions FROM $wpdb->posts WHERE post_type = 'edd_commission' LIMIT 0, 1" );
		$has_commissions = ! empty( $results->has_commissions ) ? true : false;

		if ( ! $has_commissions ) {
			edd_set_upgrade_complete( 'migrate_commissions' );
			edd_set_upgrade_complete( 'remove_legacy_commissions' );
		} else {
			printf(
				'<div class="updated">' .
				'<p>' .
				__( 'Easy Digital Downloads - Commissions needs to upgrade the commission records database, click <a href="%s">here</a> to start the upgrade. <a href="#" onClick="jQuery(this).parent().next(\'p\').slideToggle()">Learn more about this upgrade</a>.', 'eddc' ) .
				'</p>' .
				'<p style="display: none;">' .
				__( '<strong>About this upgrade:</strong><br />This is a <strong><em>mandatory</em></strong> update that will migrate all commission records and their meta data to a new custom database table. This upgrade should provider better performance and scalability.', 'eddc' ) .
				'<br /><br />' .
				__( '<strong>Please backup your database before starting this upgrade.</strong> This upgrade routine will be making changes to the database that are not reversible.', 'eddc' ) .
				'<br /><br />' .
				__( '<strong>Advanced User?</strong><br />This upgrade can also be run via WPCLI with the following command:<br /><code>wp edd-commissions migrate_commissions</code>', 'eddc' ) .
				'</p>' .
				'</div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=commissions_migration' ) )
			);
		}
	}

	if ( edd_has_upgrade_completed( 'migrate_commissions' ) && ! edd_has_upgrade_completed( 'remove_legacy_commissions' ) ) {
		printf(
			'<div class="updated">' .
			'<p>' .
			__( 'Easy Digital Downloads - Commissions has <strong>finished migrating commission</strong> records, next step is to <a href="%s">remove the legacy data</a>. <a href="#" onClick="jQuery(this).parent().next(\'p\').slideToggle()">Learn more about this process</a>.', 'eddc' ) .
			'</p>' .
			'<p style="display: none;">' .
			__( '<strong>Removing legacy data:</strong><br />All commissions records have been migrated to their own custom table. Now all old data needs to be removed.', 'eddc' ) .
			'<br /><br />' .
			__( '<strong>If you have not already, back up your database</strong> as this upgrade routine will be making changes to the database that are not reversible.', 'eddc' ) .
			'</p>' .
			'</div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=remove_legacy_commissions' ) )
		);
	}
}
add_action( 'admin_notices', 'eddc_upgrade_notices' );


/**
 * Upgrade all commissions with user ID meta
 *
 * Prior to 1.3 it wasn't possible to query commissions by user ID (dumb)
 *
 * @since       1.3
 * @return      void
 */
function eddc_upgrade_user_ids() {
	if ( get_option( 'eddc_upgraded_user_ids' ) ) {
		return; // don't perform the upgrade if we have already done it
	}

	$args = array(
		'post_type'      => 'edd_commission',
		'posts_per_page' => -1
	);

	$commissions = get_posts( $args );

	if ( $commissions ) {
		foreach ( $commissions as $commission ) {
			$info = maybe_unserialize( get_post_meta( $commission->ID, '_edd_commission_info', true ) );

			update_post_meta( $commission->ID, '_user_id', $info['user_id'] );
		}
		add_option( 'eddc_upgraded_user_ids', '1' );
	}
}
add_action( 'admin_init', 'eddc_upgrade_user_ids' );


/**
 * Upgrades all commission records to use a taxonomy for tracking the status of the commission
 *
 * @since 2.8
 * @return void
 */
function eddc_upgrade_commission_statuses() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	define( 'EDDC_DOING_UPGRADES', true );

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		set_time_limit( 0 );
	}

	$step = isset( $_GET['step'] ) ? absint( $_GET['step'] )  : 1;

	$args = array(
		'posts_per_page' => 20,
		'paged'          => $step,
		'status'         => 'any',
		'order'          => 'ASC',
		'post_type'      => 'edd_commission',
		'fields'         => 'ids'
	);

	$commissions = get_posts( $args );

	if ( $commissions ) {
		// Commissions found so upgrade them
		foreach ( $commissions as $commission_id ) {
			$status = get_post_meta( $commission_id, '_commission_status', true );

			if ( 'paid' !== $status ) {
				$status = 'unpaid';
			}

			eddc_set_commission_status( $commission_id, $status );
		}

		$step++;

		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_commission_statuses',
			'step'        => $step
		), admin_url( 'index.php' ) );

		wp_safe_redirect( $redirect );
		exit;
	} else {
		// No more commissions found, finish up
		update_option( 'eddc_version', EDD_COMMISSIONS_VERSION );

		// No more commissions found, finish up
		wp_redirect( admin_url() ); exit;
	}
}
add_action( 'edd_upgrade_commission_statuses', 'eddc_upgrade_commission_statuses' );

/**
 * Migrates all commissions and their meta to the new custom table
 *
 * @since 3.4
 * @return void
 */
function eddc_commissions_migration() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	define( 'EDDC_DOING_UPGRADES', true );

	ignore_user_abort( true );
	set_time_limit( 0 );

	$step   = isset( $_GET['step'] )   ? absint( $_GET['step'] )   : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 10;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_commissions FROM $wpdb->posts WHERE post_type = 'edd_commission'";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_commissions;
	}

	if ( 1 === $step ) {
		$commissions_db      = edd_commissions()->commissions_db;
		if ( ! $commissions_db->table_exists( $commissions_db->table_name ) ) {
			@$commissions_db->create_table();
		}

		$commissions_meta_db = edd_commissions()->commission_meta_db;
		if ( ! $commissions_meta_db->table_exists( $commissions_meta_db->table_name ) ) {
			@$commissions_meta_db->create_table();
		}
	}

	$commissions = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $wpdb->posts WHERE post_type = 'edd_commission' ORDER BY ID ASC LIMIT %d,%d;",
			$offset,
			$number
		)
	);

	if ( ! empty( $commissions ) ) {

		// Commissions found so migrate them
		foreach ( $commissions as $old_commission ) {

			// Prevent an already migrated item from being migrated.
			$migrated = $wpdb->get_var( "SELECT meta_id FROM {$wpdb->prefix}edd_commissionmeta WHERE meta_key = '_edd_commission_legacy_id' AND meta_value = $old_commission->ID" );
			if ( ! empty( $migrated ) ) {
				continue;
			}

			$meta_items = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", $old_commission->ID ) );
			$post_meta  = array();
			foreach ( $meta_items as $meta_item ) {
				$post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
			}

			$download        = new EDD_Download( $post_meta['_download_id'] );
			$commission_info = isset( $post_meta['_edd_commission_info'] ) ? $post_meta['_edd_commission_info'] : array();
			if ( empty( $commission_info ) ) {
				continue; // We got some bad records, just move on
			}

			$commission_price_id = false;
			if ( ! empty( $post_meta['_edd_commission_download_variation'] ) ) {
				$prices = $download->get_prices();
				foreach ( $prices as $price_id => $price ) {
					if ( $price['name'] === $post_meta['_edd_commission_download_variation'] ) {
						$commission_price_id = $price_id;
					}
				}
			}

			$cart_index = 0;
			$payment    = false;
			if ( ! empty( $post_meta['_edd_commission_payment_id'] ) ) {

				$payment    = edd_get_payment( $post_meta['_edd_commission_payment_id'] );
				if ( false !== $payment ) {
					foreach ( $payment->cart_details as $index => $item ) {

						if ( (int) $item['id'] !== (int) $download->ID ) {
							continue;
						}

						if ( false !== $commission_price_id ) {
							if ( (int) $item['item_number']['options']['price_id'] !== (int) $commission_price_id ) {
								continue;
							}
						}

						$cart_index = $index;
						break;

					}
				}

			}

			$status = 'unpaid';
			$terms  = get_the_terms( $old_commission->ID, 'edd_commission_status' );

			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$status = $term->slug;
					break;
				}
			}

			$commission_data = array(
				'user_id'       => $commission_info['user_id'],
				'amount'        => $commission_info['amount'],
				'status'        => $status,
				'download_id'   => $download->ID,
				'payment_id'    => false !== $payment && ! empty( $payment->ID ) ? $payment->ID : 0,
				'cart_index'    => $cart_index,
				'price_id'      => $commission_price_id,
				'date_created' => $old_commission->post_date,
				'date_paid'     => '',
				'type'          => ! empty( $commission_info['type'] ) ? $commission_info['type'] : eddc_get_commission_type( $download->ID ),
				'rate'          => $commission_info['rate'],
				'currency'      => ! empty( $commission_info['currency'] ) ? $commission_info['currency'] : edd_get_currency(),
			);

			$commission_id = edd_commissions()->commissions_db->insert( $commission_data, 'commission' );
			if ( ! empty( $commission_id ) ) {
				$new_commission = new EDD_Commission( $commission_id );

				// Unset the now defunct post meta items so they don't get set.
				unset( $post_meta['_edd_commission_info'] );
				unset( $post_meta['_download_id'] );
				unset( $post_meta['_edd_commission_payment_id'] );
				unset( $post_meta['_edd_commission_description'] );
				unset( $post_meta['_edd_commission_status'] );
				unset( $post_meta['_user_id'] );
				unset( $post_meta['_edd_commission_download_variation'] );

				foreach ( $post_meta as $key => $value ) {
					$new_commission->update_meta( $key, $value );
				}

				$new_commission->update_meta( 'legacy_id', $old_commission->ID );

				/**
				 * Allow developers to hook into this upgrade routine for this result, so they can move any meta they want.
				 * Developers: keep in mind any custom meta data has already been migrated over, this is just for any further
				 * customizations.
				 */
				do_action( 'eddc_migrate_commission_record', $old_commission->ID, $new_commission );
			}

		}

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'commissions_migration',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		wp_safe_redirect( $redirect );
		exit;

	} else {

		// No more commissions found, finish up
		update_option( 'eddc_version', EDD_COMMISSIONS_VERSION );
		edd_set_upgrade_complete( 'migrate_commissions' );

		delete_option( 'edd_doing_upgrade' );

		wp_redirect( admin_url() );
		exit;

	}
}
add_action( 'edd_commissions_migration', 'eddc_commissions_migration' );

/**
 * Removes legacy commission date
 *
 * @since 3.4
 * @return void
 */
function eddc_remove_legacy_commissions() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	define( 'EDDC_DOING_UPGRADES', true );

	ignore_user_abort( true );
	set_time_limit( 0 );

	$commission_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_commission'" );
	$commission_ids = wp_list_pluck( $commission_ids, 'ID' );
	$commission_ids = implode( ', ', $commission_ids );

	if( ! empty( $commission_ids ) ) {

		$delete_posts_query = "DELETE FROM $wpdb->posts WHERE ID IN ({$commission_ids})";
		$wpdb->query( $delete_posts_query );

		$delete_postmeta_query = "DELETE FROM $wpdb->postmeta WHERE post_id IN ({$commission_ids})";
		$wpdb->query( $delete_postmeta_query );

	}

	// No more commissions found, finish up
	edd_set_upgrade_complete( 'remove_legacy_commissions' );

	delete_option( 'edd_doing_upgrade' );

	wp_redirect( admin_url() );
	exit;

}
add_action( 'edd_remove_legacy_commissions', 'eddc_remove_legacy_commissions' );

/**
 * Fix the `rate` column's datatype
 *
 * @since       3.4.2
 * @return      void
 */
function eddc_342_rate_column_update() {

	if ( ! edd_has_upgrade_completed( 'commissions_342_rate_column' ) ) {

		$db = new EDDC_DB;
		@$db->create_table();

		edd_set_upgrade_complete( 'commissions_342_rate_column' );
	}
}
add_action( 'admin_init', 'eddc_342_rate_column_update' );