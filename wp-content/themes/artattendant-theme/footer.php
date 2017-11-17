			</div><!-- close .*-inner (main-content or sidebar, depending if sidebar is used) -->
		</div><!-- close .row -->
	</div><!-- close .container -->



<footer id="footer" class="container hidden-xs">
		<div class="row">
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
				</div>

			<div id="backtotop">
			 <a id="toTop" href="#" onClick="return false"><i class="fa fa-chevron-up fa-lg"></i></a>
			</div>
-->
		</div>
</footer>
	<div class="scrollTop">
		 <a href="#main"><i class="fa fa-arrow-circle-up fa-lg" aria-hidden="true"></i></a>
		</div>
<?php wp_footer(); ?>

</body>
</html>