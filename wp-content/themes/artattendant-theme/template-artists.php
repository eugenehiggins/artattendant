<?php /* Template Name: Artists */
get_header(); ?>
	<div class="container main-content">
		<div class="row">
			<div id="content" class="main-content-inner col-sm-12">
			        <div class="content">
			            <div class="container-fluid">
			                <div class="row">
			                    <div class="col-md-12">

			                        <div class="card">
				                        <div class="content">
									        <?php
												echo '<ul class="list-unstyled" style="column-count: 3;">';
												foreach( get_terms( 'artist', array( 'hide_empty' => true ) ) as $parent_term ) {
											  // display top level term name
											 echo '<li>';
											  echo '<a href="'.get_term_link( $parent_term ).'">'.$parent_term->name.'</a>';

											}
											 echo '</ul>';

											 ?>
				                        </div>
				                    </div>
								</div>
			                </div>
			            </div>
			        </div>

			</div><!-- close .*-inner (main-content or sidebar, depending if sidebar is used) -->
		</div><!-- close .row -->
	</div><!-- close .container -->
<?php get_footer(); ?>