<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_Message_FES_Frontend {

	public function __construct() {
		add_filter( 'fes_signal_custom_task', array( $this, 'add_task' ), 10, 2 );
		add_action( 'fes_custom_task_message', array( $this, 'process_form' ) );
		add_action( 'fes_custom_task_message', array( $this, 'show_form' ) );
		add_action( 'fes-order-table-column-title', array( $this, 'orders_column_name' ) );
		add_action( 'fes-order-table-column-value', array( $this, 'orders_column_data' ) );
	}

	public function orders_column_name() {
		echo '<th>' . __( 'Message', 'edd-message' ) . '</th>';
	}

	public function orders_column_data( $order ) {
		// Get customer's email address
		$email = get_userdata( $order->post_author );
		$email = ( is_object( $email ) ) ? $email->user_email : null;
		?>
		<td class = "fes-order-list-td">
			<?php if ( !empty ( $email ) ) : ?>
			<form method="post" action="<?php echo esc_url( add_query_arg( array( 'task' => 'message' ) ) ); ?>">
				<input type="hidden" name="edd-message-emails" value="<?php echo esc_attr( $email ); ?>"/>
				<?php wp_nonce_field( 'add-customer-message', 'add_customer_message_nonce', true, true ); ?>
				<input id="add-customer-message" class="right button-primary" type="submit" value="<?php _e( 'Send message', 'edd-message' ); ?>"/>
			</form>
			<?php endif; ?>
		</td>
		<?php
	}

	public function add_task( $show, $task ) {
		if ( $task == 'message' ) {
			return true;
		} else {
			return false;
		}
	}

	public function show_form() {
		// Attachments configuration
		$attachments = new FES_File_Upload_Field();
		$attachments->characteristics['name'] = 'edd-message-files';
		$attachments->characteristics['single'] = true;
		$attachments->characteristics['label'] = __( 'Attachments', 'edd-message' );
		$attachments = $attachments->render_field_frontend(3);
		// All other fields
		$fields = new EDD_HTML_Elements();
		$current_uri = home_url( add_query_arg( null, null ) );
		$data = $_POST;
		$to = ( isset( $data['edd-message-emails'] ) ) ? $data['edd-message-emails'] : '';
		$from_name = ( isset( $data['edd-message-from-name'] ) ) ? $data['edd-message-from-name'] : '';
		$from_email = ( isset( $data['edd-message-from-email'] ) ) ? $data['edd-message-from-email'] : '';
		$reply = ( isset( $data['edd-message-reply'] ) ) ? $data['edd-message-reply'] : '';
		$cc = ( isset( $data['edd-message-cc'] ) ) ? $data['edd-message-cc'] : '';
		$bcc = ( isset( $data['edd-message-bcc'] ) ) ? $data['edd-message-bcc'] : '';
		$subject = ( isset( $data['edd-message-subject'] ) ) ? $data['edd-message-subject'] : '';
		$message = ( isset( $data['edd-message-message'] ) ) ? stripslashes( $data['edd-message-message'] ) : '';
		?>
		<form id="edd-add-customer-message" method="post" action="<?php echo esc_url( $current_uri ); ?>">
			<?php echo $fields->text( array(
				'id'    => 'edd-message-emails',
				'name'  => 'edd-message-emails',
				'label' => __( 'To: ', 'edd-message' ),
				'value' => esc_attr( $to ),
			) ); ?>
			<br/>
			<?php echo $fields->text( array(
				'id'    => 'edd-message-from-name',
				'name'  => 'edd-message-from-name',
				'label' => __( 'From name: ', 'edd-message' ),
				'value' => esc_attr( $from_name ),
			) ); ?>
			<br/>
			<?php echo $fields->text( array(
				'id'    => 'edd-message-from-email',
				'name'  => 'edd-message-from-email',
				'label' => __( 'From email: ', 'edd-message' ),
				'value' => esc_attr( $from_email ),
			) ); ?>
			<br/>
			<?php echo $fields->text( array(
				'id'    => 'edd-message-reply',
				'name'  => 'edd-message-reply',
				'label' => __( 'Reply to: ', 'edd-message' ),
				'value' => esc_attr( $reply ),
			) ); ?>
			<br/>
			<?php echo $fields->text( array(
				'id'    => 'edd-message-cc',
				'name'  => 'edd-message-cc',
				'label' => __( 'CC: ', 'edd-message' ),
				'value' => esc_attr( $cc ),
			) ); ?>
			<br/>
			<?php echo $fields->text( array(
				'id'    => 'edd-message-bcc',
				'name'  => 'edd-message-bcc',
				'label' => __( 'BCC: ', 'edd-message' ),
				'value' => esc_attr( $bcc ),
			) ); ?>
			<br/>
			<?php echo $fields->text( array(
				'id'    => 'edd-message-subject',
				'name'  => 'edd-message-subject',
				'label' => __( 'Subject: ', 'edd-message' ),
				'value' => esc_attr( $subject ),
			) ); ?>
			<br/>
			<?php
			_e( 'Message:', 'edd-message' );
			wp_editor( $message, 'edd-message-message', array(
				'teeny' => true,
				'editor_height' => 140,
			) );
			/**
			 * @todo figure out why multiple attachments aren't working
			 */
			echo $attachments;
			?>
			<br/>
			<?php wp_nonce_field( 'add-customer-message', 'add_customer_message_nonce', true, true ); ?>
			<input id="add-customer-message" class="right button-primary" type="submit" value="<?php _e( 'Send message', 'edd-message' ); ?>"/>
		</form>
		<?php
	}

	public function process_form() {
		$args = $_POST;
		if ( empty( $args ) ) {
			return;
		}

		if ( array_key_exists( 'edd-message-emails', $args ) ) {
			if ( empty( $args['edd-message-emails'] ) ) {
				echo '<div class="error">' . __( 'A recipient is required.', 'edd-message' ) . '</div>';

				return;
			} else {
				$to = trim( sanitize_text_field( $args['edd-message-emails'] ) );
			}
		} else {
			return;
		}
		if ( array_key_exists( 'edd-message-message', $args ) ) {
			if ( empty( $args['edd-message-message'] ) ) {
				echo '<div class="error">' . __( 'A message is required.', 'edd-message' ) . '</div>';

				return;
			} else {
				$message = wp_kses_post( do_shortcode( $args['edd-message-message'] ) );
			}
		} else {
			return;
		}
		if ( array_key_exists( 'edd-message-subject', $args ) ) {
			if ( empty( $args['edd-message-subject'] ) ) {
				echo '<div class="error">' . __( 'A subject is required.', 'edd-message' ) . '</div>';

				return;
			} else {
				$subject = trim( sanitize_text_field( $args['edd-message-subject'] ) );
			}
		} else {
			return;
		}
		if ( ! empty( $args['edd-message-files'][0] ) ) {
			$files = $args['edd-message-files'];
			$wp_upload_url = wp_upload_dir();
			$edd_upload_path = edd_get_upload_dir();
			$attachments = array();
			foreach ( $files as $file ) {
				$file = str_replace( $wp_upload_url['baseurl'] . '/edd', '', $file );
				$attachments[] = $edd_upload_path . $file;
			}
		} else {
			$attachments = null;
		}

		$nonce = $args['add_customer_message_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'add-customer-message' ) ) {
			wp_die( __( 'Cheatin\' eh?!', 'edd-message' ) );
		}

		// Send the message
		$email = new EDD_Emails();
		$email->send( $to, $subject, $message, $attachments );
		echo '<div class="success">' . __( 'Message sent successfully.', 'edd-message' ) . '</div>';
	}
}
new EDD_Message_FES_Frontend();