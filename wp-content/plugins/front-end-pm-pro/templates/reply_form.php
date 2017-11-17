<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! fep_current_user_can( 'send_reply', $parent_id ) ) {
	echo "<div class='fep-error'>".__("You do not have permission to send reply to this message!", 'front-end-pm')."</div>";
} elseif( !empty($_POST['fep_action']) && 'reply' == $_POST['fep_action'] ) {
	if( fep_errors()->get_error_messages() ) {
		echo Fep_Form::init()->form_field_output('reply', fep_errors(), array( 'fep_parent_id' => $parent_id ));
	} else {
		echo fep_info_output();
	}
} else {
	echo Fep_Form::init()->form_field_output('reply', '', array( 'fep_parent_id' => $parent_id ));
}