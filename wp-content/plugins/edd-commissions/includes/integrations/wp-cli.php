<?php
/**
 * Easy Digital Downloads WP-CLI Tools for Commissions
 *
 * This class provides an integration point with the WP-CLI plugin allowing
 * access to EDD from the command line.
 *
 * @package     EDD
 * @subpackage  Classes/CLI
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/license/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

WP_CLI::add_command( 'edd-commissions', 'EDD_Commissions_CLI' );

/**
 * Work with EDD through WP-CLI
 *
 * EDD_CLI Class
 *
 * Adds CLI support to EDD through WP-CL
 *
 * @since   1.0
 */
class EDD_Commissions_CLI extends EDD_CLI {
	/**
	 * Migrate the Commissions to the custom tables
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd-commissions migrate_commissions
	 * wp edd-commissions migrate_commissions --force
	 */
	public function migrate_commissions( $args, $assoc_args ) {
		global $wpdb;
		$force  = isset( $assoc_args['force'] ) ? true : false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_commissions' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The commissions custom database migration has already been run. To do this anyway, use the --force argument.', 'eddc' ) );
		}

		$commissions_db      = edd_commissions()->commissions_db;
		if ( ! $commissions_db->table_exists( $commissions_db->table_name ) ) {
			@$commissions_db->create_table();
		}

		$commissions_meta_db = edd_commissions()->commission_meta_db;
		if ( ! $commissions_meta_db->table_exists( $commissions_meta_db->table_name ) ) {
			@$commissions_meta_db->create_table();
		}

		$sql     = "SELECT * FROM $wpdb->posts WHERE post_type = 'edd_commission'";
		$results = $wpdb->get_results( $sql );
		$total   = count( $results );

		if ( ! empty( $total ) ) {

			$progress = new \cli\progress\Bar( 'Migrating Commissions', $total );

			foreach ( $results as $result ) {

				// Prevent an already migrated item from being migrated.
				$migrated = $wpdb->get_var( "SELECT meta_id FROM {$wpdb->prefix}edd_commissionmeta WHERE meta_key = '_edd_commission_legacy_id' AND meta_value = $result->ID" );
				if ( ! empty( $migrated ) ) {
					$progress->tick();
					continue;
				}

				$meta_items = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", $result->ID ) );
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

							if ( (int) $item[ 'id' ] !== (int) $download->ID ) {
								continue;
							}

							if ( false !== $commission_price_id ) {
								if ( (int) $item[ 'item_number' ][ 'options' ][ 'price_id' ] !== (int) $commission_price_id ) {
									continue;
								}
							}

							$cart_index = $index;
							break;

						}
					}

				}

				$status = 'unpaid';
				$terms  = get_the_terms( $result->ID, 'edd_commission_status' );

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
					'date_created' => $result->post_date,
					'date_paid'     => '',
					'type'          => ! empty( $commission_info['type'] ) ? $commission_info['type'] : eddc_get_commission_type( $download->ID ),
					'rate'          => $commission_info['rate'],
					'currency'      => ! empty( $commission_info['currency'] ) ? $commission_info['currency'] : edd_get_currency(),
				);

				$commission_id = edd_commissions()->commissions_db->insert( $commission_data, 'commission' );
				if ( ! empty( $commission_id ) ) {
					$commission = new EDD_Commission( $commission_id );

					// Unset the now defunct post meta items so they don't get set.
					unset( $post_meta['_edd_commission_info'] );
					unset( $post_meta['_download_id'] );
					unset( $post_meta['_edd_commission_payment_id'] );
					unset( $post_meta['_edd_commission_description'] );
					unset( $post_meta['_edd_commission_status'] );
					unset( $post_meta['_user_id'] );
					unset( $post_meta['_edd_commission_download_variation'] );

					foreach ( $post_meta as $key => $value ) {
						$commission->update_meta( $key, $value );
					}

					$commission->update_meta( 'legacy_id', $result->ID );

					/**
					 * Allow developers to hook into this upgrade routine for this result, so they can move any meta they want.
					 * Developers: keep in mind any custom meta data has already been migrated over, this is just for any further
					 * customizations.
					 */
					do_action( 'eddc_migrate_commission_record', $result->ID, $commission );

				}

				$progress->tick();
			}

			$progress->finish();
			WP_CLI::line( __( 'Migration complete.', 'eddc' ) );
			$new_count = edd_commissions()->commissions_db->count( array( 'number' => -1 ) );
			$old_count = $wpdb->get_col( "SELECT count(ID) FROM $wpdb->posts WHERE post_type ='edd_commission'", 0 );
			WP_CLI::line( __( 'Old Records: ', 'eddc' ) . $old_count[0] );
			WP_CLI::line( __( 'New Records: ', 'eddc' ) . $new_count );

			edd_set_upgrade_complete( 'migrate_commissions' );
			update_option( 'eddc_version', preg_replace( '/[^0-9.].*/', '', EDD_COMMISSIONS_VERSION ) );

			WP_CLI::confirm( __( 'Remove legacy commission records?', 'eddc' ), $remove_args = array() );
			WP_CLI::line( __( 'Removing old commission data.', 'eddc' ) );

			$commission_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_commission'" );
			$commission_ids = wp_list_pluck( $commission_ids, 'ID' );
			$commission_ids = implode( ', ', $commission_ids );

			$delete_posts_query = "DELETE FROM $wpdb->posts WHERE ID IN ({$commission_ids})";
			$wpdb->query( $delete_posts_query );

			$delete_postmeta_query = "DELETE FROM $wpdb->postmeta WHERE post_id IN ({$commission_ids})";
			$wpdb->query( $delete_postmeta_query );
			edd_set_upgrade_complete( 'remove_legacy_commissions' );
		} else {
			WP_CLI::line( __( 'No commission records found.', 'eddc' ) );
			edd_set_upgrade_complete( 'migrate_commissions' );
			edd_set_upgrade_complete( 'remove_legacy_commissions' );
		}

	}
}