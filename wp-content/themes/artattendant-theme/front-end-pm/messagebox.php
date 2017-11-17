<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

echo fep_info_output();

if( ! $total_message ) {
	echo "<div class='fep-error'>".apply_filters('fep_filter_messagebox_empty', __("No messages found.", 'front-end-pm'), $action)."</div>";
	return;
}

do_action('fep_display_before_messagebox', $action);

	  	?><form class="fep-message-table form" method="post" action="">
		<div class="fep-table fep-action-table">
			<div>
				<div class="fep-bulk-action">
					<select name="fep-bulk-action">
						<option value=""><?php _e('Bulk action', 'front-end-pm'); ?></option>
						<?php foreach( Fep_Message::init()->get_table_bulk_actions() as $bulk_action => $bulk_action_display ) { ?>
						<option value="<?php echo $bulk_action; ?>"><?php echo $bulk_action_display; ?></option>
						<?php } ?>
					</select>
				</div>
				<div>
					<input type="hidden" name="token"  value="<?php echo fep_create_nonce('bulk_action'); ?>"/>
					<button type="submit" class="fep-button" name="fep_action" value="bulk_action"><?php _e('Apply', 'front-end-pm'); ?></button>
				</div>
				<div class="fep-loading-gif-div">
				</div>
				<div class="fep-filter">
					<select onchange="if (this.value) window.location.href=this.value">
						<option value="<?php echo esc_url( remove_query_arg( array( 'feppage', 'fep-filter') ) ); ?>"><?php _e('Show all', 'front-end-pm'); ?></option>
						<?php foreach( Fep_Message::init()->get_table_filters() as $filter => $filter_display ) { ?>
						<option value="<?php echo esc_url( add_query_arg( array('fep-filter' => $filter, 'feppage' => false ) ) ); ?>" <?php selected($g_filter, $filter);?>><?php echo $filter_display; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		</div>
		<?php if( $messages->have_posts() ) { ?>
		<div id="fep-table" class="fep-table fep-odd-even">
			<div id="fep-message-" class="fep-header-row">
				<div class="fep-column fep-column-"></div>
				<div class="fep-column fep-column-"></div>
				<div class="fep-column fep-column- upper">Person</div>
				<div class="fep-column fep-column- upper">Subject</div>
				<div class="fep-column fep-column- upper">Artwork</div>
			</div>
			<?php
			while ( $messages->have_posts() ) {
				$messages->the_post();  ?>

					<div id="fep-message-<?php the_ID(); ?>" class="fep-table-row"><?php
						foreach ( Fep_Message::init()->get_table_columns() as $column => $display ) { ?>
							<div class="fep-column fep-column-<?php echo $column; ?>"><?php Fep_Message::init()->get_column_content($column); ?></div>
						<?php } ?>

						<?php
							$artwork_link = $artwork_title = $artwork_img ='';
							$artwork_id = get_post_meta( get_the_ID(), '_artwork_id', true );
							if($artwork_id){
								$artwork_link = get_the_permalink( $artwork_id);
								$artwork_img = anagram_resize_image(array('width'=>30, 'crop'=>false, 'image_id'=> get_post_thumbnail_id( $artwork_id ), 'url'=> true ));
								$artwork_title = get_the_title( $artwork_id );

						}; ?>
						<div class="fep-column fep-column-artwork"><a href="<?php echo $artwork_link;  ?>" target="_blank"><img class="original " src="<?php echo $artwork_img; ?>" ></a> <a href="<?php echo $artwork_link;  ?>" target="_blank" class="upper"><small><?php echo $artwork_title;  ?></small></a></div>
						</div>
				<?php
			} //endwhile
			?></div><?php
			echo fep_pagination();
		} else {
			?><div class="fep-error"><?php _e('No messages found. Try different filter.', 'front-end-pm'); ?></div><?php
		}
		?></form><?php
	wp_reset_postdata();
