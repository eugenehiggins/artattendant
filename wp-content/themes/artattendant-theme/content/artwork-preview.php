<?php

	$post_id = isset( $_REQUEST['post_id'] ) && absint( $_REQUEST['post_id'] )  ? absint( $_REQUEST['post_id'] ) : -2;

/*
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
*/
	if ( !EDD_FES()->vendors->vendor_can_edit_product( $post_id ) ) {
	?>


		<div class="alert alert-danger">
			<strong>Error:</strong> Please go back to your dashboard
		</div>


	<?php }else{ ?>






		<div class="alert alert-warning">
		<strong>Your Private View</strong>
		  <a href="/collection/?task=edit-product&post_id=<?php echo $post_id; ?>"  class="alert-link pull-right"><i class="fa fa-edit fa-lg"></i> Edit <?php echo get_the_title($post_id); ?></a>
		</div>


		<div id="artwork" class="container-fluid switcher">
		<div class="col-sm-6 nopadding  artwork-image">
			<img class="original " src="<?php echo anagram_resize_image(array('width'=>877, 'crop'=>false, 'image_id'=> get_post_thumbnail_id( $post_id ), 'url'=> true )); ?>" >
		</div>
		<div class="col-sm-6 nopadding">
					<div id="no-more-tables-off" class="clearfix">

								<?php //echo anagram_get_public_artwork_info($post_id, true); ?>


		<?php $alt_images = get_post_meta( $post_id,'edd_image_uploader', true);
			$imgcount = count($alt_images);
			 ?>
			 			<?php  if($imgcount>1 ){ ?>
	 <div id="thumbs" class="switchimg">

	  <ul class="list-inline">

		<?php $count=0;foreach($alt_images as $mainimage){

		$timthumb_url =  wp_get_attachment_image_src( $mainimage , 'thumbnail');
		$full_url =  wp_get_attachment_image_src( $mainimage , 'large');
		if($count==0){$activeclass = 'activethumb';}else{$activeclass = '';};
		?>
		<li>
<a href="<?php echo $full_url[0]; ?>" attachment="<?php echo $full_url[0]; ?>" class="<?php echo $activeclass; ?>"><img src="<?php echo $timthumb_url[0]; ?>" width="60" height="60" alt="" /></a></li>
		 <?php  $count++;}; //end while ?>
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


							<?php

								//mapi_var_dump( get_post_meta( $post_id, 'size_type',true ) );

								//$meta = get_post_meta($post_id, '', true);
								//mapi_var_dump($meta);
 	?>
<?php echo anagram_get_private_artwork_info( $post_id ); ?>


		</div>
	</div>

<?php }; //end allow editing of works ?>