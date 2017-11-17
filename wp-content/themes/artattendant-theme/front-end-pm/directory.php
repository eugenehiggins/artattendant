<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="fep-directory-search-form-div">
	<form id="fep-directory-search-form"  class="form-inline" action="">
	<div class="form-group">
		<div class="input-group">
			<input type="hidden" name="fepaction" value="directory" />
			<input type="text" name="search" class="fep-directory-search-form-field form-control" value="<?php isset( $_GET["search"] ) ? esc_attr_e( $_GET["search"] ): ""; ?>" />
			<input type="hidden" name="feppage" value="1" />
		</div>
			<input type="submit" class="fep-directory-search-form-submit btn btn-default" value="<?php _e("Search Users", "front-end-pm"); ?>" />
		
	</div>
	</form>
</div>
<?php 
if (! empty( $user_query->results)){ ?>
  
	<div class="fep-table fep-odd-even">
		
     <span class="fep-table-caption"><?php _e("Total Users", "front-end-pm"); ?>: (<?php echo number_format_i18n($total); ?>)</span>
		
      <?php foreach( $user_query->results as $u ){ ?>
		  <div class="fep-table-row">
		  <div class="fep-column"><?php echo get_avatar($u->ID, 64); ?></div>
		  <div class="fep-column"><?php esc_html_e( $u->display_name ); ?></div>
		  <div class="fep-column"><a href="<?php echo fep_query_url( "newmessage", array( "to" => $u->user_nicename)); ?>"><?php _e("Send Message", "front-end-pm"); ?></a></div>
		</div>
      <?php } ?>
	 </div>
	 <?php echo fep_pagination( $total, fep_get_option('user_page', 50 ) ); ?>

<?php } else { ?>
  <div class='fep-error'><?php _e("No users found.", 'front-end-pm'); ?></div>
<?php }
