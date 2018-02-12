<?php
	/*
	Template Name: Collection
*/
redirect_from_collections_to_login();
get_header('dashboard');

	$user = wp_get_current_user();

	 ?>


				<?php if(is_page( 'collection' )): ?>
					<header class="dash-holder">
						<div class="col-sm-3 weclome-name">
							<div class="profile-icon"><?php if(get_avatar( $user->ID, 32 )){ echo get_avatar($user->ID, 32 ); }; ?></div>

						<?php printf(__('<span>Hello %s</span>'), $user->first_name); ?>
						</div>
						<div class="col-sm-9 dash-stats hidden-xs">
							<div class="col-sm-3">
								<div class="dash-title">Artists</div>
								<span><?php $artworks = EDD_FES()->vendors->get_all_products( get_current_user_id(), array('publish', 'private' ) );
									if($artworks){ echo count(wp_get_object_terms( wp_list_pluck( $artworks, 'ID'), 'artist' ) ); }else{ echo '0';}; ?></span>
							</div>
							<div class="col-sm-3">
								<div class="dash-title">Artworks</div>
								<span><?php echo anagram_get_user_work_count(array('publish',  'private' )); ?></span>

								<span><?php //echo EDD_FES()->vendors->get_all_products_count( get_current_user_id(), array('publish', 'pending', 'draft', 'private' ) ); ?></span>
							</div>
							<div class="col-sm-3">
								<div class="dash-title">Total Cost</div>
								<span id="totalCost"><?php if (function_exists('count_total_cost')) echo count_total_cost('$'); ?></span>
							</div>
							<div class="col-sm-3">
								<div class="dash-title">Total Value</div>
								<span id="totalPrice"><?php  if (function_exists('count_total_edd_price')) echo count_total_edd_price('$'); ?></span>
							</div>
                            <div id="open-close-tab">
                                <span class="tab close"><i class="fas fa-times"></i>close</span>
                                <span class="tab open"><i class="fas fa-plus"></i>open</span>
                            </div>
						</div>
					</header><!-- .entry-header -->
				<?php endif; ?>

			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div  id="wrapper" class="entry-content">
						<?php the_content(); ?>

					</div><!-- .entry-content -->
				</article><!-- #post-## -->
			<?php endwhile; // end of the loop. ?>
<?php get_footer(); ?>