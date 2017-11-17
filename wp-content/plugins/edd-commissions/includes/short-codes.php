<?php
/**
 * Short codes
 *
 * @package     EDD_Commissions
 * @subpackage  Core
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Callback function for the edd_commissioned_products shortcode
 *
 * @since       3.2.1
 * @param       array $atts Attributes from the shortcode
 * @return      string HTML Markup for the Commissioned Products
 */
function eddc_user_product_list_shortcode( $atts ) {
	$user_id = eddc_userid_from_shortcode_atts( $atts );

	return eddc_user_product_list( $user_id );
}
add_shortcode( 'edd_commissioned_products', 'eddc_user_product_list_shortcode' );


/**
 * Given a User ID, return the markup for the list of user's products that earn commissions
 *
 * @param       integer $user_id The User ID to get the commissioned products for
 * @return      string HTML markup for the list of products
 */
function eddc_user_product_list( $user_id = 0 ) {
	$user_id = empty ( $user_id ) ? get_current_user_id() : $user_id;

	if ( empty( $user_id ) ) {
		return;
	}

	$products = eddc_get_download_ids_of_user( $user_id );

	if ( empty( $products ) ) {
		return;
	}

	$header_text = __( 'Your Products', 'eddc' );
	if ( $user_id != get_current_user_id() ) {
		$user_info   = get_userdata( $user_id );
		$header_text = sprintf( __( '%s\'s Products', 'eddc' ), $user_info->display_name );
	}
	ob_start(); ?>
	<div id="edd_commissioned_products">
		<h3 class="edd_commissioned_products_header"><?php echo $header_text; ?></h3>
		<table id="edd_commissioned_products_table">
			<thead>
				<tr>
					<?php do_action( 'edd_commissioned_products_head_row_begin' ); ?>
					<th class="edd_commissioned_item"><?php _e('Item', 'eddc'); ?></th>
					<th class="edd_commissioned_sales"><?php _e('Sales', 'eddc'); ?></th>
					<?php do_action( 'edd_commissioned_products_head_row_end' ); ?>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $products ) ) : ?>
				<?php foreach ( $products as $product ) : if ( ! get_post( $product ) ) continue; ?>
					<tr class="edd_user_commission_row">
						<?php
						do_action( 'edd_commissioned_products_row_begin', $product, $user_id ); ?>
						<td class="edd_commissioned_item"><?php echo get_the_title( $product ); ?></td>
						<td class="edd_commissioned_sales"><?php echo edd_get_download_sales_stats( $product ); ?></td>
						<?php do_action( 'edd_commissioned_products_row_end', $product, $user_id ); ?>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="edd_commissioned_products_row_empty">
					<td colspan="4"><?php _e('No item', 'eddc'); ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
	return ob_get_clean();
}


/**
 * Callback function for the edd_commissions_overview shortcode
 *
 * @since       3.2.1
 * @param       array $atts Attributes from the Shotcode
 * @return      string The HTML markup for the commissions overview shortcode
 */
function eddc_user_commissions_overview_shortcode( $atts ) {
	$user_id = eddc_userid_from_shortcode_atts( $atts );

	return eddc_user_commissions_overview( $user_id );
}
add_shortcode( 'edd_commissions_overview', 'eddc_user_commissions_overview_shortcode' );


/**
 * Given a User ID, return the markup for the user's commissions overview
 *
 * @param       integer $user_id User ID to get the commissions overview for
 * @return      string HTML markup for the overview
 */
