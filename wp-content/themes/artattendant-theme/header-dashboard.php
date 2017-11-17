<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title><?php wp_title( '|', true, 'right' ); ?></title>

<link rel="profile" href="http://gmpg.org/xfn/11" />
<?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IE versions. ?>
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/ie/html5.js" type="text/javascript"></script>
<![endif]-->
<link rel="icon" href="<?php echo get_template_directory_uri(); ?>/img/favicon.png" type="image/png" />
<?php wp_head(); ?>

<style>
/*
	body{
		background: url(<?php echo get_template_directory_uri(); ?>/img/temp-dash.jpg) top center no-repeat;
	}
*/
	</style>
</head>

<body <?php body_class(); ?>>
<nav class="navbar navbar-default main">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" class="navbar-brand"  rel="home"><?php echo anagramLoadFile(get_template_directory_uri()."/img/artattendant-logo.svg"); ?></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
       <!-- The WordPress Menu goes here -->
						        <?php wp_nav_menu(
					                array(
					                    'theme_location' => 'primary',
					                    'container_class' => 'navbar-inner',
					                    'container' => 0,
					                    'menu_class' => 'nav navbar-nav navbar-right',
					                    'fallback_cb' => '',
					                    'menu_id' => 'main-menu',
					                    'walker' => new wp_bootstrap_navwalker()
					                )
					            ); ?>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
