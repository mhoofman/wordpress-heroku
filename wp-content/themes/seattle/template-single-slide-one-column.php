<?php
/*
Template Name: Single Slide to One Column
*/
?>

<?php get_header(); ?>
			
	<div id="content">

		<div id="dsa-fullheight" class="bg-DSAred txt-DSAwhite">
			<div class="row">
				<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_slide0', true)); ?>
			</div>
			<div class="row">
				<div class="large-12 columns txt-center dsa-continue">
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