function eddc_user_commissions_overview( $user_id = 0 ) {
	$user_id = empty ( $user_id ) ? get_current_user_id() : $user_id;

	// If still empty, exit
	if ( empty( $user_id ) ) {
		return;
	}

	$unpaid_commissions  = eddc_get_unpaid_totals( array( 'user_id' => $user_id ) );
	$paid_commissions    = eddc_get_paid_totals( array( 'user_id' => $user_id ) );
	$revoked_commissions = eddc_get_revoked_totals( array( 'user_id' => $user_id ) );

	$total_unpaid        = eddc_count_user_commissions( $user_id, 'unpaid' );
	$total_paid          = eddc_count_user_commissions( $user_id, 'paid' );
	$total_revoked       = eddc_count_user_commissions( $user_id, 'revoked' );

	$stats = '';

	ob_start(); ?>
		<div id="edd_user_commissions_overview">

			<?php do_action( 'eddc_before_commissions_overview', $user_id ); ?>

			<h3><?php _e( 'Commissions Overview', 'eddc' ); ?></h3>
			<table>
				<thead>
					<th><?php _e( 'Unpaid Earnings', 'eddc' ); ?></th>
					<th><?php _e( 'Paid Earnings', 'eddc' ); ?></th>
					<th><?php _e( 'Revoked Earnings', 'eddc' ); ?></th>
					<?php do_action( 'eddc_commissions_overview_table_head', $user_id ); ?>
				</thead>
				<tbody>
					<?php if ( eddc_user_has_commissions( $user_id ) ) : ?>
					<tr>
						<td><?php echo edd_currency_filter( edd_format_amount( $unpaid_commissions ) ); ?></td>
						<td><?php echo edd_currency_filter( edd_format_amount( $paid_commissions ) ); ?></td>
						<td><?php echo edd_currency_filter( edd_format_amount( $revoked_commissions ) ); ?></td>
						<?php do_action( 'eddc_commissions_overview_table_row', $user_id ); ?>
					</tr>
					<?php else: ?>
					<tr>
						<td colspan="3"><?php _e( 'No commissions found', 'eddc' ); ?></td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<table>
				<thead>
					<th><?php _e( 'Unpaid Sales', 'eddc' ); ?></th>
					<th><?php _e( 'Paid Sales', 'eddc' ); ?></th>
					<th><?php _e( 'Revoked Sales', 'eddc' ); ?></th>
				</thead>
				<tbody>
					<?php if ( eddc_user_has_commissions( $user_id ) ) : ?>
					<tr>
						<td><?php echo $total_unpaid; ?></td>
						<td><?php echo $total_paid; ?></td>
						<td><?php echo $total_revoked; ?></td>
					</tr>
					<?php else: ?>
					<tr>
						<td colspan="3"><?php _e( 'No commissions found', 'eddc' ); ?></td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php do_action( 'eddc_after_commissions_overview', $user_id ); ?>

		</div>
	<?php


	$stats = apply_filters( 'edd_user_commissions_overview_display', ob_get_clean() );
	return $stats;
}


/**
 * Callback for the edd_commissions shortcode
 *
 * @since       3.2.1
 * @param       array $atts Array of Shortcode attributes
 * @return      string HTML markup for the commissions
 */
function eddc_edd_commissions_shortcode( $atts ) {
	$user_id = eddc_userid_from_shortcode_atts( $atts );

	return eddc_user_commissions( $user_id );
}
add_shortcode( 'edd_commissions', 'eddc_edd_commissions_shortcode' );


/**
 * Given a user id, provide a detailed list of commissions
 *
 * @param       integer $user_id Given a user id, get their commissions details
 * @return      string HTML markup for the commissions details
 */
