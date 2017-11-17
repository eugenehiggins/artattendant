<?php get_header(); ?>
		<div class="container main-content">
		<div class="row">
			<div id="content" class="main-content-inner col-sm-12">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="page-header">
				<h1 class="page-title"><?php the_title(); ?></h1>

				<div class="entry-meta">
					<?php the_time('m.d.Y'); // Display the time it was published ?>
					<?php // the author(); Uncomment this and it will display the post author ?>
				</div><!-- .entry-meta -->
			</header><!-- .entry-header -->

			<div class="entry-content">
				<?php if ( has_post_thumbnail() ) { ?>
				<?php //echo anagram_resize_image(array('width'=>877, 'height'=>360, 'crop'=>true)); ?>
				<?php //be_display_image_and_caption('large');
					the_post_thumbnail('large'); ?>
			<?php } ?>
				<?php the_content(); ?>
				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . __( 'Pages:', 'anagram_coal' ),
						'after'  => '</div>',
					) );
				?>
			</div><!-- .entry-content -->

			<footer class="entry-meta">
					<div class="cat-links"><?php echo get_the_category_list(', '); // Display the categories this post belongs to, as links ?></div>
					<div class="tags-links"><?php echo get_the_tag_list( 'Tags: ', ' | ' ); // Display the tags this post has, as links separated by spaces and pipes ?></div>
			</footer><!-- .entry-meta -->

					<ul class="pager"><?php previous_post_link( '<li class="nav-previous previous">%link</li>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'anagram_coal' ) . '</span> Previous' ); ?>
					<?php next_post_link( '<li class="nav-next next">%link</li>', 'Next <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'anagram_coal' ) . '</span>' ); ?></ul>
			
								</article><!-- #post-## -->
			<?php endwhile; // end of the loop. ?>

			</div><!-- close .*-inner (main-content or sidebar, depending if sidebar is used) -->
		</div><!-- close .row -->
	</div><!-- close .container -->
<?php get_footer(); ?>