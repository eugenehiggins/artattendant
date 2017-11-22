<?php
redirect_login_to_collections();
get_header('pre'); ?>
<div class="container main-content">
    <div class="row">
        <header class="col-sm-12">
            <div class="landing-logo"><?php echo anagramLoadFile(get_template_directory_uri() . "/img/artattendant-logo.svg"); ?></div>
            <div class="landing-intro-text">
                <div class="button-block text-center">
                    <a href="<?php echo get_the_permalink(85); ?>" class="btn btn-default btn-aqua btn-lg">Discover
                        Art</a>
                    <a href="<?php echo get_the_permalink(20); ?>" class="btn btn-default btn-aqua btn-lg">Organize
                        Art</a>
                    <a href="<?php echo get_the_permalink(20); ?>" class="btn btn-default btn-aqua btn-lg">Archive
                        Services</a>
                </div>
                <div class="text">
                    <h1>Free Unlimited Art Archive</h1>
                    <strong>We believe in connecting the vibrant and diverse worldwide arts community.</strong>
                </div>


            </div><!-- .entry-content -->
        </header><!-- close .*-inner (main-content or sidebar, depending if sidebar is used) -->
    </div><!-- close .row -->
</div><!-- close .container -->

<section class="container-fluid section2">
    <div class="row">
        <div class="item">


            <span class="fa-stack fa-5x">
              <i class="fa fa-circle fa-stack-2x"></i>
              <i class="fa fa-cloud-upload fa-stack-1x fa-inverse"></i>
            </span>

            <h3>
                Upload and organize unlimited art for free
            </h3>
            <span>
            We offer a free, secure, easy-to-use, cloud-based <a href="<?php echo get_the_permalink(85); ?>">artwork inventory database</a> to document, manage and track art collections. Our members can choose to make their art collection public or keep it private. Collection management, insurance valuation, estate planning, publishing archive records and social sharing are all incorporated into our intuitive online platform.
        </span>
        </div>
        <div class="item">
            <span class="fa-stack fa-5x">
              <i class="fa fa-circle fa-stack-2x"></i>
              <i class="fa fa-shopping-cart fa-stack-1x fa-inverse"></i>
            </span>
            <h3>
                Buy, sell, rent or loan art on our marketplace
            </h3>
            <span>
            Promote artwork for sale, rent or loan in our public online art marketplace. Buy art directly from other artAttendant community members. artAttendant is one of the most affordable ways to sell artwork online as we only charge a 10% commission and 3% credit card processing fees. We have no up-front costs, and no membership fees for displaying artwork for sale, rent or loan.
        </span>
        </div>
        <div class="item">
            <span class="fa-stack fa-5x">
              <i class="fa fa-circle fa-stack-2x"></i>
              <i class="fa fa-archive fa-stack-1x fa-inverse"></i>
            </span>
            <h3>
                Low-cost professional art archive services
            </h3>
            <span>
            Archived art collections have greater value than undocumented collections. Art Attendant was developed by professional art dealer and archivist with over 25 years of experience. If your collection needs help and more personalized service, our art archive services start as low as $25/hour. Contact us to discuss your collection and develop a customized affordable solution.
        </span>
        </div>
    </div>

</section>

<!--<div class="container-fluid">-->
<!--    <div class="container">-->
<!--        <div class="row">-->
<!--            <div class="col-sm-12">-->
<!--                <div class="about-block">-->
<!--                    --><?php //while (have_posts()) : the_post(); ?>
<!--                        --><?php //the_content(); ?>
<!---->
<!---->
<!--                        --><?php ////echo do_shortcode('[gravityform id="4" title="true" description="false" ajax="true"]'); ?>
<!---->
<!--                    --><?php //endwhile; // end of the loop. ?>
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

<section class="container-fluid section3">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="audiences">
                    <h2 class="col-12">Serving the Arts Community</h2>
                    <div class="col-xs-12 col-sm-6 col-lg-8">
                        <div id="artists" class="audience">
                            Artists
                            <img src="<?php bloginfo('template_url'); ?>/img/frontpage/artists.jpg" alt="Artists">

                            <span>
                                Upload, organize, promote and sell your artwork on artAttendant. Get organized today and be rewarded with a free home for your digital archive.  We only charge 10% commission and 3% credit card processing fees on art sales with no up-front fees or monthly subscription costs. Our site is intuitive and does not have complicated features or cumbersome interfaces.
                            </span>
                        </div>
                        <div></div>
                    </div>
                    <div class="col-xs-6 col-lg-4">col 2</div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php get_footer('pre'); ?>