function eddc_user_commissions( $user_id = 0 ) {
	$user_id = empty ( $user_id ) ? get_current_user_id() : $user_id;

	// If still empty, exit
	if ( empty( $user_id ) ) {
		return;
	}

	$per_page      = 20;
	$unpaid_paged  = isset( $_GET['eddcup'] ) ? absint( $_GET['eddcup'] ) : 1;
	$paid_paged    = isset( $_GET['eddcp'] )  ? absint( $_GET['eddcp'] )  : 1;
	$revoked_paged = isset( $_GET['eddcrp'] ) ? absint( $_GET['eddcrp'] ) : 1;

	$unpaid_commissions  = eddc_get_unpaid_commissions( array( 'user_id' => $user_id, 'number' => $per_page, 'paged' => $unpaid_paged ) );
	$paid_commissions    = eddc_get_paid_commissions( array( 'user_id' => $user_id, 'number' => $per_page, 'paged' => $paid_paged ) );
	$revoked_commissions = eddc_get_revoked_commissions( array( 'user_id' => $user_id, 'number' => $per_page, 'paged' => $paid_paged ) );

	$total_unpaid        = eddc_count_user_commissions( $user_id, 'unpaid' );
	$total_paid          = eddc_count_user_commissions( $user_id, 'paid' );
	$total_revoked       = eddc_count_user_commissions( $user_id, 'revoked' );

	$unpaid_total_pages  = ceil( $total_unpaid / $per_page );
	$paid_total_pages    = ceil( $total_paid / $per_page );
	$revoked_total_pages = ceil( $total_revoked / $per_page );

	$page_prefix         = false !== strpos( edd_get_current_page_url(), '?' ) ? '&' : '?';

	$stats = '';
	if ( eddc_user_has_commissions( $user_id ) ) : // only show tables if user has commission data
		ob_start(); ?>
			<div id="edd_user_commissions">

				<!-- unpaid -->
				<div id="edd_user_commissions_unpaid">
					<h3 class="edd_user_commissions_header"><?php _e('Unpaid Commissions', 'eddc'); ?></h3>
					<table id="edd_user_unpaid_commissions_table" class="edd_user_commissions">
						<thead>
							<tr class="edd_user_commission_row">
								<?php do_action( 'eddc_user_commissions_unpaid_head_row_begin' ); ?>
								<th class="edd_commission_item"><?php _e('Item', 'eddc'); ?></th>
								<th class="edd_commission_amount"><?php _e('Amount', 'eddc'); ?></th>
								<th class="edd_commission_rate"><?php _e('Rate', 'eddc'); ?></th>
								<th class="edd_commission_date"><?php _e('Date', 'eddc'); ?></th>
								<?php do_action( 'eddc_user_commissions_unpaid_head_row_end' ); ?>
							</tr>
						</thead>
						<tbody>
						<?php $requested_downloads = array(); ?>
						<?php if ( ! empty( $unpaid_commissions ) ) : ?>
							<?php foreach ( $unpaid_commissions as $commission ) : ?>
								<tr class="edd_user_commission_row">
									<?php
									if ( empty( $requested_downloads[ $commission->download_id ] ) && ! empty( $commission->download_id ) ) {
										$requested_downloads[ $commission->download_id ] = new EDD_Download( $commission->download_id );
									}
									$download = ! empty( $requested_downloads[ $commission->download_id ] ) ? $requested_downloads[ $commission->download_id ] : false;
									do_action( 'eddc_user_commissions_unpaid_row_begin', $commission );
									?>
									<td class="edd_commission_item">
										<?php if ( ! empty( $download ) ) : ?>
											<?php echo esc_html( $download->get_name() ); ?>
										<?php else: ?>
											<?php printf( __( 'No %s specified', 'eddc' ), edd_get_label_singular() ); ?>
										<?php endif; ?>
									</td>
									<td class="edd_commission_amount">
										<?php echo edd_currency_filter( edd_format_amount( edd_sanitize_amount( $commission->amount ) ) ); ?>
										<?php if ( $commission->get_meta( 'is_renewal' ) ) : ?>
											&nbsp;&olarr;
										<?php endif; ?>
									</td>
									<td class="edd_commission_rate"><?php echo eddc_format_rate( $commission->rate, $commission->type ); ?></td>
									<td class="edd_commission_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->date_created ) ); ?></td>
									<?php do_action( 'eddc_user_commissions_unpaid_row_end', $commission ); ?>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="edd_user_commission_row edd_row_empty">
								<td colspan="4"><?php _e('No unpaid commissions', 'eddc'); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div id="edd_user_commissions_unpaid_total"><?php _e('Total unpaid:', 'eddc');?>&nbsp;<?php echo edd_currency_filter( edd_format_amount( eddc_get_unpaid_totals( $user_id ) ) ); ?></div>

					<div id="edd_commissions_unpaid_pagination" class="navigation">
					<?php
						$big = 999999;
						$search_for   = array( $big, '#038;' );
						$replace_with = array( '%#%', '&' );
						echo paginate_links( array(
							'base'    => remove_query_arg( 'eddcup', str_replace( $search_for, $replace_with, edd_get_current_page_url() ) ) . '%_%',
							'format'  => $page_prefix . 'eddcup=%#%',
							'current' => max( 1, $unpaid_paged ),
							'total'   => $unpaid_total_pages
						) );
					?>
					</div>

				</div><!--end #edd_user_commissions_unpaid-->

				<!-- paid -->
				<div id="edd_user_commissions_paid">
					<h3 class="edd_user_commissions_header"><?php _e('Paid Commissions', 'eddc'); ?></h3>
					<table id="edd_user_paid_commissions_table" class="edd_user_commissions">
						<thead>
							<tr class="edd_user_commission_row">
								<?php do_action( 'eddc_user_commissions_paid_head_row_begin' ); ?>
								<th class="edd_commission_item"><?php _e('Item', 'eddc'); ?></th>
								<th class="edd_commission_amount"><?php _e('Amount', 'eddc'); ?></th>
								<th class="edd_commission_rate"><?php _e('Rate', 'eddc'); ?></th>
								<th class="edd_commission_date"><?php _e('Date', 'eddc'); ?></th>
								<?php do_action( 'eddc_user_commissions_paid_head_row_end' ); ?>
							</tr>
						</thead>
						<tbody>
						<?php $total = (float) 0; ?>
						<?php if( ! empty( $paid_commissions ) ) : ?>
							<?php foreach( $paid_commissions as $commission ) : ?>
								<tr class="edd_user_commission_row">
									<?php
									if ( empty( $requested_downloads[ $commission->download_id ] ) && ! empty( $commission->download_id ) ) {
										$requested_downloads[ $commission->download_id ] = new EDD_Download( $commission->download_id );
									}
									$download = ! empty( $requested_downloads[ $commission->download_id ] ) ? $requested_downloads[ $commission->download_id ] : false;
									do_action( 'eddc_user_commissions_paid_row_begin', $commission );
									?>
									<td class="edd_commission_item">
										<?php if ( ! empty( $download ) ) : ?>
											<?php echo esc_html( $download->get_name() ); ?>
										<?php else: ?>
											<?php printf( __( 'No %s specified', 'eddc' ), edd_get_label_singular() ); ?>
										<?php endif; ?>
									</td>
									<td class="edd_commission_amount">
										<?php echo edd_currency_filter( edd_format_amount( edd_sanitize_amount( $commission->amount ) ) ); ?>
										<?php if ( eddc_commission_is_renewal( $commission->id ) ) : ?>
											&nbsp;&olarr;
										<?php endif; ?>
									</td>
									<td class="edd_commission_rate"><?php echo eddc_format_rate( $commission->rate, $commission->type ); ?></td>
									<td class="edd_commission_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->date_created ) ); ?></td>
									<?php do_action( 'eddc_user_commissions_paid_row_end', $commission ); ?>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="edd_user_commission_row edd_row_empty">
								<td colspan="4"><?php _e('No paid commissions', 'eddc'); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div id="edd_user_commissions_paid_total"><?php _e('Total paid:', 'eddc');?>&nbsp;<?php echo edd_currency_filter( edd_format_amount( eddc_get_paid_totals( $user_id ) ) ); ?></div>

					<div id="edd_commissions_paid_pagination" class="navigation">
					<?php
						$big = 999999;
						$search_for   = array( $big, '#038;' );
						$replace_with = array( '%#%', '&' );
						echo paginate_links( array(
							'base'    => remove_query_arg( 'eddcp', str_replace( $search_for, $replace_with, edd_get_current_page_url() ) ) . '%_%',
							'format'  => $page_prefix . 'eddcp=%#%',
							'current' => max( 1, $paid_paged ),
							'total'   => $paid_total_pages
						) );
					?>
					</div>

				</div><!--end #edd_user_commissions_paid-->

				<!-- revoked -->
				<div id="edd_user_commissions_revoked">
					<h3 class="edd_user_commissions_header"><?php _e('Revoked Commissions', 'eddc'); ?></h3>
					<table id="edd_user_revoked_commissions_table" class="edd_user_commissions">
						<thead>
							<tr class="edd_user_commission_row">
								<?php do_action( 'eddc_user_commissions_revoked_head_row_begin' ); ?>
								<th class="edd_commission_item"><?php _e('Item', 'eddc'); ?></th>
								<th class="edd_commission_amount"><?php _e('Amount', 'eddc'); ?></th>
								<th class="edd_commission_rate"><?php _e('Rate', 'eddc'); ?></th>
								<th class="edd_commission_date"><?php _e('Date', 'eddc'); ?></th>
								<?php do_action( 'eddc_user_commissions_revoked_head_row_end' ); ?>
							</tr>
						</thead>
						<tbody>
						<?php $total = (float) 0; ?>
						<?php if( ! empty( $revoked_commissions ) ) : ?>
							<?php foreach( $revoked_commissions as $commission ) : ?>
								<tr class="edd_user_commission_row">
									<?php
									if ( empty( $requested_downloads[ $commission->download_id ] ) && ! empty( $commission->download_id ) ) {
										$requested_downloads[ $commission->download_id ] = new EDD_Download( $commission->download_id );
									}
									$download = ! empty( $requested_downloads[ $commission->download_id ] ) ? $requested_downloads[ $commission->download_id ] : false;
									do_action( 'eddc_user_commissions_revoked_row_begin', $commission );
									?>
									<td class="edd_commission_item">
										<?php if ( ! empty( $download ) ) : ?>
											<?php echo esc_html( $download->get_name() ); ?>
										<?php else: ?>
											<?php printf( __( 'No %s specified', 'eddc' ), edd_get_label_singular() ); ?>
										<?php endif; ?>
									</td>
									<td class="edd_commission_amount">
										<?php echo edd_currency_filter( edd_format_amount( edd_sanitize_amount( $commission->amount ) ) ); ?>
										<?php if ( $commission->get_meta( 'is_renewal' ) ) : ?>
											&nbsp;&olarr;
										<?php endif; ?>
									</td>
									<td class="edd_commission_rate"><?php echo eddc_format_rate( $commission->rate, $commission->type ); ?></td>
									<td class="edd_commission_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->date_created ) ); ?></td>
									<?php do_action( 'eddc_user_commissions_revoked_row_end', $commission ); ?>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="edd_user_commission_row edd_row_empty">
								<td colspan="4"><?php _e('No revoked commissions', 'eddc'); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div id="edd_user_commissions_revoked_total"><?php _e('Total revoked:', 'eddc');?>&nbsp;<?php echo edd_currency_filter( edd_format_amount( eddc_get_revoked_totals( $user_id ) ) ); ?></div>

					<div id="edd_commissions_revoked_pagination" class="navigation">
					<?php
						$big = 999999;
						$search_for   = array( $big, '#038;' );
						$replace_with = array( '%#%', '&' );
						echo paginate_links( array(
							'base'    => remove_query_arg( 'eddcrp', str_replace( $search_for, $replace_with, edd_get_current_page_url() ) ) . '%_%',
							'format'  => $page_prefix . 'eddcrp=%#%',
							'current' => max( 1, $revoked_paged ),
							'total'   => $revoked_total_pages
						) );
					?>
					</div>

				</div><!--end #edd_user_commissions_revoked-->

				<?php if ( ! empty( $total_paid ) ) : ?>
				<div id="edd_commissions_export">
					<?php
					$args = array(
						'user_id' => $user_id,
						'number'  => 1,
						'orderby' => 'date',
						'order'   => 'ASC',
					);

					$first_commission = eddc_get_paid_commissions( $args );
					$first_year       = date( 'Y', strtotime( $first_commission[0]->date_created ) );
					$years_back       = date( 'Y', current_time( 'timestamp' ) ) - $first_year;
					$url              = is_admin() ? admin_url( 'index.php' ) : home_url();
					?>
					<h3><?php _e( 'Export Paid Commissions', 'eddc' ); ?></h3>
					<form method="post" action="<?php echo $url; ?>">
						<?php echo EDD()->html->month_dropdown(); ?>
						<?php echo EDD()->html->year_dropdown( 'year', 0, $years_back, 0 ); ?>
						<input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
						<input type="hidden" name="edd_action" value="generate_commission_export"/>
						<input type="submit" class="edd-submit button" value="<?php _e( 'Download CSV', 'eddc' ); ?>"/>
					</form>
				</div>
				<?php endif; ?>
			</div><!--end #edd_user_commissions-->
		<?php
		$stats = apply_filters( 'edd_user_commissions_display', ob_get_clean() );
	endif;

	return $stats;
}


