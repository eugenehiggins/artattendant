<?php get_header(); ?>
	<?php if ( have_posts() ) : ?>

	<header class="page-header">
		<h1 class="page-title"><?php _e( 'News', 'anagram_coal' ); ?></h1>
	</header><!-- .page-header -->

		<?php /* Start the Loop */ ?>
		<?php while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>



	<div class="entry-summary row">
		<div class="col-md-3">
		<?php echo get_the_post_thumbnail(); ?>
		</div>
		<div class="col-md-9">
			<h3 class="page-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>

		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php the_time('m/d/Y'); // Display the time published ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
		<?php the_excerpt(); ?>
			<footer class="entry-meta">
		<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
			<div class="cat-links">
				<?php echo get_the_category_list(); // Display the categories this post belongs to, as links ?>
			</div>


		<?php endif; // End if 'post' == get_post_type() ?>
	</footer><!-- .entry-meta -->
		</div>
	</div><!-- .entry-summary -->

</article><!-- #post-## -->
		<?php endwhile; ?>
		<ul class="pager">
			<?php if ( get_next_posts_link() ) : ?>
			<li class="nav-previous previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'anagram_coal' ) ); ?></li>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<li class="nav-next next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'anagram_coal' ) ); ?></li>
			<?php endif; ?>
		</ul>
	<?php else : ?>

		<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php _e( 'Nothing Found', 'anagram_coal' ); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php if ( is_search() ) : ?>

			<p><?php _e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'anagram_coal' ); ?></p>
			<?php get_search_form(); ?>

		<?php else : ?>

			<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'anagram_coal' ); ?></p>
			<?php get_search_form(); ?>

		<?php endif; ?>
	</div><!-- .page-content -->
</section><!-- .no-results -->


	<?php endif; ?>
<?php get_footer(); ?>