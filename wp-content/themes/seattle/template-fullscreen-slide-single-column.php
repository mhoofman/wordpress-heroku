<?php
/*
Template Name: Full Screen Slide (Single Column)
*/
?>

<?php get_header(); ?>
			
	<div id="content">

		<div id="dsa-fullheight" class="bg-DSAred txt-DSAwhite">
			<div class="row">
				<div class="large-6 medium-6 small-12 columns dsa-left-fullheight">
					<?php echo get_post_meta($post->ID, '_dsa_slide_left', true); ?>
				</div>
				<div class="large-6 medium-6 small-12 columns dsa-right-fullheight">
					<?php echo get_post_meta($post->ID, '_dsa_slide_right', true); ?>
				</div>
			</div>
			<div class="row">
				<div class="large-12 columns txt-center dsa-fullheight-cont">
					<a href="#inner-content" class="txt-DSAwhite button">Continue</a>
				</div>
			</div>
		</div>
	
		<div id="inner-content" class="row">
	
		    <main id="main" class="large-12 medium-12 columns" role="main">
				
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php get_template_part( 'parts/loop', 'page' ); ?>
					
				<?php endwhile; endif; ?>							

			</main> <!-- end #main -->
		    
		</div> <!-- end #inner-content -->
	
	</div> <!-- end #content -->

<?php get_footer(); ?>