/**
 * Callback for the edd_commissions_graph shortcode
 *
 * @since       3.2.1
 * @param       array $atts Array of shortcode attributes
 * @return      string HTML markup for the commissions graph
 */
function eddc_user_commissions_graph_shortcode( $atts ) {
	$user_id = eddc_userid_from_shortcode_atts( $atts );

	return eddc_user_commissions_graph( $user_id );
}
add_shortcode( 'edd_commissions_graph', 'eddc_user_commissions_graph_shortcode' );


/**
 * Given a user id, display a graph of commissions
 *
 * @since       3.2
 * @param       integer $user_id The user id to display commissions graph for
 * @return      string HTML markup of the commissions graph for the user
 */
function eddc_user_commissions_graph( $user_id = 0 ) {
	$user_id = empty ( $user_id ) ? get_current_user_id() : $user_id;

	// If still empty, exit
	if ( empty( $user_id ) ) {
		return;
	}

	$graph = '';
	if ( eddc_user_has_commissions( $user_id ) ) :
		include_once( EDD_PLUGIN_DIR . 'includes/admin/reporting/class-edd-graph.php' );
		global $post;
		$month = ! isset( $_GET['month'] ) ? date( 'n' ) : absint( $_GET['month'] );
		$year  = ! isset( $_GET['year'] )  ? date( 'Y' ) : absint( $_GET['year' ] );
		$num_of_days = cal_days_in_month( CAL_GREGORIAN, $month, $year );

		ob_start(); ?>
		<script>
		if ( typeof( edd_vars ) === 'undefined' ) {
			edd_vars = {
				"currency": "<?php echo edd_get_currency(); ?>",
				"currency_sign": "<?php echo edd_currency_filter(""); ?>",
				"currency_pos": "<?php echo edd_get_option( 'currency_position', 'before' ); ?>",
				"currency_decimals": "<?php echo edd_currency_decimal_filter(); ?>",
			};
		}
		</script>
		<style>
		.tickLabel {
			width: 30px;
		}
		.legend > table {
			width: auto;
		}
		</style>
		<div id="eddc-dashboard-graphs">

			<h4><?php _e( 'Commission Stats', 'eddc' ); ?></h4>
			<form id="edd-graphs-filter" method="get" action="<?php echo get_the_permalink( $post->ID ); ?>#eddc-dashboard-graphs">
				<div class="tablenav top">
					<div class="actions">
						<?php echo EDD()->html->month_dropdown( 'month', $month ); ?>
						<?php echo EDD()->html->year_dropdown( 'year', $year ); ?>

						<input type="hidden" name="edd_action" value="filter_reports" />
						<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'eddc' ); ?>"/>
					</div>
				</div>
			</form>
			<?php

			$args = array(
				'user_id'        => $user_id,
				'number'         => -1,
				'query_args'     => array(
					'date_query' => array(
						'after'       => array(
							'year'    => $year,
							'month'   => $month,
							'day'     => 1,
						),
						'before'      => array(
							'year'    => $year,
							'month'   => $month,
							'day'     => $num_of_days,
						),
						'inclusive' => true
					)
				)
			);

			$commissions = eddc_get_commissions( $args );

			$grouped_data = array();
			if ( ! empty( $commissions ) ) {
				foreach ( $commissions as $commission ) {
					$key = date( 'njY', strtotime( $commission->date_created ) );
					if ( ! isset( $grouped_data[ $key ] ) ) {
						$grouped_data[ $key ] = array();
						$grouped_data[ $key ]['earnings'] = $commission->amount;
						$grouped_data[ $key ]['sales']    = 1;
					} else {
						$grouped_data[ $key ]['earnings'] += (float) $commission->amount;
						$grouped_data[ $key ]['sales']++;
					}
				}
			}

			$d = 1;
			while ( $d <= $num_of_days ) {
				$key      = $month . $d . $year;
				$date     = mktime( 0, 0, 0, $month, $d, $year ) * 1000;
				$sales    = isset( $grouped_data[ $key ]['sales'] )    ? $grouped_data[ $key ]['sales']    : 0;
				$earnings = isset( $grouped_data[ $key ]['earnings'] ) ? round( $grouped_data[ $key ]['earnings'], edd_currency_decimal_filter() ) : 0;

				$sales_data[]    = array( $date, $sales );
				$earnings_data[] = array( $date, $earnings );
				$d++;
			}

			$data = array(
				__( 'Earnings', 'edd' ) => $earnings_data,
				__( 'Sales', 'edd' )    => $sales_data
			);
			?>
			<div class="inside">
				<?php
				$graph = new EDD_Graph( $data );
				$graph->set( 'x_mode', 'time' );
				$graph->set( 'multiple_y_axes', true );
				$graph->display();
				?>
			</div>

		</div>
		<?php
		$graph = apply_filters( 'edd_user_commissions_graph_display', ob_get_clean() );
	endif;
	return $graph;
}


