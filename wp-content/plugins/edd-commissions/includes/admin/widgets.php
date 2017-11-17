<?php
/**
 * Widgets
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register Dashboard Widgets
 *
 * Registers the dashboard widgets.
 *
 * @since       1.6
 * @return      void
 */
function eddc_register_dashboard_commission_widgets() {
	if ( eddc_user_has_commissions() ) {
		wp_add_dashboard_widget( 'edd_dashboard_user_commissions', __('Commissions Summary', 'edd'), 'eddc_dashboard_commissions_widget' );
	}
}
add_action('wp_dashboard_setup', 'eddc_register_dashboard_commission_widgets', 100 );


/**
 * Commissions Summary Dashboard Widget
 *
 * @since       1.6
 * @global      int $user_ID The ID of the currently logged in user
 * @return      void
 */

function eddc_dashboard_commissions_widget() {
	global $user_ID;

	$per_page     = 20;
	$unpaid_paged = isset( $_GET['eddcup'] ) ? absint( $_GET['eddcup'] ) : 1;
	$paid_paged   = isset( $_GET['eddcp'] ) ? absint( $_GET['eddcp'] ) : 1;

	$unpaid_commissions = eddc_get_unpaid_commissions( array( 'user_id' => $user_ID, 'number' => $per_page, 'paged' => $unpaid_paged ) );
	$paid_commissions   = eddc_get_paid_commissions( array( 'user_id' => $user_ID, 'number' => $per_page, 'paged' => $paid_paged ) );
	$total_unpaid       = eddc_count_user_commissions( $user_ID, 'unpaid' );
	$total_paid         = eddc_count_user_commissions( $user_ID, 'paid' );
	$unpaid_total_pages = ceil( $total_unpaid / $per_page );
	$paid_total_pages   = ceil( $total_paid / $per_page );

	$stats              = '';

	if ( ! empty( $unpaid_commissions ) || ! empty( $paid_commissions ) ) : // only show tables if user has commission data
		ob_start(); ?>
			<div id="edd_user_commissions" class="edd_dashboard_widget">
				<style>#edd_user_commissions_unpaid { margin-top: 30px; }#edd_user_commissions_unpaid_total,#edd_user_commissions_paid_total { padding-bottom: 20px; } .edd_user_commissions { width: 100%; margin: 0 0 20px; }.edd_user_commissions th, .edd_user_commissions td { text-align:left; padding: 4px 4px 4px 0; }</style>
				<!-- unpaid -->
				<div id="edd_user_commissions_unpaid" class="table">
					<p class="edd_user_commissions_header sub"><?php _e('Unpaid Commissions', 'eddc'); ?></p>
					<table id="edd_user_unpaid_commissions_table" class="edd_user_commissions">
						<thead>
							<tr class="edd_user_commission_row">
								<th class="edd_commission_item"><?php _e('Item', 'eddc'); ?></th>
								<th class="edd_commission_amount"><?php _e('Amount', 'eddc'); ?></th>
								<th class="edd_commission_rate"><?php _e('Rate', 'eddc'); ?></th>
								<th class="edd_commission_date"><?php _e('Date', 'eddc'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php $total = (float) 0; ?>
						<?php if ( ! empty( $unpaid_commissions ) ) : ?>
							<?php foreach( $unpaid_commissions as $commission ) : ?>
								<tr class="edd_user_commission_row">
									<?php
									$total          += $commission->amount;
									$download        = new EDD_Download( $commission->download_id );
									?>
									<td class="edd_commission_item"><?php echo esc_html( $download->get_name() ); ?></td>
									<td class="edd_commission_amount">
										<?php echo edd_currency_filter( edd_format_amount( edd_sanitize_amount( $commission->amount ) ) ); ?>
										<?php if ( $commission->get_meta( 'is_renewal' ) ) : ?>
											&nbsp;&olarr;
										<?php endif; ?>
									</td>
									<td class="edd_commission_rate"><?php echo eddc_format_rate( $commission->rate, $commission->type ); ?></td>
									<td class="edd_commission_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->date_created ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="edd_user_commission_row edd_row_empty">
								<td colspan="4"><?php _e('No unpaid commissions', 'eddc'); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>

					<div id="edd_user_commissions_unpaid_total"><?php _e('Total unpaid:', 'eddc');?>&nbsp;<?php echo edd_currency_filter( eddc_get_unpaid_totals( $user_ID ) ); ?></div>

					<div id="edd_commissions_unpaid_pagination" class="navigation" style="padding: 0 0 15px;">
					<?php
						$big = 999999;
						echo paginate_links( array(
							'base'    => admin_url() . '%_%#edd_user_commissions_unpaid',
							'format'  => '?eddcup=%#%',
							'current' => max( 1, $unpaid_paged ),
							'total'   => $unpaid_total_pages
						) );
					?>
					</div>

				</div><!--end #edd_user_commissions_unpaid-->

				<!-- paid -->
				<div id="edd_user_commissions_paid" class="table">
					<p class="edd_user_commissions_header sub"><?php _e('Paid Commissions', 'eddc'); ?></p>
					<table id="edd_user_paid_commissions_table" class="edd_user_commissions">
						<thead>
							<tr class="edd_user_commission_row">
								<th class="edd_commission_item"><?php _e('Item', 'eddc'); ?></th>
								<th class="edd_commission_amount"><?php _e('Amount', 'eddc'); ?></th>
								<th class="edd_commission_rate"><?php _e('Rate', 'eddc'); ?></th>
								<th class="edd_commission_date"><?php _e('Date', 'eddc'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php $total = (float) 0; ?>
						<?php if ( ! empty( $paid_commissions ) ) : ?>
							<?php foreach ( $paid_commissions as $commission ) : ?>
								<tr class="edd_user_commission_row">
									<?php
									$total          += $commission->amount;
									$download        = new EDD_Download( $commission->download_id );
									?>
									<td class="edd_commission_item"><?php echo esc_html( $download->get_name() ); ?></td>
									<td class="edd_commission_amount">
										<?php echo edd_currency_filter( edd_format_amount( edd_sanitize_amount( $commission->amount ) ) ); ?>
										<?php if ( $commission->get_meta( 'is_renewal' ) ) : ?>
											&nbsp&olarr;
										<?php endif; ?>
									</td>
									<td class="edd_commission_rate"><?php echo eddc_format_rate( $commission->rate, $commission->type ); ?></td>
									<td class="edd_commission_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->date_created ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="edd_user_commission_row edd_row_empty">
								<td colspan="4"><?php _e('No paid commissions', 'eddc'); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>

					<div id="edd_user_commissions_paid_total"><?php _e('Total paid:', 'eddc');?>&nbsp;<?php echo edd_currency_filter( eddc_get_paid_totals( $user_ID ) ); ?></div>

					<div id="edd_commissions_paid_pagination" class="navigation" style="padding: 0 0 15px;">
					<?php
						$big = 999999;
						echo paginate_links( array(
							'base'    => admin_url() . '%_%#edd_user_commissions_paid',
							'format'  => '?eddcp=%#%',
							'current' => max( 1, $paid_paged ),
							'total'   => $paid_total_pages
						) );
					?>
					</div>
				</div><!--end #edd_user_commissions_paid-->

				<div id="edd_commissions_export">
					<p><strong><?php _e( 'Export Paid Commissions', 'eddc' ); ?></strong></p>
					<form method="post" action="<?php echo admin_url( 'index.php' ); ?>">
						<?php echo EDD()->html->month_dropdown(); ?>
						<?php echo EDD()->html->year_dropdown(); ?>
						<input type="hidden" name="edd_action" value="generate_commission_export"/>
						<input type="submit" class="button-secondary" value="<?php _e( 'Download CSV', 'eddc' ); ?>"/>
					</form>
				</div>
			</div><!--end #edd_user_commissions-->
		<?php
		$stats = ob_get_clean();
	endif;

	echo $stats;
}
