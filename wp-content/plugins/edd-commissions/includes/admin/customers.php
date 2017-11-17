<?php
/**
 * Add Commissions to the EDD Customer Interface
 *
 * @package     EDD_Commissions
 * @subpackage  Admin
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add the commissions tab to the customer interface if the customer has commissions
 *
 * @since       3.2
 * @param       array $tabs The tabs currently added to the customer view
 * @return      array Updated tabs array
 */
function eddc_customer_tab( $tabs ) {
	$customer_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;
	$customer    = new EDD_Customer( $customer_id );
	$downloads   = eddc_get_download_ids_of_user( $customer->user_id );

	// Check for both commissions OR if they have downloads associated with them for commissions
	if ( $customer->user_id && ( eddc_user_has_commissions( $customer->user_id ) || ! empty( $downloads ) ) ) {

		// This makes it so former commission recievers get the tab and new commission users with no sales see it
		$tabs['commissions'] = array( 'dashicon' => 'dashicons-money', 'title' => __( 'Commissions', 'eddc' ) );
	}

	return $tabs;
}
add_filter( 'edd_customer_tabs', 'eddc_customer_tab', 10, 1 );

/**
 * Register the commissions view for the customer interface
 *
 * @since       3.2
 * @param       array $tabs The tabs currently added to the customer views
 * @return      array Updated tabs array
 */
function eddc_customer_view( $views ) {
	$customer_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;
	$customer    = new EDD_Customer( $customer_id );

	if ( $customer->user_id && eddc_user_has_commissions( $customer->user_id ) ) {
		$views['commissions'] = 'eddc_customer_commissions_view';
	}

	return $views;
}
add_filter( 'edd_customer_views', 'eddc_customer_view', 10, 1 );


/**
 * Display the commissions area for the customer view
 *
 * @since       3.2
 * @param       object $customer The Customer being displayed
 * @return      void
 */
function eddc_customer_commissions_view( $customer ) {
	?>
	<div class="edd-item-notes-header">
		<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
	</div>

	<div id="edd-item-stats-wrapper" class="customer-section">
		<ul>
			<li>
				<span class="dashicons dashicons-chart-area"></span>
				<?php echo edd_currency_filter( edd_format_amount( eddc_get_paid_totals( $customer->user_id ) ) ); ?> <?php _e( 'Paid Commissions', 'eddc' ); ?>
				<?php $paid_sales = eddc_count_user_commissions( $customer->user_id, 'paid' ); ?>
				<?php if ( ! empty( $paid_sales ) ) : ?>
				<br />
				<a title="<?php _e( 'View All Paid Commissions', 'edd' ); ?>" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=paid&user=' . $customer->user_id ); ?>">
					<?php printf( _n( 'via %d sale', 'via %d sales', $paid_sales, 'eddc' ), $paid_sales  ); ?>
				</a>
				<?php endif; ?>
			</li>
			<li>
				<span class="dashicons dashicons-chart-area"></span>
				<?php echo edd_currency_filter( edd_format_amount( eddc_get_unpaid_totals( $customer->user_id ) ) ); ?> <?php _e( 'Unpaid Commissions', 'eddc' ); ?>
				<?php $unpaid_sales = eddc_count_user_commissions( $customer->user_id, 'unpaid' ); ?>
				<?php if ( ! empty( $unpaid_sales ) ) : ?>
				<br />
				<a title="<?php _e( 'View All Unpaid Commissions', 'edd' ); ?>" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=unpaid&user=' . $customer->user_id ); ?>">
					<?php printf( _n( 'via %d sale', 'via %d sales', $unpaid_sales, 'eddc' ), $unpaid_sales  ); ?>
				</a>
				<?php endif; ?>
			</li>
		</ul>
	</div>

	<?php $downloads = eddc_get_download_ids_of_user( $customer->user_id ); ?>
	<?php if ( false !== $downloads ) : ?>
	<div id="edd-item-tables-wrapper" class="customer-section">
		<h3><?php printf( __( 'Commissioned %s', 'eddc' ), edd_get_label_plural() ); ?></h3>

		<table class="wp-list-table widefat striped downloads">
			<thead>
				<tr>
					<th><?php echo edd_get_label_singular(); ?></th>
					<th><?php _e( 'Rate', 'eddc' ); ?></th>
					<th width="120px"><?php _e( 'Actions', 'eddc' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $downloads ) ) : ?>
					<?php foreach ( $downloads as $download ) : ?>
						<?php $download        = new EDD_Download( $download ); ?>
						<?php $commission_type = eddc_get_commission_type( $download->ID ); ?>
						<?php $commission_rate = eddc_get_recipient_rate( $download->ID, $customer->user_id ); ?>
						<tr>
							<td><?php echo $download->post_title; ?></td>
							<td>
								<?php echo eddc_format_rate( $commission_rate, $commission_type ); ?>

							</td>
							<td>
								<a title="<?php echo esc_attr( sprintf( __( 'View %s', 'edd' ), $download->post_title ) ); ?>" href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $download->ID ) ); ?>">
									<?php printf( __( 'View %s', 'eddc' ), edd_get_label_singular() ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="2"><?php printf( __( 'No %s Found', 'eddc' ), edd_get_label_plural() ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

	</div>
	<?php endif; ?>

	<div id="edd-item-tables-wrapper" class="customer-section">

		<h3><?php _e( 'Recent Unpaid Commissions', 'edd' ); ?></h3>
		<?php
			$args = array(
				'user_id' => $customer->user_id,
				'number'  => 10,
			);
			$commissions = eddc_get_unpaid_commissions( $args );
		?>
		<table class="wp-list-table widefat striped payments">
			<thead>
				<tr>
					<th><?php _e( 'ID', 'edd' ); ?></th>
					<th><?php _e( 'Item', 'edd' ); ?></th>
					<th><?php _e( 'Amount', 'edd' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $commissions ) ) : ?>
					<?php $requested_downloads = array(); ?>
					<?php foreach ( $commissions as $commission ) :
						if ( empty( $requested_downloads[ $commission->download_id ] ) ) {
							$requested_downloads[ $commission->download_id ] = new EDD_Download( $commission->download_id );
						}
						$download = ! empty( $commission->download_id ) ? $requested_downloads[ $commission->download_id ] : false;
						?>
						<tr>
							<td><?php echo $commission->ID; ?></td>
							<td>
								<?php
								if ( ! empty( $download ) ) {
									echo $download->get_name();
								} else {
									printf( __( 'No %s specified', 'eddc' ), edd_get_label_singular() );
								}
								?>
							</td>
							<td><?php echo edd_currency_filter( edd_sanitize_amount( $commission->amount ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="5"><?php _e( 'No unpaid commissions', 'edd' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

	</div>

	<?php
}
