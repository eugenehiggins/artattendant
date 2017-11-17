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
					<div class="fep-message-title-heading subject-title">Subject: <?php the_title(); ?></div>
					<div class="fep-message-title-heading participants">
						<?php $artwork_id = get_post_meta( get_the_ID(), '_artwork_id', true );
							if($artwork_id){ ?>
								<span class="label label-default label-feature">Artwork Inquiry</span>
								<?php }; ?>

						 <?php _e("From", 'front-end-pm'); ?> <?php echo str_replace( 'artAttendant Team, ', '', apply_filters( 'fep_filter_display_participants', implode( ', ', $par ), $par )); ?></div>
					<div class="fep-message-toggle-all fep-align-right"><?php _e("Toggle Messages", 'front-end-pm'); ?></div>

				<?php if($artwork_id ){
								$artwork_link = get_the_permalink( $artwork_id);
								$artwork_img = anagram_resize_image(array('width'=>120, 'crop'=>false, 'image_id'=> get_post_thumbnail_id( $artwork_id ), 'url'=> true ));
								$artwork_title = get_the_title( $artwork_id ); ?>


						<div class="panel panel-default">
							<div class="panel-body">
								<div class="col-sm-3">
									<a href="<?php echo $artwork_link;  ?>" target="_blank"><img class="original " src="<?php echo $artwork_img; ?>" ></a>
								</div>
								<div class="col-sm-6">
									<?php echo anagram_get_public_artwork_info( $artwork_id ); ?>
								</div>
								<div class="col-sm-3">
								<h4 class="upper">Owner Info</h4>
									<?php $owner_id = anagram_user_details( $artwork_id); ?>
									<?php the_author_meta( 'display_name', $owner_id  ); ?>
									<br/>
									<a href="https://artattendant.com/messages/?fepaction=newmessage&to=<?php the_author_meta( 'user_login', $owner_id  ); ?>">Message user</a>
								</div>

							</div>
						</div>
						</div>
				<?php	}; ?>
		<?php } //end if first loop ?>
				<div class="panel panel-default">
					<div class="fep-message-title panel-heading clearfix">
						<span class="author"><?php the_author_meta('display_name'); ?></span>
						<span class="date pull-right"><?php the_time(); ?></span>
					</div>
					<div class="fep-message-content<?php echo $read_class; ?> panel-body">
						<?php the_content(); ?>

						<?php if( $i === 1 ){
							do_action ( 'fep_display_after_parent_message' );
						} else {
							do_action ( 'fep_display_after_reply_message' );
						} ?>
						<div class="read-receipt">
							<?php do_action ( 'fep_display_after_message', $i ); ?>
						</div>
					</div>
				</div>
			</div><?php
		} ?>
	</div><?php
	wp_reset_postdata();

	include( fep_locate_template( 'reply_form.php') );

} else {
	echo "<div class='fep-error'>".__("You do not have permission to view this message!", 'front-end-pm')."</div>";
}