/**
 * Display the field to edit the PayPal email address in the profile editor
 *
 * @since       3.2
 * @return      void
 */
function eddc_profile_editor_paypal() {
	$user_id = get_current_user_id();
	if ( ! eddc_user_has_commissions( $user_id ) ) {
		return;
	}

	$custom_paypal = get_user_meta( $user_id, 'eddc_user_paypal', true );
	$email         = is_email( $custom_paypal ) ? $custom_paypal : '';
	if ( version_compare( EDD_VERSION, '2.7.8', '>=' ) ) {
		?>
		<fieldset id="eddc_profile_paypal_fieldset">

			<legend id="eddc_profile_paypal_label"><?php _e( 'Commissions', 'eddc' ); ?></legend>

			<p id="eddc_profile_paypal_wrap">
				<label for="eddc-paypal-email"><?php _e( 'PayPal Email Address', 'eddc' ); ?></label>
				<input name="eddc_paypal_email" id="eddc-paypal-email" class="text edd-input" type="email" value="<?php echo esc_attr( $email ); ?>" />
			</p>
		</fieldset>
		<?php
	} else {
		?>
		<p>
			<strong><?php _e( 'Commissions', 'eddc' ); ?></strong><br />
			<label for="eddc-paypal-email"><?php _e( 'PayPal Email Address', 'eddc' ); ?></label>
			<input name="eddc_paypal_email" id="eddc-paypal-email" class="text edd-input" type="email" value="<?php echo esc_attr( $email ); ?>" />
		</p>	
		<?php
	}
}
if ( defined( 'EDD_VERSION' ) && version_compare( EDD_VERSION, '2.7.8', '>=' ) ) {
	add_action( 'edd_profile_editor_after_address_fields', 'eddc_profile_editor_paypal', 9999 );
} else {
	add_action( 'edd_profile_editor_after_password', 'eddc_profile_editor_paypal', 9999 );
}



