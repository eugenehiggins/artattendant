<?php
/**
 * Commissions Filters
 *
 * @package     EDD_Commissions
 * @subpackage  Admin
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Renders the main commissions admin page
 *
 * @since       3.3
 * @return      void
*/
function eddc_commissions_page() {
	$default_views  = eddc_commission_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'commissions';

	if ( $requested_view == 'add' ) {
		eddc_render_add_commission_view();
	} elseif ( array_key_exists( $requested_view, $default_views ) && is_callable( $default_views[$requested_view] ) ) {
		eddc_render_commission_view( $requested_view, $default_views );
	} else {
		eddc_commissions_list();
	}
}


/**
 * Register the views for commission management
 *
 * @since       3.3
 * @return      array Array of views and their callbacks
 */
function eddc_commission_views() {
	$views = array();
	return apply_filters( 'eddc_commission_views', $views );
}


/**
 * Register the tabs for commission management
 *
 * @since       3.3
 * @return      array Array of tabs for the customer
 */
function eddc_commission_tabs() {
	$tabs = array();
	return apply_filters( 'eddc_commission_tabs', $tabs );
}


/**
 * List table of commissions
 *
 * @since  3.3
 * @return void
 */
function eddc_commissions_list() {
	?>
	<div class="wrap">

		<div id="icon-edit" class="icon32"><br/></div>
		<h2>
			<?php _e( 'Commissions', 'eddc' ); ?>
			<?php $base_url = 'edit.php?post_type=download&page=edd-commissions'; ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'add' ), $base_url ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'eddc' ); ?></a>
		</h2>

		<?php if ( defined( 'EDD_VERSION' ) && version_compare( '2.4.2', EDD_VERSION, '<=' ) ) : ?>
			<div id="edd-commissions-export-wrap">
				<button class="button-primary eddc-commissions-export-toggle"><?php _e( 'Generate Payout File', 'eddc' ); ?></button>
				<button class="button-primary eddc-commissions-export-toggle" style="display:none"><?php _e( 'Close', 'eddc' ); ?></button>

				<?php do_action( 'eddc_commissions_page_buttons' ); ?>

				<form id="eddc-export-commissions" class="eddc-export-form edd-export-form" method="post" style="display:none;">
					<?php echo EDD()->html->date_field( array( 'id' => 'edd-payment-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'eddc' ) ) ); ?>
					<?php echo EDD()->html->date_field( array( 'id' => 'edd-payment-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'eddc' ) ) ); ?>
					<input type="number" increment="0.01" class="eddc-medium-text" id="minimum" name="minimum" placeholder=" <?php _e( 'Minimum', 'eddc' ); ?>" />
					<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
					<input type="hidden" name="edd-export-class" value="EDD_Batch_Commissions_Payout"/>
					<span>
						<input type="submit" value="<?php _e( 'Generate File', 'eddc' ); ?>" class="button-secondary"/>
						<span class="spinner"></span>
					</span>
					<p><?php _e( 'This will generate a payout file for review.', 'eddc' ); ?></p>
				</form>

				<form id="eddc-export-commissions-mark-as-paid" class="eddc-export-form edd-export-form" method="post" style="display: none;">
					<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
					<input type="hidden" name="edd-export-class" value="EDD_Batch_Commissions_Mark_Paid"/>
					<span>
						<input type="submit" value="<?php _e( 'Mark as Paid', 'eddc' ); ?>" class="button-primary"/>&nbsp;
						<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions' ); ?>" class="button-secondary"><?php _e( 'Cancel', 'eddc' ); ?></a>
						<span class="spinner"></span>
					</span>
					<p><?php _e( 'This will mark all unpaid commissions in the generated file as paid', 'eddc' ); ?></p>
				</form>
			</div>
		<?php else: ?>
			<p>
				<form id="commission-payouts" method="get" style="float:right;margin:0;">
					<input type="text" name="from" class="edd_datepicker" placeholder="<?php _e( 'From - mm/dd/yyyy', 'eddc' ); ?>"/>
					<input type="text" name="to" class="edd_datepicker" placeholder="<?php _e( 'To - mm/dd/yyyy', 'eddc' ); ?>"/>
					<input type="hidden" name="post_type" value="download" />
					<input type="hidden" name="page" value="edd-commissions" />
					<input type="hidden" name="edd_action" value="generate_payouts" />
					<?php echo wp_nonce_field( 'eddc-payout-nonce', 'eddc-payout-nonce' ); ?>
					<?php echo submit_button( __('Generate Mass Payment File', 'eddc'), 'secondary', '', false ); ?>
				</form>
			</p>
		<?php endif; ?>

		<style>
			.column-status, .column-count { width: 100px; }
			.column-limit { width: 150px; }
		</style>
		<form id="commissions-filter" method="get">
			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-commissions" />
			<?php
			$commissions_table = new edd_C_List_Table();
			$commissions_table->prepare_items();
			$commissions_table->views();

			$user_id      = $commissions_table->get_filtered_user();
			$total_unpaid = edd_currency_filter( edd_format_amount( eddc_get_unpaid_totals( $user_id ) ) );
			?>
			<div class="eddc-user-search-wrapper">
				<?php if ( ! empty( $user_id ) ) : ?>
					<?php $user = get_userdata( $user_id ); ?>
					<?php printf( __( 'Showing commissions for: %s', 'eddc' ), $user->user_nicename ); ?> <a class="eddc-clear-search" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions' ); ?>">&times;</a>
				<?php else: ?>
					<?php echo EDD()->html->ajax_user_search( array( 'name' => 'user', 'placeholder' => __( 'Search Users', 'eddc' ) ) ); ?>
					<input type="submit" class="button-secondary" value="Filter" />
				<?php endif; ?>
			</div>
			<?php
			$commissions_table->display();
			?>
		</form>
		<div class="commission-totals">
			<?php _e( 'Total Unpaid:', 'eddc' ); ?>&nbsp;<strong><?php echo $total_unpaid; ?></strong>
		</div>
	</div>
	<?php

	$redirect = get_transient( '_eddc_bulk_actions_redirect' );

	if ( false !== $redirect ) : delete_transient( '_eddc_bulk_actions_redirect' );
	$redirect = admin_url( 'edit.php?post_type=download&page=edd-commissions' );

	if ( isset( $_GET['s'] ) ) {
		$redirect = add_query_arg( 's', $_GET['s'], $redirect );
	}
	?>
	<script type="text/javascript">
	window.location = "<?php echo $redirect; ?>";
	</script>
	<?php endif;
}


/**
 * Renders the add commission view
 *
 * @since       3.3
 * @return      void
 */
function eddc_render_add_commission_view() {
	$render = true;

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		edd_set_error( 'edd-no-access', __( 'You are not permitted to add commissions.', 'eddc' ) );
		$render = false;
	}
	?>
	<div class="wrap">
		<h2><?php _e( 'Add New Commission', 'eddc' ); ?></h2>
		<?php if ( edd_get_errors() ) : ?>
			<div class="error settings-error">
				<?php edd_print_errors(); ?>
			</div>
		<?php endif; ?>

		<?php if ( $render ) : ?>
			<div id="edd-item-card-wrapper" class="eddc-commission-card eddc-add-commission" style="float: left">
				<div class="info-wrapper item-section">
					<form id="add-item-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions' ); ?>">
						<div class="item-info">
							<table class="widefat striped">
								<?php do_action( 'eddc_commission_new_fields_top' ); ?>
								<tr id="eddc-add-user-id-row">
									<td class="row-title">
										<label for="user_id"><?php _e('User ID', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<?php echo EDD()->html->user_dropdown( array( 'id' => 'user_id', 'name' => 'user_id' ) ); ?>
										<p class="description"><?php _e('The ID of the user that received this commission.', 'eddc'); ?></p>
									</td>
								</tr>
								<tr id="eddc-add-download-id-row">
									<td class="row-title">
										<label for="download_id"><?php _e('Download ID', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<?php echo EDD()->html->product_dropdown( array( 'id' => 'download_id', 'name' => 'download_id', 'chosen' => true, 'variations' => true, 'class' => 'required' ) ); ?>
										<p class="description"><?php _e('The ID of the product this commission was for (required).', 'eddc'); ?></p>
									</td>
								</tr>
								<tr id="eddc-add-payment-id-row">
									<td class="row-title">
										<label for="payment_id_id"><?php _e('Payment ID', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<input type="text" id="payment_id_id" name="payment_id" value=""/>
										<p class="description"><?php _e('The payment ID this commission is related to (optional).', 'eddc'); ?></p>
									</td>
								</tr>
								<tr id="eddc-add-status-row">
									<td class="row-title">
										<label for="status"><?php _e('Status', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<?php
										$args = array(
											'options' => array(
												'unpaid'  => __( 'Unpaid', 'eddc' ),
												'paid'    => __( 'Paid', 'eddc' ),
												'revoked' => __( 'Revoked', 'eddc' ),
											),
											'name'             => 'status',
											'show_option_all'  => false,
											'show_option_none' => false,
										);
										echo EDD()->html->select( $args );
										?>
										<p class="description"><?php _e('The status of the commission record.', 'eddc'); ?></p>
									</td>
								</tr>
								<tr id="eddc-add-date-created-row">
									<td class="row-title">
										<label for="date_created"><?php _e('Date Created', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<input type="text" class="edd_commission_datepicker" id="date_created" name="date_created" />
										<p class="description"><?php _e('The date the commission should be recorded on. If blank, the payment date will be used. If no payment is defined, today\'s date will be used.', 'eddc'); ?></p>
									</td>
								</tr>
								<tr id="eddc-add-date-paid-row">
									<td class="row-title">
										<label for="date_paid"><?php _e('Date Paid', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<input type="text" disabled="disabled" class="edd_commission_datepicker" id="date_paid" name="date_paid" />
										<p class="description"><?php _e('The date the commission should be marked as paid.', 'eddc'); ?></p>
									</td>
								</tr>
								<tr id="eddc-add-type-row">
									<td class="row-title">
										<label for="type"><?php _e('Type', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<input type="radio" id="type-percentage" name="type" value="percentage" checked="checked" /> <label for="type-percentage"><?php _e( 'Percentage', 'eddc' ); ?></label>
										<br />
										<input type="radio" id="type-flat" name="type" value="flat"/> <label for="type-flat"><?php _e( 'Flat', 'eddc' ); ?></label>
										<p class="description"><?php _e('The type of commission to be recorded.', 'eddc'); ?></p>
									</td>
								</tr>
								<tr id="eddc-add-rate-row">
									<td class="row-title">
										<label for="rate"><?php _e('Rate', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<input type="text" id="rate" name="rate" value=""/>
										<p class="description"><?php _e('The percentage rate of this commission.', 'eddc'); ?></p>
									</td>
								</tr>
								<tr id="eddc-add-amount-row">
									<td class="row-title">
										<label for="amount"><?php _e('Amount', 'eddc'); ?></label>
									</td>
									<td style="word-wrap: break-word">
										<input type="text" id="amount" name="amount" value=""/>
										<p class="description"><?php _e('The total amount of this commission.', 'eddc'); ?></p>
									</td>
								</tr>
								<?php do_action( 'eddc_commission_new_fields_bottom' ); ?>
							</table>
						</div>
						<div id="item-edit-actions" class="edit-item" style="float: right; margin: 10px 0 0; display: block;">
							<?php wp_nonce_field( 'eddc_add_commission', 'eddc_add_commission_nonce' ); ?>
							<input type="submit" name="eddc_add_commission" id="eddc_add_commission" class="button button-primary" value="<?php _e( 'Add Commission', 'eddc' ); ?>" />
						</div>
						<div class="clear"></div>
					</form>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}


/**
 * Renders the commission view wrapper
 *
 * @since       3.3
 * @param       string $view The View being requested
 * @param       array $callbacks The Registered views and their callback functions
 * @return      void
 */
function eddc_render_commission_view( $view, $callbacks ) {
	$render = true;

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		edd_set_error( 'edd-no-access', __( 'You are not permitted to view this data.', 'eddc' ) );
		$render = false;
	}

	if ( ! isset( $_GET['commission'] ) || ! is_numeric( $_GET['commission'] ) ) {
		edd_set_error( 'edd-invalid-commission', __( 'Invalid commission ID provided.', 'eddc' ) );
		$render = false;
	}

	$commission_id   = (int) $_GET['commission'];
	$commission      = new EDD_Commission( $commission_id );
	$commission_tabs = eddc_commission_tabs();
	?>
	<div class="wrap">
		<h2><?php _e( 'Commission Details', 'eddc' ); ?></h2>
		<?php if ( edd_get_errors() ) : ?>
			<div class="error settings-error">
				<?php edd_print_errors(); ?>
			</div>
		<?php endif; ?>

		<?php if ( $render ) : ?>
			<div id="edd-item-wrapper" class="edd-item-has-tabs edd-clearfix">
				<div id="edd-item-tab-wrapper" class="commission-tab-wrapper">
					<ul id="edd-item-tab-wrapper-list" class="commission-tab-wrapper-list">
						<?php foreach ( $commission_tabs as $key => $tab ) : ?>
							<?php $active = $key === $view ? true : false; ?>
							<?php $class  = $active ? 'active' : 'inactive'; ?>

							<li class="<?php echo sanitize_html_class( $class ); ?>">
								<?php
								$tab_title  = sprintf( _x( 'Commission %s', 'Commission Details page tab title', 'eddc' ), esc_attr( $tab['title'] ) );
								$aria_label = ' aria-label="' . $tab_title . '"';
								?>

								<?php if ( ! $active ) : ?>
									<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-commissions&view=' . $key . '&commission=' . $commission_id . '#wpbody-content' ) ); ?>"<?php echo $aria_label; ?>>
								<?php endif; ?>
								<span class="edd-item-tab-label-wrap"<?php echo $active ? $aria_label : ''; ?>>
									<span class="dashicons <?php echo sanitize_html_class( $tab['dashicon'] ); ?>" aria-hidden="true"></span>
									<?php
									if ( version_compare( EDD_VERSION, 2.7, '>=' ) ) {
										echo '<span class="edd-item-tab-label">' . esc_attr( $tab['title'] ) . '</span>';
									}
									?>
								</span>
								<?php if ( ! $active ) : ?>
									</a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div id="edd-item-card-wrapper" class="eddc-commission-card" style="float: left">
					<?php
					if ( is_callable( $callbacks[ $view ] ) ) {
						$callbacks[ $view ]( $commission );
					}
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}


/**
 * View a commission
 *
 * @since       3.3
 * @param       object $commission The commission object being displayed
 * @return      void
 */
function eddc_commissions_view( $commission ) {
	if ( ! $commission ) {
		echo '<div class="info-wrapper item-section">' . __( 'Invalid commission specified.', 'eddc' ) . '</div>';
		return;
	}

	$base            = admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission->id );
	$base            = wp_nonce_url( $base, 'eddc_commission_nonce' );

	do_action( 'eddc_commission_card_top', $commission->id );
	?>
	<div class="info-wrapper item-section">
		<form id="edit-item-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission->id ); ?>">
			<div class="item-info">
				<table class="widefat striped">
					<tbody>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Commission ID', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo $commission->id; ?>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Payment', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo $commission->payment_id ? '<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $commission->payment_id ) ) . '" title="' . __( 'View payment details', 'eddc' ) . '">#' . $commission->payment_id . '</a> - ' . edd_get_payment_status( get_post( $commission->payment_id ), true  ) : ''; ?>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Status', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo ucfirst( $commission->status ); ?>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Date Created', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->date_created ) ); ?>
								<p>
									<input type="text" class="edd_commission_datepicker" name="date_created" />
								</p>
							</td>
						</tr>
						<?php if ( 'paid' === $commission->status && '0000-00-00 00:00:00' !== $commission->date_paid ) : ?>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Date Paid', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->date_paid ) ); ?>
								<p>
									<input type="text" class="edd_commission_datepicker" name="date_paid" />
								</p>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'User', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php
								$base_url  = admin_url( 'edit.php?post_type=download&page=edd-commissions' );
								$user_data = get_userdata( $commission->user_id );
								if ( false !== $user_data ) {
									echo '<a href="' . esc_url( add_query_arg( array( 'user' => $user_data->ID ), $base_url ) ) . '" title="' . __( 'View all commissions for this user', 'eddc' ) . '"">' . $user_data->display_name . '</a>&nbsp;(' . __( 'ID:', 'eddc' ) . ' ' . $commission->user_id . ')';
								} else {
									echo '<em>' . __( 'Invalid User', 'eddc' ) . '</em>';
								}
								?>
								<?php echo EDD()->html->user_dropdown( array( 'class' => 'eddc-commission-user', 'id' => 'eddc_user', 'name' => 'eddc_user', 'selected' => esc_attr( $commission->user_id ) ) ); ?>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Download', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php
								$base_url  = admin_url( 'edit.php?post_type=download&page=edd-commissions' );
								$selected  = ! empty( $commission->download_id ) ? $commission->download_id . ( ! empty( $commission->variation ) ? '_' . $commission->price_id : '' ) : '';
								if ( ! empty( $commission->download_id ) ) {
									$download = new EDD_Download( $commission->download_id );
									echo '<a href="' . esc_url( add_query_arg( array( 'download' => $commission->download_id ), $base_url ) ) . '" title="' . __( 'View all commissions for this item', 'eddc' ) . '">' . $download->get_name() . '</a>';
									echo ( ! empty( $commission->variation ) ) ? ' - ' . $commission->variation : '';
								}
								echo EDD()->html->product_dropdown( array( 'class' => 'eddc-commission-download', 'id' => 'eddc_download', 'name' => 'eddc_download', 'chosen' => true, 'variations' => true, 'selected' => $selected ) );
								?>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Rate', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo eddc_format_rate( $commission->rate, $commission->type ); ?>
								<input type="text" name="eddc_rate" class="hidden eddc-commission-rate" value="<?php echo esc_attr( $commission->rate ); ?>" />
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Amount', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo edd_currency_filter( edd_format_amount( $commission->amount ) ); ?>
								<input type="text" name="eddc_amount" class="hidden eddc-commission-amount" value="<?php echo edd_format_amount( $commission->amount ); ?>" />
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Currency', 'eddc' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo $commission->currency; ?>
							</td>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Actions:', 'eddc' ); ?></label>
							</td>
							<td class="eddc-commission-card-actions">
								<?php
								$actions = array(
									'edit' => '<a href="#" class="eddc-edit-commission">' . __( 'Edit Commission', 'eddc' ) . '</a>'
								);
								$base    = admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission->id );
								$base    = wp_nonce_url( $base, 'eddc_commission_nonce' );

								if ( $commission->status == 'revoked' ) {
									$actions['mark_as_accepted'] = sprintf( '<a href="%s&action=%s">' . __( 'Accept', 'eddc' ) . '</a>', $base, 'mark_as_accepted' );
								} elseif ( $commission->status == 'paid' ) {
									$actions['mark_as_unpaid'] = sprintf( '<a href="%s&action=%s">' . __( 'Mark as Unpaid', 'eddc' ) . '</a>', $base, 'mark_as_unpaid' );
								} else {
									$actions['mark_as_paid'] = sprintf( '<a href="%s&action=%s">' . __( 'Mark as Paid', 'eddc' ) . '</a>', $base, 'mark_as_paid' );
									$actions['mark_as_revoked'] = sprintf( '<a href="%s&action=%s">' . __( 'Revoke', 'eddc' ) . '</a>', $base, 'mark_as_revoked' );
								}

								$actions = apply_filters( 'eddc_commission_details_actions', $actions, $commission->id );

								if ( ! empty( $actions ) ) {
									$count = count( $actions );
									$i     = 1;

									foreach ( $actions as $action ) {
										echo $action;

										if ( $i < $count ) {
											echo '&nbsp;|&nbsp;';
											$i++;
										}
									}
								} else {
									_e( 'No actions available for this commission', 'eddc' );
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="item-edit-actions" class="edit-item" style="float: right; margin: 10px 0 0; display: block;">
				<?php wp_nonce_field( 'eddc_update_commission', 'eddc_update_commission_nonce' ); ?>
				<input type="submit" name="eddc_update_commission" id="eddc_update_commission" class="button button-primary" value="<?php _e( 'Update Commission', 'eddc' ); ?>" />
				<input type="hidden" name="commission_id" value="<?php echo absint( $commission->id ); ?>" />
			</div>
			<div class="clear"></div>
		</form>
	</div>

	<?php
	do_action( 'eddc_commission_card_bottom', $commission->id );
}


/**
 * Delete a commission
 *
 * @since       3.3
 * @param       object $commission The commission being deleted
 * @return      void
 */
function eddc_commissions_delete_view( $commission ) {
	if ( ! $commission ) {
		echo '<div class="info-wrapper item-section">' . __( 'Invalid commission specified.', 'eddc' ) . '</div>';
		return;
	}
	?>

	<div class="eddc-commission-delete-header">
		<span><?php printf( __( 'Commission ID: %s', 'eddc' ), $commission->id ); ?></span>
	</div>

	<?php do_action( 'eddc_commissions_before_commission_delete', $commission->id ); ?>

	<form id="delete-commission" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=delete&commission=' . $commission->id ); ?>">
		<div class="edd-item-info delete-commission">
			<span class="delete-commission-options">
				<p>
					<?php echo EDD()->html->checkbox( array( 'name' => 'eddc-commission-delete-comfirm' ) ); ?>
					<label for="eddc-commission-delete-comfirm"><?php _e( 'Are you sure you want to delete this commission?', 'eddc' ); ?></label>
				</p>

				<?php do_action( 'eddc_commissions_delete_inputs', $commission->id ); ?>
			</span>

			<span id="commission-edit-actions">
				<input type="hidden" name="commission_id" value="<?php echo $commission->id; ?>" />
				<?php wp_nonce_field( 'delete-commission', '_wpnonce', false, true ); ?>
				<input type="hidden" name="edd_action" value="delete_commission" />
				<input type="submit" disabled="disabled" id="eddc-delete-commission" class="button-primary" value="<?php _e( 'Delete Commission', 'eddc' ); ?>" />
				<a id="eddc-delete-commission-cancel" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission->id ); ?>" class="delete"><?php _e( 'Cancel', 'eddc' ); ?></a>
			</span>
		</div>
	</form>

	<?php do_action( 'eddc_commissions_after_commission_delete', $commission->id );
}
