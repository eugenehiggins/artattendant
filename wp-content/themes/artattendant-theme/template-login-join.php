<?php
	/*
	Template Name: Login/Join Template
*/
redirect_login_to_collections();
get_header('pre'); ?>
<div class="container">
	<div class="col-sm-12">

	<?php if( ! is_user_logged_in() ) : endif; ?>
	    <div class="join-login-header">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"  rel="home"><img src="<?php echo get_template_directory_uri(); ?>/img/artattendant-logo-trans.png"  alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"></a>
    </div>
			<?php while ( have_posts() ) : the_post(); ?>
					<div  class="entry-content">
						<?php the_content(); ?>
					</div><!-- .entry-content -->
			<?php endwhile; // end of the loop. ?>
	</div>
</div>
<?php get_footer('pre'); ?>