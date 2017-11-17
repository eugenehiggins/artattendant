<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up the email logs view output
 */
function edd_message_email_logs_view() {
	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/admin/class-email-logs-list-table.php' );

	$logs_table = new EDD_Message_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();
}
add_action( 'edd_logs_view_email', 'edd_message_email_logs_view' );

/**
 * Includes the email log view in view selector
 *
 * @param $views
 *
 * @return mixed
 */
function edd_message_add_email_log_view( $views ) {
	$views['email'] = __( 'Messages', 'edd-message' );
	return $views;
}
add_filter( 'edd_log_views', 'edd_message_add_email_log_view' );

/**
 * Registers the email log view
 *
 * @param $terms
 *
 * @return array
 */
function edd_message_add_email_logging( $terms ) {
	$terms[] = 'email';
	return $terms;
}
add_filter( 'edd_log_types', 'edd_message_add_email_logging' );

/**
 * Gets a list of all logged messages to a customer
 *
 * @param $id
 */
function edd_message_get_logged_emails( $id ) {
	$args = array(
		'post_type' => 'edd_log',
		'post_status'    => 'publish',
		'tax_query' => array(
			'taxonomy'  => 'edd_log_type',
			'field'     => 'slug',
			'terms'     => 'email',
		),
		'meta_key' => '_edd_log_customer_id',
		'meta_value' => $id
	);
	$logs = new WP_Query( $args );

	if ( $logs->have_posts() ) {
		echo '<div class="edd-message-logs">';
			echo '<h2>' . __( 'Message history', 'edd-message' ) . '</h2>';
			echo '<div id="postbox-container-2" class="postbox-container">';
			while( $logs->have_posts() ) {
				$logs->the_post();
				$id = $logs->post->ID;
				$to = get_post_meta( $id, '_edd_log_to' );
				$from = get_post_meta( $id, '_edd_log_from', true );
				$reply = get_post_meta( $id, '_edd_log_reply_to', true );
				$cc = get_post_meta( $id, '_edd_log_cc', true );
				$bcc = get_post_meta( $id, '_edd_log_bcc', true );
				$attachments = get_post_meta( $id, '_edd_log_attachments' );
				?>
				<div class="meta-box-sortables ui-sortable-disabled">
					<div class="postbox closed">
						<button type="button" class="handlediv button-link" aria-expanded="false">
							<span class="screen-reader-text">
								<?php
								_e( 'Toggle panel:', 'edd-message' );
								echo get_the_title();
								?>
							</span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h3 class="hndle ui-sortable-handle">
							<?php echo get_the_title(); ?>
						</h3>
						<em>
							<?php echo get_the_date(); ?>
						</em>
						<div class="inside">
							<?php
							if ( ! empty( $to ) ) {
								echo '<strong>' . __( 'To:', 'edd-message' ) . '</strong> <ul>';
								foreach ( $to[0] as $recipient ) {
									echo '<li>' . $recipient . '</li>';
								}
								echo '</ul>';
							}
							if ( ! empty( $from ) ) echo '<strong>' . __( 'From:', 'edd-message' ) . '</strong> ' . $from . '<br/>';
							if ( ! empty( $reply ) ) echo '<strong>' . __( 'Reply to:', 'edd-message' ) . '</strong> ' . $reply . '<br/>';
							if ( ! empty( $cc ) ) echo '<strong>' . __( 'CC:', 'edd-message' ) . '</strong> ' . $cc . '<br/>';
							if ( ! empty( $bcc ) ) echo '<strong>' . __( 'BCC:', 'edd-message' ) . '</strong> ' . $bcc . '<br/>';
							?>
							<strong><?php _e( 'Message body:', 'edd-message' ); ?></strong><br/>
							<?php echo get_the_content(); ?>
							<br/>
							<?php if ( ! empty( $attachments ) ) {
									echo '<strong>' . __( 'Attachments:', 'edd-message' ) . '</strong><ul>';
									foreach ( $attachments[0] as $attachment ) {
										echo '<li>' . basename( $attachment ) . '</li>';
									}
									echo '</ul>';
								} ?>
						</div>
					</div>
				</div>
			<?php
			}
			echo '</div>';
		echo '</div>';
	}
}