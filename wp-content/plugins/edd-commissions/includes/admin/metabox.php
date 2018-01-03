<?php
/**
 * Metabox functions
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register the new meta box
 *
 * @since       3.3
 * @return      void
 */
function eddc_add_commission_meta_box() {
	if ( current_user_can( 'manage_shop_settings' ) ) {
		add_meta_box( 'edd_download_commissions', __( 'Commission', 'edd' ), 'eddc_render_commissions_meta_box', 'download', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'eddc_add_commission_meta_box', 100 );


/**
 * Render the new meta box
 *
 * @since       3.3
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddc_render_commissions_meta_box() {
	global $post;

	$enabled = get_post_meta( $post->ID, '_edd_commisions_enabled', true ) ? true : false;
	$meta    = get_post_meta( $post->ID, '_edd_commission_settings', true );
	$type    = isset( $meta['type'] ) ? $meta['type'] : 'percentage';
	$display = $enabled ? '' : ' style="display:none";';

	// Convert to array
	$user_id = isset( $meta['user_id'] ) ? $meta['user_id'] : '';
	$amounts = isset( $meta['amount'] ) ? $meta['amount'] : '';
	$users   = ! empty( $user_id ) ? array_map( 'trim', explode( ',', $user_id ) ) : array();
	$amounts = ! empty( $amounts ) ? array_map( 'trim', explode( ',', $amounts ) ) : array();
	$rates   = array();

	foreach ( $users as $i => $user_id ) {
		$rates[ $i ] = array(
			'user_id' => $user_id,
			'amount'  => array_key_exists( $i, $amounts ) ? $amounts[ $i ] : ''
		);
	}

	do_action( 'eddc_metabox_before_options', $post->ID );

	// Use nonce for verification
	?>
	<input type="hidden" name="edd_download_commission_meta_box_nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />
	<table class="form-table">
		<?php do_action( 'eddc_metabox_options_table_begin', $post->ID ); ?>
		<tr id="eddc_commission_enable_wrapper">
			<td class="edd_field_type_text" colspan="2">
				<?php do_action( 'eddc_metabox_before_commissions_enabled', $post->ID ); ?>
				<input type="checkbox" name="edd_commisions_enabled" id="edd_commisions_enabled" value="1" <?php checked( true, $enabled, true ); ?>/>&nbsp;
				<label for="edd_commisions_enabled"><?php _e( 'Check to enable commissions', 'eddc' ); ?></label>
				<?php do_action( 'eddc_metabox_after_commissions_enabled', $post->ID ); ?>
			</td>
		</tr>

		<?php do_action( 'eddc_metabox_after_enable', $post->ID ); ?>

		<tr <?php echo $display; ?> class="eddc_toggled_row" id="eddc_commission_type_wrapper">
			<td class="edd_field_type_select">
				<?php do_action( 'eddc_metabox_before_type', $post->ID ); ?>
				<label for="edd_commission_settings[type]"><strong><?php _e( 'Type:', 'eddc' ); ?></strong></label>
				<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong><?php _e( 'Type', 'eddc' ); ?></strong>: <?php _e( 'With commissions enabled, you will need to specify who to assign commissions to. Commissions can be given based on a percentage of the purchase cost, or at a flat rate.', 'eddc' ); ?>"></span><br/>
				<p><?php

				// Filter in the types of commissions there could be.
				$commission_types = apply_filters( 'eddc_commission_types', array(
					'percentage' => __( 'Percentage', 'eddc' ),
					'flat'       => __( 'Flat', 'eddc' ),
				) );

				foreach ( $commission_types as $commission_type => $commission_pretty_string ) {
					?>
					<span class="edd-commission-type-wrapper" id="eddc_type_<?php echo $commission_type; ?>_wrapper">
						<input id="eddc_type_<?php echo $commission_type; ?>" type="radio" name="edd_commission_settings[type]" value="<?php echo $commission_type; ?>" <?php checked( $type, $commission_type, true ); ?>/>
						<label for="eddc_type_<?php echo $commission_type; ?>"><?php echo $commission_pretty_string; ?></label>
					</span>
					<?php
				}
				?>
				</p>
				<p><?php _e( 'Select the type of commission(s) to record.', 'eddc' ); ?></p>
				<?php do_action( 'eddc_metabox_after_type', $post->ID ); ?>
			</td>
		</tr>
		<?php do_action( 'eddc_metabox_options_table_after', $post->ID ); ?>
	</table>

	<?php do_action( 'eddc_metabox_after_options', $post->ID ); ?>

	<?php do_action( 'eddc_metabox_before_commission_users', $post->ID ); ?>

	<div <?php echo $display; ?> id="eddc_commission_rates_wrapper" class="edd_meta_table_wrap eddc_toggled_row">
		<p><strong><?php _e( 'Commission Rates:', 'eddc' ); ?></strong></p>
		<table class="widefat edd_repeatable_table" width="100%" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<?php do_action( 'eddc_meta_box_table_header_before', $post->ID ); ?>
					<th class="eddc-commission-rate-user"><?php _e( 'User', 'eddc' ); ?></th>
					<th class="eddc-commission-rate-rate">
						<?php _e( 'Rate', 'eddc' ); ?>
						<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong> <?php _e( 'Rate', 'eddc' ); ?></strong>:&nbsp;
							<?php _e( 'Enter the flat or percentage rate for commissions for each user. If no rate is entered, the default rate for the user will be used. If no user rate is set, the global default rate will be used. Currency and percent symbols are not required.', 'eddc' ); ?>">
						</span>
					</th>
					<?php do_action( 'eddc_meta_box_table_header_after', $post->ID ); ?>
					<th class="eddc-commission-rate-remove"></th>
				</tr>
			</thead>
			<tbody>
			<?php do_action( 'eddc_meta_box_table_body_before', $post->ID ); ?>
				<?php if ( ! empty( $rates ) ) : ?>
					<?php foreach ( $rates as $key => $value ) : ?>
						<tr class="edd_repeatable_upload_wrapper edd_repeatable_row" data-key="' . esc_attr( $key ) . '">
							<td>
								<?php echo EDD()->html->user_dropdown( array(
									'name'        => 'edd_commission_settings[rates][' . $key . '][user_id]',
									'id'          => 'edd_commission_user_' . $key,
									'selected'    => $value['user_id'],
								) ); ?>
							</td>
							<td>
								<input type="text" class="edd-commissions-rate-field" name="edd_commission_settings[rates][<?php echo $key; ?>][amount]" id="edd_commission_amount_<?php echo $key; ?>" value="<?php echo $value['amount']; ?>" placeholder="<?php _e( 'Rate for this user', 'eddc' ); ?>"/>
							</td>
							<td>
								<a href="#" class="edd_commissions_remove_repeatable"><span class="dashicons dashicons-dismiss"></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr class="edd_repeatable_upload_wrapper edd_repeatable_row" data-key="1">
						<td>
							<?php echo EDD()->html->user_dropdown( array(
								'name'        => 'edd_commission_settings[rates][1][user_id]',
								'id'          => 'edd_commission_user_1',
							) ); ?>
						</td>
						<td>
							<input type="text" name="edd_commission_settings[rates][1][amount]" id="edd_commission_amount_1" placeholder=" <?php _e( 'Rate for this user', 'eddc' ); ?>"/>
						</td>
						<?php do_action( 'eddc_meta_box_table_cell_remove_before', $post->ID ); ?>
						<td>
							<a href="#" class="edd_commissions_remove_repeatable" data-type="commission"><span class="dashicons dashicons-dismiss"></span></a>
						</td>
						<?php do_action( 'eddc_meta_box_table_cell_remove_after', $post->ID ); ?>
					</tr>
				<?php endif; ?>
				<tr>
					<td class="submit" colspan="4" style="float: none; clear:both; background: #fff;">
						<a class="button-secondary edd_commission_rates_add_repeatable" style="margin: 6px 0 10px;"><?php _e( 'Add New Commission Rate', 'eddc' ); ?></a>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="description"><?php _e( 'Configure the commission rates for your users. ', 'eddc' ); ?></p>
	</div>
	<?php
	do_action( 'eddc_metabox_after_commission_users', $post->ID );
}


/**
 * Save data when save_post is called
 *
 * @since       3.3
 * @param       int $post_id The ID of the post being saved
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddc_download_meta_box_save( $post_id ) {
	global $post;

	// verify nonce
	if ( ! isset( $_POST['edd_download_commission_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['edd_download_commission_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// Check for auto save / bulk edit
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	if ( isset( $_POST['post_type'] ) && 'download' != $_POST['post_type'] ) {
		return $post_id;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return $post_id;
	}

	if ( isset( $_POST['edd_commisions_enabled'] ) ) {

		update_post_meta( $post_id, '_edd_commisions_enabled', true );

		$new  = isset( $_POST['edd_commission_settings'] ) ? $_POST['edd_commission_settings'] : false;
		$type = ! empty( $_POST['edd_commission_settings']['type'] ) ? $_POST['edd_commission_settings']['type'] : 'percentage';

		if ( ! empty( $_POST['edd_commission_settings']['rates'] ) && is_array( $_POST['edd_commission_settings']['rates'] ) ) {
			$users   = array();
			$amounts = array();

			foreach( $_POST['edd_commission_settings']['rates'] as $rate ) {
				$amounts[] = $rate['amount'];
				$users[]   = $rate['user_id'];
			}

			$new['user_id'] = implode( ',', $users );
			$new['amount']  = implode( ',', $amounts );

			// No need to store this value since we're saving as a string
			unset( $new['rates'] );
		}

		if ( $new ) {
			if ( ! empty( $new['user_id'] ) ) {
				$new['amount'] = str_replace( '%', '', $new['amount'] );
				$new['amount'] = str_replace( '$', '', $new['amount'] );

				$values           = explode( ',', $new['amount'] );
				$sanitized_values = array();

				foreach ( $values as $key => $value ) {

					switch ( $type ) {
						case 'flat':
							$value = $value < 0 || ! is_numeric( $value ) ? '' : $value;
							$value = round( $value, edd_currency_decimal_filter() );
							break;
						case 'percentage':
						default:
							if ( $value < 0 || ! is_numeric( $value ) ) {
								$value = '';
							}

							$value = ( is_numeric( $value ) && $value < 1 ) ? $value * 100 : $value;
							if ( is_numeric( $value ) ) {
								$value = round( $value, 2 );
							}

							break;
					}

					$sanitized_values[ $key ] = $value;

				}

				$new_values    = implode( ',', $sanitized_values );
				$new['amount'] = trim( $new_values );
			}
		}
		update_post_meta( $post_id, '_edd_commission_settings', $new );

	} else {
		delete_post_meta( $post_id, '_edd_commisions_enabled' );
	}
}
add_action( 'save_post', 'eddc_download_meta_box_save' );
