<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$i = 0;

if( $messages->have_posts() ) {
	wp_enqueue_script( 'fep-replies-show-hide' );
	?>
	<div class="fep-message"><?php
		while ( $messages->have_posts() ) {
			$i++;
			
			$messages->the_post(); 
			$read_class = fep_is_read() ? ' fep-hide-if-js' : '';
			fep_make_read(); 
			fep_make_read( true ); ?>
			
			<div class="fep-per-message">
				<?php if( $i === 1 ){
					$participants = get_post_meta( get_the_ID(), '_participants' );
					$par = array();
					foreach( $participants as $participant ) {
						$par[] = fep_get_userdata( $participant, 'display_name', 'id' );
					} ?>
					<div class="fep-message-title-heading"><?php the_title(); ?></div>
					<div class="fep-message-title-heading participants"><?php _e("Participants", 'front-end-pm'); ?>: <?php echo apply_filters( 'fep_filter_display_participants', implode( ', ', $par ), $par ); ?></div>
					<div class="fep-message-toggle-all fep-align-right"><?php _e("Toggle Messages", 'front-end-pm'); ?></div>
				<?php } ?>
				
				<div class="fep-message-title">
					<span class="author"><?php the_author_meta('display_name'); ?></span>
					<span class="date"><?php the_time(); ?></span>
				</div>
				<div class="fep-message-content<?php echo $read_class; ?>">
					<?php the_content(); ?>
					
					<?php if( $i === 1 ){
						do_action ( 'fep_display_after_parent_message' );
					} else {
						do_action ( 'fep_display_after_reply_message' );
					} ?>
					<?php do_action ( 'fep_display_after_message', $i ); ?>
				</div>
			</div><?php
		} ?>
	</div><?php
	wp_reset_postdata();
	
	include( fep_locate_template( 'reply_form.php') );
	
} else {
	echo "<div class='fep-error'>".__("You do not have permission to view this message!", 'front-end-pm')."</div>";
}