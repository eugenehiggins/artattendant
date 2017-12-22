</div><!-- close .row -->
</div><!-- close .container -->

<footer id="footer" class="container-fluid">
    <div class="container">
        <div class="row col-md-8 col-md-offset-2">
            <?php wp_nav_menu(
                array(
                    'container' => 0,
                    'menu_class' => 'nav navbar-nav navbar',
                    'fallback_cb' => '',
                    'menu' => 176,
                    'walker' => new wp_bootstrap_navwalker()
                )
            ); ?>
            <!--
			<div class="site-info clearfix ">
					<div class="copyright col-xs-6"> &copy;<?php echo bloginfo('title'); ?>  <?php echo date('Y'); ?> </div>
					<div class="site-credit  col-xs-6"> Powered by <a href="http://anagr.am" target="_blank"><img src="<?php bloginfo('template_directory'); ?>/img/anagram/anagram-logo.png" alt="Anagram"  /></a></div>
				</div>-->
            <div class="wrapper">
                <div class="icon">
                    <img class="img-responsive" src="<?php bloginfo('template_url'); ?>/img/frontpage/artAttendant-outlines-square.png" alt="Nancy Testimony">
<!--                    <div class="svg-container">-->
<!--                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"-->
<!--                             viewBox="0 0 86 85">-->
<!--                            <title>-->
<!--                                Asset 1</title>-->
<!--                            <g id="Layer_2" data-name="Layer 2">-->
<!--                                <g id="Footer">-->
<!--                                    <g id="artAttendant-outlines-square_Image"-->
<!--                                       data-name="artAttendant-outlines-square Image">-->
<!--                                        <image id="artAttendant-outlines-square_Image-2"-->
<!--                                               data-name="artAttendant-outlines-square Image"-->
<!--                                               width="86" height="85"-->
<!--                                               xlink:href="data:image/png;base64,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"/>-->
<!--                                    </g>-->
<!--                                </g>-->
<!--                            </g>-->
<!--                        </svg>-->
<!--                    </div>-->
                </div>
                <div class="contact">
                    <div>505.321.3182 | <a href="mailto:connect@artAttendant.com">connect@artAttendant.com</a></div>
                    <div>2017 artAttendant. All rights reserved</div>
                </div>
                <div class="social">

                    <a href="https://www.facebook.com/artAttendant/" target="_blank"><span class="fa-stack fa-lg">
                      <i class="fa fa-circle fa-stack-2x"></i>
                      <i class="fa fa-facebook fa-stack-1x fa-inverse" aria-hidden="true"></i>
                    </span></a>
                    <a href="https://www.instagram.com/artattendant/" target="_blank"><span class="fa-stack fa-lg">
                      <i class="fa fa-circle fa-stack-2x"></i>
                      <i class="fa fa-instagram fa-stack-1x fa-inverse" aria-hidden="true"></i>
                    </span></a>

                </div>
            </div>
            <div id="backtotop">
                <a id="toTop" href="#" onClick="return false"><i class="fa fa-chevron-up fa-lg"></i></a>
            </div>

        </div>
    </div>
</footer>


<?php wp_footer(); ?>

</body>
</html>