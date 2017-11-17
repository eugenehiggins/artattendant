<?php
/**
 * Plugin Name: Restrict Content Pro - Easy Digital Downloads Vendor Submission Limits
 * Description: Control the number of products a vendor can publish using Frontend Submissions for Easy Digital Downloads.
 * Version: 1.0
 * Author: Restrict Content Pro Team
 * Text Domain: rcp-edd-fes-submission-limits
 */


/**
 * Loads the plugin textdomain.
 */
function rcp_edd_fes_textdomain() {
	load_plugin_textdomain( 'rcp-edd-fes-submission-limits', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'rcp_edd_fes_textdomain' );


/**
 * Adds the plugin settings form fields to the subscription level form.
 */
function rcp_edd_fes_level_fields( $level ) {

	if ( ! class_exists( 'EDD_Front_End_Submissions' ) ) {
		return;
	}

	$allowed = ( ! empty( $level ) ? get_option( 'rcp_subscription_fes_products_allowed_' . $level->id, 0 ) : 0 );
?>

	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="rcp-edd-fes-products"><?php printf( __( '%s Product Limit', 'rcp-edd-fes-submission-limits' ), ucfirst( EDD_FES()->helper->get_option( 'fes-vendor-constant', 'vendor' ) ) ); ?></label>
		</th>
		<td>
			<input type="number" min="0" step="1" id="rcp-edd-fes-products" name="rcp-edd-fes-products" value="<?php echo esc_attr( $allowed ); ?>" style="width: 60px;"/>
			<p class="description"><?php printf( __( 'The number of %s a %s is allowed to submit per subscription period.', 'rcp-edd-fes-submission-limits' ), strtolower( edd_get_label_plural() ), strtolower( EDD_FES()->helper->get_option( 'fes-vendor-constant', 'vendor' ) ) ); ?></p>
		</td>
	</tr>

<?php
}
add_action( 'rcp_add_subscription_form', 'rcp_edd_fes_level_fields' );
add_action( 'rcp_edit_subscription_form', 'rcp_edd_fes_level_fields' );



/**
 * Saves the subscription level limit settings.
 */
function rcp_edd_fes_save_level_limits( $level_id = 0, $args = array() ) {

	if ( ! class_exists( 'EDD_Front_End_Submissions' ) ) {
		return;
	}

	if ( empty( $_POST['rcp-edd-fes-products'] ) ) {
		return;
	}
	update_option( 'rcp_subscription_fes_products_allowed_' . $level_id, absint( $_POST['rcp-edd-fes-products'] ) );
}
add_action( 'rcp_add_subscription', 'rcp_edd_fes_save_level_limits', 10, 2 );
add_action( 'rcp_edit_subscription_level', 'rcp_edd_fes_save_level_limits', 10, 2 );



/**
 * Displays a notice to the vendor on the dashboard.
 */
function rcp_edd_fes_vendor_announcement( $content ) {

	if ( ! function_exists( 'rcp_get_subscription_id' ) ) {
		return;
	}

	if ( rcp_edd_fes_member_at_limit() ) {
		return wpautop( rcp_edd_fes_vendor_at_limit_message() ) . $content;
	}
	return $content;
}
add_filter( 'fes_dashboard_content', 'rcp_edd_fes_vendor_announcement' );


/**
 * Displays a notice to the vendor on the submission form screen.
 */
function rcp_edd_fes_before_submission_form_fields( $obj, $user_id, $readonly ) {

	if ( ! function_exists( 'rcp_get_subscription_id' ) ) {
		return;
	}

	if ( rcp_edd_fes_member_at_limit() ) {
		echo rcp_edd_fes_vendor_at_limit_message();
	}
}
add_action( 'fes_render_submission_form_frontend_before_fields', 'rcp_edd_fes_before_submission_form_fields', 10, 3 );


/**
 * Constructs the vendor limit message.
 */
function rcp_edd_fes_vendor_at_limit_message() {
	global $rcp_options;
	return apply_filters(
		'rcp_edd_fes_vendor_at_limit_message',
		sprintf( __( 'You have published the maximum number of %s allowed by your subscription. <a href="%s">Upgrade your membership</a> to publish more.', 'rcp-edd-fes-submission-limits' ), strtolower( edd_get_label_plural() ), get_permalink( 1009 ) ) //anagram - edit page url for upgrade from: $rcp_options['registration_page']
	);
}


/**
 * Overrides the submission form fields when a vendor is at the submission limit.
 */
function rcp_edd_fes_submission_form_override( $fields, $obj, $user_id, $readonly ) {
	if ( rcp_edd_fes_member_at_limit() && isset( $_GET['task'] ) && 'edit-product' !== $_GET['task'] ) {
		return array(); // @todo this could suck less.
	}


	return $fields;
}
add_filter( 'fes_render_submission_form_frontend_fields', 'rcp_edd_fes_submission_form_override', 10, 4 );


/**
 * Anagram - Overrides header.
 */
function rcp_submission_form_header_override( $output  ) {
	if ( rcp_edd_fes_member_at_limit() && isset( $_GET['task'] ) && 'edit-product' !== $_GET['task'] ) {
		return false; // @todo this could suck less.
	}


	return $output;
}
add_filter( 'fes_submission_form_header', 'rcp_submission_form_header_override', 20, 1  );


/**
 * Anagram - Overrides buttons for draft.
 */
function rcp_draft_html_fes_field_override( $output, $this, $user_id, $readonly  ) {
	if ( rcp_edd_fes_member_at_limit() && isset( $_GET['task'] ) && 'edit-product' !== $_GET['task'] ) {
		return false; // @todo this could suck less.
	}


	return $output;
}
add_filter( 'fes_render_submission_form_frontend_output_after_fields', 'rcp_draft_html_fes_field_override', 20, 4  );

/**
 * Removes the New Product menu item from the vendor dashboard when the vendor is at the submission limit.
 */
function rcp_edd_fes_vendor_menu_items( $menu_items ) {

	if ( rcp_edd_fes_member_at_limit() ) {
		unset( $menu_items['new_product'] );
	}

	return $menu_items;
}
//anagram - do not remove button
//add_filter( 'fes_vendor_dashboard_menu', 'rcp_edd_fes_vendor_menu_items' );


/**
 * Updates the vendor's total submission count.
 */
function rcp_edd_fes_save_submission_count( $obj, $user_id, $save_id ) {

	if ( ! EDD()->session->get( 'fes_is_new' ) ) {
		return;
	}
	$count = (int) get_user_meta( $user_id, 'rcp_edd_fes_vendor_submission_count', true );
	$count++;
	update_user_meta( $user_id, 'rcp_edd_fes_vendor_submission_count', $count );
}
add_action( 'fes_save_submission_form_values_after_save', 'rcp_edd_fes_save_submission_count', 10, 3 );


/**
 * Determines if the member is at the product submission limit.
 */
function rcp_edd_fes_member_at_limit( $user_id = 0 ) {

	if ( ! function_exists( 'rcp_get_subscription_id' ) ) {
		return;
	}

	if ( empty( $user_id ) ) {
		$user_id = wp_get_current_user()->ID;
	}

	$limit = false;

	$subscription_id = rcp_get_subscription_id( $user_id );

	if ( $subscription_id ) {
		$max     = (int) get_option( 'rcp_subscription_fes_products_allowed_' . $subscription_id, 0 );
		$current = (int) get_user_meta( $user_id, 'rcp_edd_fes_vendor_submission_count', true );
		if ( $max >= 1 && $current >= $max ) {
			$limit = true;
		}
	}

	return $limit;
}


/**
 * Resets a vendor's product submission count when making a new payment.
 */
function rcp_edd_fes_reset_limit( $payment_id, $args = array(), $amount ) {

	if ( ! class_exists( 'EDD_Front_End_Submissions' ) ) {
		return;
	}

	if ( ! empty( $args['user_id'] ) ) {
		delete_user_meta( $args['user_id'], 'rcp_edd_fes_vendor_submission_count' );
	}
}
add_action( 'rcp_insert_payment', 'rcp_edd_fes_reset_limit', 10, 3 );