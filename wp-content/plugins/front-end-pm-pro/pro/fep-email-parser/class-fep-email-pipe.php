<?php

class FEP_Email_Pipe {
	public $ep;
	public $parent_id = 0;
	public $sender_id = 0;
	public $inserted_id = 0;



    public function __construct( $ep = null ){
		if( null === $ep ){
			if( ! class_exists( 'FEP_Email_Parser' ) ){
				// Require the file with the FEP_Email_Parser class in it
				require_once( dirname(__FILE__) .'/class-fep-email-parser.php');
			}

			$this->ep 		= new FEP_Email_Parser;
		} else {
			$this->ep 		= $ep;
		}

		$this->message_key 	= $this->ep->message_key();
		$this->blog_id 		= $this->ep->blog_id();
		$this->sender_email 	= $this->ep->sender_email();
		$this->subject 		= $this->ep->subject();

		$this->multisite_switch();

		if( ! $this->check() )
			return false;

		add_action ('fep_action_message_after_send', array( $this, 'upload_attachments' ), 10, 3 );

		$this->send_message();

    }

	function multisite_switch(){
		if( is_multisite() && $this->blog_id ) {
			$b_id = absint( $this->blog_id );
			add_action( 'switch_blog', array( $this, 'switch_to_blog_cache_clear' ), 10, 2 );
			switch_to_blog( $b_id );
		}
	}
	function switch_to_blog_cache_clear( $blog_id, $prev_blog_id ) {
		if ( $blog_id === $prev_blog_id )
			return false;

		wp_cache_delete( 'notoptions', 'options' );
		wp_cache_delete( 'alloptions', 'options' );
	}
	function check(){
		if( ! $this->message_key || ! $this->sender_email || ! $this->subject ){
			return false;
		}

		if( $this->blog_id && ! is_numeric( $this->blog_id ) ){
			return false;
		}
		if( ! function_exists( 'fep_get_option' ) ) {
			return false;
		}

		if( ! fep_get_option('ep_enable', 0 ) ) {
			return false;
		}
		global $wpdb;
		$this->parent_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_fep_message_key' AND meta_value = '%s' LIMIT 1", $this->message_key ) );

		if( ! $this->parent_id ){
			return false;
		}

		$this->sender_id = fep_get_userdata( $this->sender_email , 'ID', 'email');

		if( ! $this->sender_id ){
			return false;
		}

		if( ! get_current_user_id() )
		wp_set_current_user( $this->sender_id );

		if ( get_current_user_id() !== $this->sender_id ){
			return false;
		}
		if ( ! fep_current_user_can( 'send_reply', $this->parent_id ) ){
			return false;
		}

		return true;
	}

	function send_message(){
		if( fep_get_option('ep_clean_reply', 1 ) ) {
			$body = $this->ep->clean_body();
		} else {
			$body = $this->ep->body();
		}

		$message = array(
			'fep_parent_id' => $this->parent_id,
			'message_content' => $body,
			);

		$this->inserted_id = fep_send_message( $message );
	}

	function upload_attachments( $message_id, $message, $inserted_message ){
		if ( ! fep_get_option( 'allow_attachment', 1 ) || ! $message_id )
			return false;

		$attachments = $this->ep->attachments();

		if( ! $attachments )
			return false;

		$size_limit = (int) wp_convert_hr_to_bytes(fep_get_option('attachment_size','4MB'));
		$fields = (int) fep_get_option('attachment_no', 4);

		if( class_exists( 'Fep_Attachment' ) ){
			add_filter('upload_dir', array(Fep_Attachment::init(), 'upload_dir'), 99 );
		}

		$i = 0;
		foreach( $attachments as $k => $contents ) {

			$name = isset( $contents['name'] ) ? $contents['name'] : '';
			$mime = isset( $contents['mime'] ) ? $contents['mime'] : '';
			$content = isset( $contents['content'] ) ? $contents['content'] : '';

			if( !$name || !$mime || !in_array( $mime, get_allowed_mime_types() ) )
				continue;

			$size = strlen( $content );
			if( $size > $size_limit )
				continue;

			$att = wp_upload_bits( $name, null, $content );

			if( ! isset( $att['file'] ) || ! isset( $att['url'] ) || ! isset( $att['type'] ) )
				continue;

			$attachment = array(
				'guid'           => $att['url'],
				'post_mime_type' => $att['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $att['url'] ) ),
				'post_content'   => '',
				'post_author'	=> $inserted_message->post_author,
				'post_status'    => 'inherit'
			);

			// Insert the attachment.
			wp_insert_attachment( $attachment, $att['file'], $message_id );

			$i++;

			if( $i >= $fields )
				break;
		}
		if( class_exists( 'Fep_Attachment' ) ){
			remove_filter('upload_dir', array(Fep_Attachment::init(), 'upload_dir'), 99 );
		}
	}

}
