<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!-- <h2><?php _e("Send Message", 'front-end-pm'); ?></h2> -->

<?php

if ( ! fep_current_user_can( 'send_new_message') ) {
	echo "<div class='fep-error'>".__("You do not have permission to send new message!", 'front-end-pm')."</div>";
} elseif( !empty($_POST['fep_action']) && 'newmessage' == $_POST['fep_action'] ) {
	if( fep_errors()->get_error_messages() ) {
		//custom message anagram
		if ( !current_user_can( 'manage_options' ) && is_page(1085) ) { ?>
			<span class="message_header">Send a message to your personal <span class="red">artAttendant</span></span>
		<?php }
		echo Fep_Form::init()->form_field_output('newmessage', fep_errors() );
	} else {
		echo fep_info_output();
	}
} else {
	//custom message anagram
	if ( !current_user_can( 'manage_options' ) && is_page(1085) ) { ?>
	<span class="message_header">Send a message to your personal <span class="red">artAttendant</span></span>
<?php }else if ( current_user_can( 'manage_options' ) && is_page(1085) ) { ?>
	<span class="message_header">Send a message to your customers</span>
<?php }else if ( is_singular('download') ) { ?>
	<span class="message_header">Please use the form below to request having this artwork loaned to you or for additional questions regarding a purchase if the artwork has been made available for sale.</span>
<?php
	}

	echo Fep_Form::init()->form_field_output( 'newmessage' );
}