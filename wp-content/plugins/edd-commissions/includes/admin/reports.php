<?php
/**
 * Commission Reports
 *
 * @package     EDDC
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Adds "Commissions" to the report views
 *
 * @since       1.4
 * @param       array $views The existing report views
 * @return      array $views The views updated with commissions
 */
function eddc_add_commissions_view( $views ) {
	$views['commissions'] = __( 'Commissions', 'edd' );
	return $views;
}
add_filter( 'edd_report_views', 'eddc_add_commissions_view' );


/**
 * Show Commissions Graph
 *
 * @since       1.0
 * @return      void
 */
function edd_show_commissions_graph() {
	// Retrieve the queried dates
	$dates      = edd_get_report_dates();
	$day_by_day = true;

	// Determine graph options
	switch ( $dates['range'] ) :
		case 'today' :
		case 'yesterday' :
			$day_by_day	= true;
			break;
		case 'last_year' :
		case 'this_year' :
			$day_by_day = false;
			break;
		case 'last_quarter' :
		case 'this_quarter' :
			$day_by_day = true;
			break;
		case 'other' :
			if ( $dates['m_start'] == 12 && $dates['m_end'] == 1 ) {
				$day_by_day = true;
			} elseif ( $dates['m_end'] - $dates['m_start'] >= 3 || ( $dates['year_end'] > $dates['year'] && ( $dates['m_start'] - $dates['m_end'] ) != 10 ) ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	endswitch;

	$user  = isset( $_GET['user'] ) ? absint( $_GET['user'] ) : 0;
	$total = (float) 0.00; // Total commissions for time period shown

	ob_start(); ?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php edd_report_views(); ?></div>
	</div>
	<?php
	$data = array();

	if ( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {
		// Hour by hour
		$hour  = 0;
		$month = $dates['m_start'];

		$i = 0;

		while ( $hour <= 23 ) :
			$date = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ) * 1000;

			$commissions = edd_get_commissions_by_date( $dates['day'], $month, $dates['year'], $hour, $user );
			$total      += $commissions;
			$date        = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] );
			$data[]      = array( $date * 1000, $commissions );
			$hour++;
		endwhile;
	} elseif ( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {
		$report_dates = array();

		$i = 0;
		while ( $i <= 6 ) {
			if ( ( $dates['day'] + $i ) <= $dates['day_end'] ) {
				$report_dates[ $i ] = array(
					'day'   => (string) $dates['day'] + $i,
					'month' => $dates['m_start'],
					'year'  => $dates['year'],
				);
			} else {
				$report_dates[ $i ] = array(
					'day'   => (string) $i,
					'month' => $dates['m_end'],
					'year'  => $dates['year_end'],
				);
			}
			$i++;
		}

		$start_date = $report_dates[0];
		$end_date   = end( $report_dates );

		$i = 0;
		foreach ( $report_dates as $report_date ) {
			$date = mktime( 0, 0, 0,  $report_date['month'], $report_date['day'], $report_date['year']  ) * 1000;
			if ( $report_date['day'] == $sales[ $i ]['d'] && $report_date['month'] == $sales[ $i ]['m'] && $report_date['year'] == $sales[ $i ]['y'] ) {
				$commissions = edd_get_commissions_by_date( $day, $month, $dates['year'], null, $user );
				$total += $commissions;
				$data[] = array( $date, $commissions );
				$i++;
			} else {
				$data[] = array( $date, $commissions );
			}
		}
	} else {
		if ( cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] ) < $dates['day'] ) {
			$next_day = mktime( 0, 0, 0, $dates['m_start'] + 1, 1, $dates['year'] );
			$day = date( 'd', $next_day );
			$month = date( 'm', $next_day );
			$year = date( 'Y', $next_day );
			$date_start = $year . '-' . $month . '-' . $day;
		} else {
			$date_start = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'];
		}

		if ( cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] ) < $dates['day_end'] ) {
			$date_end = $dates['year_end'] . '-' . $dates['m_end'] . '-' . cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
		} else {
			$date_end = $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'];
		}

		while ( strtotime( $date_start ) <= strtotime( $date_end ) ) {
			$m = date( 'm', strtotime( $date_start ) );
			$y = date( 'Y', strtotime( $date_start ) );
			$d = date( 'd', strtotime( $date_start ) );

			$commissions = edd_get_commissions_by_date( $d, $m, $y, null, $user );
			$total += $commissions;
			$commissions_data[ $y ][ $m ][ $d ] = $commissions;
			$date_start = date( 'Y-m-d', strtotime( '+1 day', strtotime( $date_start ) ) );
		}

		$data = array();

		if ( $day_by_day ) {
			foreach ( $commissions_data as $year => $months ) {
				foreach ( $months as $month => $days ) {
					foreach ( $days as $day => $commission ) {
						$date   = mktime( 0, 0, 0, $month, $day, $year ) * 1000;
						$data[] = array( $date, $commission );
					}
				}
			}
		} else {
			foreach ( $commissions_data as $year => $months ) {
				$month_keys = array_keys( $months );
				$last_month = end( $month_keys );

				foreach ( $months as $month => $days ) {
					$day_keys = array_keys( $days );
					$last_day = end( $day_keys );

					$consolidated_date = 1;

					if ( $day_by_day ) {
						$consolidated_date = $month === end( $month_keys ) ? cal_days_in_month( CAL_GREGORIAN, $month, $year ) : 1;
					}

					$commissions = array_sum( $days );
					$date        = mktime( 0, 0, 0, $month, $consolidated_date, $year ) * 1000;
					$data[]      = array( $date, $commissions );
				}
			}
		}
	}

	$data = array(
		__( 'Commissions', 'eddc' ) => $data
	);
	?>

	<div class="metabox-holder" style="padding-top: 0;">
		<div class="postbox">
			<h3><span><?php _e('Commissions Paid Over Time', 'edd'); ?></span></h3>

			<div class="inside">
				<?php if ( ! empty( $user ) ) : $user_data = get_userdata( $user ); ?>
				<p>
					<?php printf( __( 'Showing commissions paid to %s', 'eddc' ), $user_data->display_name ); ?>
					&nbsp;&ndash;&nbsp;<a href="<?php echo esc_url( remove_query_arg( 'user' ) ); ?>"><?php _e( 'clear', 'eddc' ); ?></a>
				</p>
				<?php endif; ?>
				<?php
					edd_reports_graph_controls();
					$graph = new EDD_Graph( $data );
					$graph->set( 'x_mode', 'time' );
					$graph->display();
				?>
				<p id="edd_graph_totals"><strong><?php _e( 'Total commissions for period shown: ', 'edd' ); echo edd_currency_filter( edd_format_amount( $total ) ); ?></strong></p>
   			</div>
   		</div>
   	</div>
	<?php
	echo ob_get_clean();
}
add_action('edd_reports_view_commissions', 'edd_show_commissions_graph');