/**
 * Save and sanitize the PayPal email address from the profile editor
 *
 * @since       3.2
 * @param       int $user_id  The User ID being edited
 * @param       array $userdata The array of user info
 * @return      void
 */
function eddc_update_paypal_email( $user_id, $userdata ) {
	if ( ! empty( $_POST['eddc_paypal_email'] ) ) {
		$email = sanitize_text_field( $_POST['eddc_paypal_email'] );
		if ( ! is_email( $email ) ) {
			edd_set_error( 'eddc-invalid-paypal-email', __( 'PayPal email address must be a valid email address', 'eddc' ) );
		} else {
			$success = update_user_meta( $user_id, 'eddc_user_paypal', $email );
		}
	} else {
		delete_user_meta( $user_id, 'eddc_user_paypal' );
	}
}
add_action( 'edd_pre_update_user_profile', 'eddc_update_paypal_email', 10, 2 );


/**
 * Helper function for shortcodes that take a user ID
 * Allows the shortcodes to be used with a provided user_id attribute,
 * or without to get the currently logged in user's data
 *
 * @since       3.2.1
 * @param       mixed $atts Either the user id, or array of shortcode attributes
 * @return      integer The user id parsed from the provided data
 */
function eddc_userid_from_shortcode_atts( $atts ) {
	// If still empty, exit
	if ( empty( $atts ) ) {
		return false;
	}

	if ( ! is_array( $atts ) && is_numeric( $atts ) ) {
		// Looks like we're getting a user ID, send it back
		return (int) $atts;
	}

	// We've gotten to an array of items (shortcode attributes)
	$shortcode_atts = array();
	
	if ( is_array( $atts ) ) {
		$shortcode_atts = shortcode_atts( array(
			'user_id' => get_current_user_id(),
		), $atts );

		$user_id = (int) $shortcode_atts['user_id'];
	}

	return $user_id;
}
