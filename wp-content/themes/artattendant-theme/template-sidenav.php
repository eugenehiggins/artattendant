<?php
	/*
	Template Name: Side Nav
*/

get_header(); ?>
<div class="container">
	<div class="col-sm-12">
			<?php while ( have_posts() ) : the_post(); ?>
					<div  class="entry-content">
					<header class="page-header">
				<h1 class="page-title"><?php the_title(); ?></h1>
			</header><!-- .entry-header -->

						<?php the_content(); ?>
					</div><!-- .entry-content -->
			<?php endwhile; // end of the loop. ?>
	</div>
</div>

<?php get_footer(); ?>




