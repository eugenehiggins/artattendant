<?php
/*
Plugin Name: EDD Mailchimp Subscribe
Plugin URI: http://www.amansaini.me
Description: Allows customers to signup for your MailChimp newsletter during checkout with double-opt option.
Version: 1.1.1
Author: Aman Saini
Author URI: http://www.amansaini.me

*/

// Namespace: eddms

/**
 *  Hook into Easy Digital Downloads
 */
?>
<?php

// Create the function use in the action hook
function eddms_add_dashboard_widgets() {
	wp_add_dashboard_widget( 'eddms_dashboard_widget', 'MailChimp List Status', 'eddms_dashboard_widget' );
}

// Hook into the 'wp_dashboard_setup' action to register our other functions
add_action( 'wp_dashboard_setup', 'eddms_add_dashboard_widgets' );

// Add Dashboard Styles
function eddms_enqueue( $hook ) {
	if ( $hook != 'index.php' )
		return;
	wp_enqueue_style( 'eddms_style', plugins_url( '/style.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'eddms_enqueue' );

add_filter( 'edd_settings_extensions', 'eddms_email_filter', 10, 1 );
add_action( 'edd_purchase_form_before_submit', 'eddms_add_newsletter_checkbox_to_checkout', 99 );
add_action( 'edd_checkout_before_gateway', 'eddms_mailchimp_subscribe_user', 10, 3 );

/**
 *  Inject fields into Easy Digital Downloads Extension Tab
 */
function eddms_email_filter( $fields ) {

	$lists = eddms_get_mailchimp_lists();

	$fields[] = array(  'name' => __( 'MailChimp Integration' ), 'type' => 'header', 'id' => 'eddms_mailchimp_integration_section' );

	$fields[] = array(
		'name' => __( 'MailChimp API Key', 'eddms' ),
		'desc'      => __( 'Enter your API Key. <a href="http://admin.mailchimp.com/account/api-key-popup">Get your API key</a>', 'eddms' ),
		'id'        => 'eddms_mailchimp_api_key',
		'type'      => 'text',
		'css'       => 'min-width:300px;',
	);

	$fields[] = array(
		'name' => __( 'MailChimp lists', 'eddms' ),
		'desc'      => __( 'After you add your MailChimp API Key above and save it this list will be populated.', 'eddms' ),
		'id'        => 'eddms_mailchimp_lists',
		'css'       => 'min-width:300px;',
		'type'      => 'select',
		'options'   =>  $lists,
	);

	$fields[] = array(
		'name' => __( 'Force MailChimp lists refresh', 'eddms' ),
		'desc'      => __( "Check and 'Save changes' this if you've added a new MailChimp list and it's not showing in the list above.", 'eddms' ),
		'id'        => 'eddms_force_refresh',
		'type'      => 'checkbox',
	);

	$fields[] = array(
		'name' => __( 'Checkout newsletter field label', 'eddms' ),
		'desc'      => __( 'This text will be displayed next to the newsletter checkbox at checkout', 'eddms' ),
		'id'        => 'eddms_checkout_newsletter_label',
		'type'      => 'text',
		'std'       => "Yes, I'd like to recieve email updates and special offers!",
		'css'       => 'min-width:300px;',
	);

	$fields[] = array(
		'name' => __( 'Enable Double Opt-In', 'eddms' ),
		'desc'      => __( "Learn more about <a href='http://kb.mailchimp.com/article/how-does-confirmed-optin-or-double-optin-work'>double opt-in</a>.", 'eddms' ),
		'id'        => 'eddms_mailchimp_double_optin',
		'type'      => 'checkbox',
	);



	return $fields;
}

/**
 *  Display Newsletter Checkbox on Checkout
 */
function eddms_add_newsletter_checkbox_to_checkout() {
	$settings = get_option( 'edd_settings' );
?>
   <div class="form-row eddms">
	<input type="checkbox" class="input-checkbox" name="eddms_susbscribe" id="eddms_susbscribe" value="1" checked="checked">
	<span class="eddms_label"><?php echo $settings['eddms_checkout_newsletter_label']; ?></span>
</div>
<?php
}

/**
 *  Subscribe User to MailChimp
 */
function eddms_mailchimp_subscribe_user( $post, $user_info, $valid_data ) {


	if ( !empty( $post['eddms_susbscribe'] )  && $post['eddms_susbscribe'] == '1' ) {
		try{
			require_once 'lib/edd-MCAPI.class.php';

			$settings = get_option( 'edd_settings' );
			$apikey = $settings['eddms_mailchimp_api_key'];
			$listId = $settings['eddms_mailchimp_lists'];
			$email = $user_info['email'];
			$merge_vars = array( 'FNAME' => $user_info['first_name'], 'LNAME' => $user_info['last_name'] );
			if ( $settings['eddms_mailchimp_double_optin'] == '1' ) {
				$double_optin=true;
			}else {
				$double_optin=false;
			}
			$api = new edd_MCAPI( $apikey );
			//var_dump($email);
			$retval = $api->listSubscribe( $listId, $email, $merge_vars, $email_type='html', $double_optin );
		} catch ( Exception $e ) {}
	}

}

/**
 *  Get List from MailChimp
 */
function eddms_get_mailchimp_lists(  ) {
	$mailchimp_lists = unserialize( get_transient( 'eddms_mailchimp_mailinglist' ) );

	$settings = get_option( 'edd_settings' );

	if ( empty( $mailchimp_lists ) ||  ! empty( $settings['eddms_force_refresh'] ) ) {

		$mailchimp_lists =array();

		require_once 'lib/edd-MCAPI.class.php';

		if ( empty( $settings['eddms_mailchimp_api_key'] ) ) {
			$apikey ='' ;
		}else {
			$apikey =  $settings['eddms_mailchimp_api_key'];
		}


		$api = new edd_MCAPI( $apikey );

		$retval = $api->lists();
		if ( $api->errorCode ) {
			$mailchimp_lists['false'] = __( "Unable to load MailChimp lists, check your API Key.", 'eddms' );
		} else {
			if ( $retval['total'] == 0 ) {
				$mailchimp_lists['false'] = __( "You have not created any lists at MailChimp", 'eddms' );
				return $mailchimp_lists;
			}

			foreach ( $retval['data'] as $list ) {
				$mailchimp_lists[$list['id']] = $list['name'];
			}
			set_transient( 'eddms_mailchimp_mailinglist', serialize( $mailchimp_lists ), 86400 );
			update_option( 'eddms_force_refresh', 'no' );
		}
	}
	return $mailchimp_lists;
}


/**
 * Add Dashboard Widget
 */
function eddms_dashboard_widget() {


	$mailchimp_list_stats = unserialize( get_transient( 'eddms_mailchimp_stats' ) );
	if ( empty( $mailchimp_list_stats ) ) {
		require_once 'lib/edd-MCAPI.class.php';

		$settings = get_option( 'edd_settings' );
		$apikey = $settings['eddms_mailchimp_api_key'];
		$listId = $settings['eddms_mailchimp_lists'];

		$api = new edd_MCAPI( $apikey );

		$retval = $api->lists( $filters = array( 'list_id'=>$listId ) );
		$mailchimp_list_stats = $retval['data'][0]['stats'];
		set_transient( 'eddms_mailchimp_stats', serialize( $mailchimp_list_stats ), 86400 );
	}
?>

	<div class="edds-g">
		<div class="edds-u">
			<div class="stat-block secondary-stat-block rounded">
				<p class="label">Subscribers</p>
				<div class="stat"><?php echo empty( $mailchimp_list_stats['member_count'] )? '0': $mailchimp_list_stats['member_count']; ?><span class="small-meta">total</span></div>
			</div>
		</div>
		<div class="edds-u">
			<div class="stat-block secondary-stat-block rounded">
				<p class="label">Avg Sub Rate</p>
				<div class="stat"><?php echo empty( $mailchimp_list_stats['avg_sub_rate'] )? '0': $mailchimp_list_stats['avg_sub_rate']; ?><span class="small-meta">per month</span></div>
			</div>
		</div>
		<div class="edds-u">
			<div class="stat-block secondary-stat-block rounded">
				<p class="label">Avg Unsub Rate</p>
				<div class="stat"><?php echo empty( $mailchimp_list_stats['avg_unsub_rate'] )? '0': $mailchimp_list_stats['avg_unsub_rate']; ?><span class="small-meta">per month</span></div>
			</div>

		</div>
		<div class="edds-u">
			<div class="stat-block secondary-stat-block rounded">
				<p class="label">Avg Open Rate</p>
				<div class="stat"><?php echo empty( $mailchimp_list_stats['open_rate'] )? '0':round( $mailchimp_list_stats['open_rate'] ); ?>%<span class="small-meta">per campaign</span></div>
			</div>
		</div>
		<div class="edds-u">
			<div class="stat-block secondary-stat-block rounded">
				<p class="label">Avg Click Rate</p>
				<div class="stat"><?php echo empty( $mailchimp_list_stats['click_rate'] )? '0': round( $mailchimp_list_stats['click_rate'] ); ?>%<span class="small-meta">per campaign</span></div>
			</div>
		</div>
	</div>

	<?php

}
