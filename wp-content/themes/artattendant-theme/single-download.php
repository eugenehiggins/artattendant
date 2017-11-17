<?php get_header(); ?>

	<?php while ( have_posts() ) : the_post();



		 ?>

	<div id="artwork" class="container switcher">
	<div class="loading" ><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>
		<div class="col-sm-6 nopadding  artwork-image">
			<img class="original " src="<?php echo anagram_resize_image(array('width'=>877, 'crop'=>false, 'image_id'=> get_post_thumbnail_id( $post->ID ), 'url'=> true )); ?>" >
		</div>
		<div class="col-sm-6 nopadding artwork-info">

			<?php //the_content(); ?>
					<div class="description description--artwork  fadeup">

							<?php echo anagram_get_public_artwork_info(get_the_ID()); ?>

							<div style="color:#656565; padding:5px 0px 0;">
								<?php if(  edd_get_download_price( get_the_ID() ) != 0 && (anagram_artwork_status( get_the_ID() ) ==1 || anagram_artwork_status( get_the_ID() ) ==3 ) ){
								echo 'Retail Price $'.edd_get_download_price( get_the_ID() ).'<br/>';
								echo edd_get_purchase_link( array( 'download_id' => get_the_ID(),'price' => false, 'direct'=> false, 'text' => 'Purchase', 'class'=>'btn btn-primary btn-sm') );
								};?></div>


						<ul class="links">
							<li><span class="showhide" data-toshow="extra-details">Extra Details</span></li>
							<?php  if( anagram_artwork_status( get_the_ID() ) ) { ?><li><span class="showhide" data-toshow="request">Request Info</span></li><?php };  ?>
							<li><span class="showhide" data-toshow="share">Share</span></li>
						</ul>

						<div id="share" class="share toshow"></div>

				<div id="request" class="toshow">
					<?php include('front-end-pm/newmessage_form.php'); ?>
				</div>
			<?php $alt_images = get_post_meta( get_the_ID(),'edd_image_uploader', true);
				$imgcount = count($alt_images);
				 ?>
				<?php  if($imgcount>1 ){ ?>
				<div id="thumbs" class="switchimg  fadeup">
				  	<ul class="list-inline">

						<?php $count=0;foreach($alt_images as $mainimage){

							$timthumb_url =  wp_get_attachment_image_src( $mainimage , 'thumbnail');
							$full_url =  wp_get_attachment_image_src( $mainimage , 'large');
							if($count==0){$activeclass = 'activethumb';}else{$activeclass = '';};
							?>
								<li>
									<a href="<?php echo $full_url[0]; ?>" attachment="<?php echo $full_url[0]; ?>" class="<?php echo $activeclass; ?>"><img src="<?php echo $timthumb_url[0]; ?>" width="60" height="60" alt="" /></a>
								</li>
						<?php  $count++;
							}; //end while ?>
				    </ul>

				</div>
				    <script>

					//Wait till load
						jQuery(function($) {

							jQuery(".switchimg li a").click( function() {
								jQuery(".switchimg li a").removeClass('activethumb');
								jQuery(this).addClass('activethumb');
								var changeSrc = jQuery(this).attr("href");
								var changeSrcLg = jQuery(this).attr("attachment");
								jQuery(".artwork-image img").attr("src", changeSrc);
								jQuery("a.lightbox").attr("href", changeSrcLg);
								return false;
							});
						});

					</script>

			<?php  };//if images ?>



			</div>
		</div>
	</div>



	<div class="container bottom-content">
			<div class="fadeup delay">
	<h3 class="upper">Other works<!--  by <?php echo get_custom_taxonomy('artist', ' ', 'name' ); ?> --></h3>
			<div class="row">
				<?php

					//$category = get_the_category();
				//$firstCategory = $category[0]->cat_name;

				$args = array(
					   		'post_type' => 'download',
					    	'post_status' => 'publish',
					    	'posts_per_page' => 4,
					    	'post__not_in' => array(get_the_ID() ),
					    	'orderby' => 'rand'
				/*
					    	'tax_query' => array(
							array(
								'taxonomy' => 'category',
								'field'    => 'slug',
								'terms'    => $firstCategory,
							),
						)
				*/
				);

			$my_query = new WP_Query( $args );
			if ( $my_query->have_posts() ):

				while ( $my_query->have_posts() ) : $my_query->the_post(); ?>
							<div class="col-md-3">
								<a href="<?php the_permalink(); ?>"><?php  $att_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "medium"); ?><img src="<?php echo $att_image[0];?>" width="<?php echo $att_image[1];?>" height="<?php echo $att_image[2];?>"  class="attachment-medium" alt="<?php $post->post_excerpt; ?>" />
				</a>
								<div class="art-info">
									<h4><?php echo get_custom_taxonomy('artist', ' ', 'name', get_the_ID() ); ?></h4>
									<h5><em><?php the_title(); ?></em></h4>
									<div class="row"><div class="col-xs-6"><a href="<?php the_permalink(); ?>" class="more-link">view more //</a></div><div class="col-xs-6 text-right"><?php echo anagram_output_artwork_status( get_the_ID() ); ?></div></div>

								</div>
							</div>

				<?php endwhile; ?>
			<?php endif; ?>
			 <?php wp_reset_postdata(); ?>
			</div>
	</div>
</div>




	<?php endwhile; // end of the loop. ?>




<?php get_footer(); ?>