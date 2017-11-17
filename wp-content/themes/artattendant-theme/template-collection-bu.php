<?php /*
	
	Template Name: Custom submission
*/
get_header(); ?>
<div class="row">
  <div class="col-sm-3">
    <div class="sidebar-nav">
      <div class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <span class="visible-xs navbar-brand">Sidebar menu</span>
        </div>
        <div class="navbar-collapse collapse sidebar-navbar-collapse">
                  <?php wp_nav_menu(
					                array(
					                    'theme_location' => 'user_nav',
					                    'container_class' => 'navbar-inner',
					                    'container0' => 0,
					                    'menu_class' => 'nav navbar-nav',
					                    'fallback_cb' => '',
					                    'menu_id' => 'user-menu',
					                    'walker' => new wp_bootstrap_navwalker()
					                )
					            ); ?>
        </div><!--/.nav-collapse -->
      </div>
    </div>
  </div>
  <div class="col-sm-9">
			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="page-header">
						<h1 class="page-title"><?php the_title(); ?></h1>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<?php the_content(); ?>
						<?php
							wp_link_pages( array(
								'before' => '<div class="page-links">' . __( 'Pages:', 'anagram_coal' ),
								'after'  => '</div>',
							) );
						?>
					</div><!-- .entry-content -->
				</article><!-- #post-## -->
			<?php endwhile; // end of the loop. ?>
  </div>
</div>
<?php get_footer(); ?>