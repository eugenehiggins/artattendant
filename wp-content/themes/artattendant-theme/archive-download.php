<?php get_header(); ?>
	<div class="container main-content">
		<div class="row">
			<div id="content" class="main-content-inner col-sm-12">
			<div class="filter-bar">
				<div class="row search-bar">
					<div class="col-sm-9 col-xs-10 search-box">

						<?php echo facetwp_display( 'facet', 'artist_search' ); ?>
						<?php //echo facetwp_display( 'facet', 'search' ); ?><!-- <input placeholder="Discover" class="search-all form-control"> -->
					</div>
					<div class="col-md-3 col-xs-2 refiner">
						<i class="fa fa-sliders fa-2x fa-rotate-90" aria-hidden="true"></i> <span class="upper hidden-xs">Refine</span>
					</div>
				</div>
				<div class="the-filters">
					<section>
					  <h5 class="tab-link active">Category</h5>
					   <div class="tab-content">
					    <?php echo facetwp_display( 'facet', 'category' ); ?>
					  </div>
					  <h5 class="tab-link">Status</h5>
					  <div class="tab-content">
					    <?php echo facetwp_display( 'facet', 'artwork_status' ); ?>
					  </div>
					  <h5 class="tab-link">Price</h5>
					  <div class="tab-content">
					    <?php echo facetwp_display( 'facet', 'price' ); ?>
					    <div class="small" style="padding-top:20px;">This filters only return artworks for sale.</div>
					  </div>
					  <h5 class="tab-link">Dimensions</h5>
					   <div class="tab-content">
						    <div class="field-blocks row">
								<h5 class="slide-field col-sm-2 upper">Width</h5>
									<div class=" col-sm-10">
						    <?php echo facetwp_display( 'facet', 'width' ); ?>
									</div>
							</div>
						    <div class="field-blocks row">
								<h5 class="slide-field col-sm-2 upper">Height</h5>
									<div class=" col-sm-10">
						    <?php echo facetwp_display( 'facet', 'height' ); ?>
									</div>
							</div>
					  </div>
<!--
					  <h5 class="tab-link">Color</h5>
					  <ul class="tab-content">
					    <li>color fileds! </li>
					  </ul>
-->
					</section>
				</div>
			</div>
			<?php if( current_user_can('editor') || current_user_can('administrator') ) {  ?>

			<?php } ?>


		<div class="row filter-count">

				<div class=" col-sm-6"><?php echo facetwp_display( 'selections' ); ?><!-- <button onclick="FWP.reset()">Reset</button> --></div>


				<div class="upper-light text-right col-sm-6"><?php echo facetwp_display( 'counts' ); ?></div>

		</div>

<div class="loading" ><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>
	<div id="container" class="masonry facetwp-template clearfix row">

	 		<?php while ( have_posts() ) : the_post();
		 		//if ( get_post_status ( get_the_ID() ) == 'private' )continue; ?>
				<div class="artwork-item col-md-4">
					<a href="<?php the_permalink(); ?>"><?php  $att_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "medium"); ?><img src="<?php echo $att_image[0];?>" width="<?php echo $att_image[1];?>" height="<?php echo $att_image[2];?>"  class="attachment-medium" alt="<?php $post->post_excerpt; ?>" />
	</a>
					<div class="art-info">

						<h4><?php echo get_custom_taxonomy('artist', ' ', 'name', get_the_ID() );//get_the_term_list( $post->ID, 'medium', '', ', ' ); ?></h4>
						<h5><em><?php the_title(); ?></em></h5>
						<div class="row clear">
							<div class="col-xs-6"><a href="<?php the_permalink(); ?>" class="more-link">view more //</a></div><div class="col-xs-6 text-right"><?php echo anagram_output_artwork_status( get_the_ID() ); ?></div>
						</div>
					</div>
				</div>
			<?php endwhile;  ?>
	</div>

			<div class="row footer-filters clear">
					<div class="pull-left"><?php // Display pagination

					echo facetwp_display( 'pager' );?></div>

					<div class="pull-right"><?php  //echo facetwp_display( 'sort' ); ?></div>
			</div>
		</div>
					</div><!-- close .*-inner (main-content or sidebar, depending if sidebar is used) -->
		</div><!-- close .row -->
	</div><!-- close .container -->
<?php get_footer(); ?>