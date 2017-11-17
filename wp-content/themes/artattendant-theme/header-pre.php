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

body {
    background: #FFF;
}

	</style>
</head>

<body <?php body_class(); ?>>
<nav class="navbar navbar-default main-off">
  <div class="container-fluid">
  		<?php if(is_front_page() ){ ?> <ul id="main-menu" class="nav navbar-nav navbar-right">
  			<li id="menu-item-24" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-login"><a href="<?php echo get_the_permalink(314); ?>">Login/Join</a></li>
		</ul><?php }; ?>
  </div><!-- /.container-fluid -->
</nav>
<div class="container-fluid main-content">
	<div class="row">
