	<?php
	$vendor_announcement = EDD_FES()->helper->get_option( 'fes-dashboard-notification', '' );

	$unread_count = fep_get_new_message_number();

	if ( member_has_artworks() ) {


		?>

		<div class="row">
         	<div class="col-md-6">
				<?php	if ( $unread_count ) { ?>

				<div class="panel panel-default">
                  	<div class="panel-heading">
                  		Message Center
                	</div>
                  <div class="panel-body">
                	<?php _e('You have', 'front-end-pm');?> <a href="https://artattendant.com/messages/?task=messages"><?php printf(_n('%s unread message', '%s unread messages', $unread_count, 'front-end-pm'), number_format_i18n($unread_count) ). " " .__('and', 'front-end-pm');?></a> <span class="badge pull-right"><?php echo number_format_i18n($unread_count) ; ?></span>
                	</div>
				</div>
				<?php } ?>

              	<div class="panel dash-actions panel-default">
                  	<div class="panel-heading">
                    	<i class="icon icon-chevron-up chevron"></i>
                  		<i class="icon icon-wrench pull-right"></i> Quick Start
                	</div>
                  <div class="panel-content">

                      <div class="nav nav-justified">
                        <a href="<?php echo get_site_url(); ?>/collection/?task=new-product" class="btn btn-off col-xs-4">
                          <i class="fa fa-plus" aria-hidden="true"></i>
                          <p>Add Artwork</p>
                        </a>
                        <a href="<?php echo get_the_permalink(776); ?>" class="btn btn-off col-xs-4">
                          <i class="fa fa-comment" aria-hidden="true"></i>
                          <p>Connect</p>
                        </a>
                        <a href="<?php echo get_the_permalink(1051); ?>" class="btn btn-off col-xs-4">
                          <i class="fa fa-question-circle" aria-hidden="true"></i>
                          <p>Help</p>
                        </a>
                      </div>
                  </div><!--/panel content-->
              </div><!--/panel-->

				<div class="panel panel-default">
                  	<div class="panel-heading">
                  		Basic Stats
                	</div>
                  <div class="panel-body">
                  		<div class="widget-thumb dash-widgets dash-stats">
				  				<div class="dash-widget col-sm-6">
                                    <div class="dash-icon private-color"><i class="fa fa-eye-slash fa-lg" aria-hidden="true"></i></div>
                                    	<div class="dash-title">Private</div>
                                        <span class="counter" data-counter="counterup" data-value="<?php echo anagram_get_user_work_count('private'); ?>">0</span>
                                </div>

                                <div class="dash-widget col-sm-6">
                                    <div class="dash-icon public-color"><i class="fa fa-eye fa-lg" aria-hidden="true"></i></div>
                                    	<div class="dash-title">Public</div>
                                        <span class="counter" data-counter="counterup" data-value="<?php echo anagram_get_user_work_count('publish'); ?>">0</span>
                                </div>
                                <div class="dash-widget col-sm-6 visible-xs">
								<div class="dash-icon "><i class="fa fa-users fa-lg" aria-hidden="true"></i></div>
									<div class="dash-title">Artists</div>
									<span class="counter" data-counter="counterup" data-value="<?php $artworks = EDD_FES()->vendors->get_all_products( get_current_user_id(), array('publish',  'private' ) );
										if($artworks){ echo count(wp_get_object_terms( wp_list_pluck( $artworks, 'ID'), 'artist' ) ); }else{ echo '0';}; ?>">0</span>
								</div>
								<div class="dash-widget col-sm-6 visible-xs">
									<div class="dash-icon "><i class="fa fa-picture-o fa-lg" aria-hidden="true"></i></div>
									<div class="dash-title">Artworks</div>
									<span class="counter" data-counter="counterup" data-value="<?php echo anagram_get_user_work_count(array('publish', 'private' )); ?>">0</span>
								</div>
								<div class="dash-widget col-sm-6 visible-xs">
									<div class="dash-icon "><i class="fa fa-money fa-lg" aria-hidden="true"></i></div>
									<div class="dash-title">Total Cost</div>
									<span>$</span><span  class="counter" data-counter="counterup" data-value="<?php if (function_exists('count_total_cost')) echo count_total_cost(); ?>">0</span>
								</div>
								<div class="dash-widget col-sm-6 visible-xs">
									<div class="dash-icon "><i class="fa fa-money fa-lg" aria-hidden="true"></i></div>
									<div class="dash-title">Total Value</div>
									<span>$</span><span  class="counter" data-counter="counterup" data-value="<?php  if (function_exists('count_total_edd_price')) echo count_total_edd_price(); ?>">0</span>
								</div>
								<?php if(anagram_get_user_work_count('draft')!==0){ ?>
								<div class="dash-widget col-sm-12">
                                    <div class="dash-icon"><i class="fa fa-eye-slash fa-lg" aria-hidden="true"></i></div>
                                    	<div class="dash-title">Newly Imported</div>
                                    	<div class="dash-text">Please review newly imported artworks</div>
                                        <span class="counter" data-counter="counterup" data-value="<?php echo anagram_get_user_work_count('draft'); ?>">0</span>
                                </div>
								<?php } ?>
								<?php if(anagram_get_user_work_count('archive')!==0){ ?>
								<div class="dash-widget col-sm-12">
                                    <div class="dash-icon"><i class="fa fa-eye-slash fa-lg" aria-hidden="true"></i></div>
                                    	<div class="dash-title">Sold Artworks</div>

                                        <span class="counter" data-counter="counterup" data-value="<?php echo anagram_get_user_work_count('archive'); ?>">0</span>
                                </div>
								<?php } ?>
                  		</div>
                   </div><!--/panel content-->
              </div><!--/panel-->

		<?php	if ( 3==4 ) { ?>
              <div class="panel panel-default">
                  <div class="panel-heading">Report</div>
                  <table class="table table-striped">
                  <thead>
                    <tr><th>Col 1</th><th>Col 2</th><th>Col 3</th></tr></thead>
                  <tbody>
                    <tr><td>45</td><td>2.45%</td><td>Direct</td></tr>
                    <tr><td>289</td><td>56.2%</td><td>Referral</td></tr>
                    <tr><td>98</td><td>25%</td><td>Type</td></tr>
                    <tr><td>..</td><td>..</td><td>..</td></tr>
                    <tr><td>..</td><td>..</td><td>..</td></tr>
                  </tbody>
                  </table>
              </div><!--/panel-->
		<?php } ?>

          	</div>
        	<div class="col-md-6">
				<?php	if ( $vendor_announcement ) { ?>
				<div class="panel panel-default">
                  <div class="panel-heading">Member Notification</div>
                  <div class="panel-body">
                 <?php echo apply_filters( 'fes_dashboard_content', do_shortcode( $vendor_announcement ) ); ?>
                  </div>
              	</div>
              <?php } ?>

<!--
              	<div class="panel panel-default">
                	<div class="panel-heading">
                  		 <i class="fa fa-question pull-right" aria-hidden="true"></i>
                      	Mini Survey

                	</div>
                	<div class="panel-body">

                     <?php //echo do_shortcode('[gravityform id="6" title="false" description="false" ajax="true"]'); ?>

                  </div>
                </div>
-->



			</div><!--/col-span-6-->

      </div>



		<?php
	}else{ ?>

		<div id="fes-vendor-announcements text-center">
			<div class="upper"> Welcome to your dashboard, next step is to add some artwork</div>
			<a href="<?php echo get_site_url(); ?>/collection/?task=new-product" class="btn btn-default">Add Artworks</a>
		</div>


<?php	}
	?>

<!--
	<div id="fes-vendor-store-link">
		<?php echo EDD_FES()->vendors->get_vendor_store_url_dashboard(); ?>
	</div>
-->

<!--
	<div class="fes-comments-wrap">
		<table id="fes-comments-table">
			<tr>
				<th class="col-author"><?php  _e( 'Author', 'edd_fes' ); ?></th>
				<th class="col-content"><?php  _e( 'Comment', 'edd_fes' ); ?></th>
			</tr>
			<?php echo EDD_FES()->dashboard->render_comments_table( 10 ); ?>
		</table>
	</div>
-->
