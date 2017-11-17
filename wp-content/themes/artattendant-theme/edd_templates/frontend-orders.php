<?php global $orders; ?>
<h1 class="fes-headers" id="fes-orders-page-title"><?php _e( 'Sales', 'edd_fes' ); ?></h1>

<table class="table fes-table " id="fes-order-list">
	<thead>
		<tr>
			<th><?php _e( 'Artwork', 'edd_fes' ); ?></th>
<!-- 			<th><?php _e( 'Status', 'edd_fes' ); ?></th> -->
			<th><?php _e( 'Amount', 'edd_fes' ); ?></th>
			<th><?php _e( 'Purchaser', 'edd_fes' ) ?></th>
			<th><?php _e( 'Details','edd_fes') ?></th>
			<?php do_action('fes-order-table-column-title'); ?>
			<th><?php _e( 'Date', 'edd_fes' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if (count($orders) > 0 ){
		foreach ( $orders as $order ) : ?>
			<tr>
				<td class = "fes-order-list-td"><?php echo anagram_get_order_title( $order->ID); //echo EDD_FES()->dashboard->order_list_title($order->ID); ?></td>
<!-- 				<td class = "fes-order-list-td"><?php echo EDD_FES()->dashboard->order_list_status($order->ID); ?></td> -->
				<td class = "fes-order-list-td"><?php echo EDD_FES()->dashboard->order_list_total($order->ID); ?></td>
				<td class = "fes-order-list-td"><?php echo EDD_FES()->dashboard->order_list_customer($order->ID); ?></td>
				<td class = "fes-order-list-td"><?php EDD_FES()->dashboard->order_list_actions($order->ID); ?></td>
				<?php do_action('fes-order-table-column-value', $order); ?>
				<td class = "fes-order-list-td"><?php echo EDD_FES()->dashboard->order_list_date($order->ID); ?></td>
			</tr>
		<?php endforeach;
		}
		else{
			echo '<tr><td colspan="6">'.__('No orders found','edd_fes').'</td></tr>';
		}
		?>
	</tbody>
</table>
<?php EDD_FES()->dashboard->order_list_pagination(); ?>

<h1 class="fes-headers" id="fes-commissions-page-title"><?php _e( 'Purchases', 'edd_fes' ); ?></h1>
<?php
echo do_shortcode('[purchase_history]');

?>

<?php
if ( EDD_FES()->integrations->is_commissions_active() ) { ?>
	<h1 class="fes-headers" id="fes-commissions-page-title"><?php _e( 'Commissions Overview', 'edd_fes' ); ?></h1>

	<div>Your commission rate is <?php echo eddc_get_recipient_rate( 0, get_current_user_id() ); ?>%.</div>
	<?php
	if ( eddc_user_has_commissions() ) {
		echo do_shortcode('[edd_commissions]');
	}
	else{

		echo __( 'You haven\'t made any sales yet!', 'edd_fes' );
	}
} else {
	echo 'Error 4908';
}