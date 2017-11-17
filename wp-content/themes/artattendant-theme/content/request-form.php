	<div id="request" class="toshow">
		<?php gravity_form( 8, false, true, false, array('content_title' => get_the_title(),'content_id' => get_the_ID(),'owner_email' => get_the_author_meta( 'email', $post->post_author ) ), true ); ?>
	</div>