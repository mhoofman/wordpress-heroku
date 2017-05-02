<?php
/*
Template Name: Single Slide to One to Two Column
*/
?>

<?php get_header(); ?>

	<div id="content">

		<div class="bg-dark-1 txt-DSAwhite dsa-slide dsa-slide-0">
			<div class="row">
					<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_slide0', true)); ?>
			</div>
		</div>
	
		<div id="inner-content" class="row ease" data-animate="fade-in fade-out" aria-expanded="true">
	
		    <main id="main" class="large-12 medium-12 columns" role="main">
				
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php get_template_part( 'parts/loop', 'pagealt' ); ?>
					
				<?php endwhile; endif; ?>							

			</main> <!-- end #main -->
		    
		</div> <!-- end #inner-content -->

		<div id="dsa-twin-columns" class="row">
			<div class="large-6 medium-6 small-12 columns dsa-column-left-wrapper">
					<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_column_left', true)); ?>
				</div>
				<div class="large-6 medium-6 small-12 columns dsa-column-right-wrapper">
					<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_column_right', true)); ?>
			</div>
		</div>
	
	</div> <!-- end #content -->

<?php get_footer(); ?>