/**
 * Report Box
 *
 * Renders the EDDC report box on the Reports page
 *
 * @since       3.3
 * @return      void
 */
function eddc_add_reports_metabox() {
	?>
	<div class="postbox edd-export-commissions-history">
		<h3><span><?php _e('Export Commissions', 'eddc' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Download a CSV giving a detailed look into commissions over time.', 'eddc' ); ?></p>

			<form id="edd-export-commissions" class="edd-export-form edd-import-export-form" method="post">
				<?php echo EDD()->html->month_dropdown( 'start_month' ); ?>
				<?php echo EDD()->html->year_dropdown( 'start_year' ); ?>
				<?php echo _x( 'to', 'Date one to date two', 'eddc' ); ?>
				<?php echo EDD()->html->month_dropdown( 'end_month' ); ?>
				<?php echo EDD()->html->year_dropdown( 'end_year' ); ?>
				<?php
					$options = apply_filters( 'eddc_export_classes', array(
						'EDD_Batch_Commissions_Report_Export'         => __( 'Overview', 'eddc' ),
						'EDD_Batch_Commissions_Report_Details_Export' => __( 'Detailed', 'eddc' ),
					) );

					$args = array(
						'options'          => $options,
						'name'             => 'edd-export-class',
						'show_option_none' => false,
						'show_option_all'  => false,
						'selected'         => false,
					);
					echo EDD()->html->select( $args );

					$args = array(
						'options' => array(
							'paid'    => __( 'Paid', 'eddc' ),
							'unpaid'  => __( 'Unpaid', 'eddc' ),
							'revoked' => __( 'Revoked', 'eddc' ),
						),
						'name'             => 'status',
						'id'               => 'eddc-export-status',
						'show_option_none' => false,
						'selected'         => false,
						'disabled'         => true,
					);
					echo EDD()->html->select( $args );
				?>
				<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
				<span>
					<input type="submit" value="<?php _e( 'Generate CSV', 'eddc' ); ?>" class="button-secondary"/>
					<?php
					$tooltip_title  = __( 'Report Types', 'eddc' );
					$tooltip_desc   = __( '<p><strong>Overview Report</strong><br />Exports cumulative totals for the selected months including paid, unpaid, revoked, and pending commissions.</p>', 'eddc' );
					$tooltip_desc  .= __( '<p><strong>Detailed Report</strong><br />Provides a list of all commission records for the dates and status selected.</p>', 'eddc' );
					$tooltip_desc   = apply_filters( 'eddc_report_types_tooltip_desc', $tooltip_desc );
					?>
					<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong><?php echo $tooltip_title; ?></strong>: <?php echo $tooltip_desc; ?>"></span>
					<span class="spinner"></span>
				</span>
			</form>

		</div><!-- .inside -->
	</div><!-- .postbox -->
	<?php
}
add_action( 'edd_reports_tab_export_content_bottom', 'eddc_add_reports_metabox' );
