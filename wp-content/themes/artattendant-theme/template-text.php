<?php
	/*
	Template Name: Text Template
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
											<?php
				 $faqs = get_field('faqs'); ?>
					<div class="faq_container">
					<?php	if($faqs)
						{
							foreach($faqs as $faq )
							{ ?>

							   <div class="faq">
							      <h3 class="faq_question"><?php echo $faq['question']; ?></h3>
							           <div class="faq_answer_container">
							              <div class="faq_answer"><div class="faq_text"><?php echo $faq['answer']; ?></div></div>
							           </div>
							    </div>


							<?php } ?>
					 </div>
					<?php } ?>

					</div><!-- .entry-content -->
			<?php endwhile; // end of the loop. ?>
	</div>
</div>

<?php get_footer(); ?>




