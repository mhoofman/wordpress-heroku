<?php
/*
Template Name: Three Slides to One to Two Columns
*/
?>

<?php get_header(); ?>
			
	<div id="content">

		<div class="orbit" role="region" aria-label="Slides" data-orbit>
			<ul class="orbit-container">
				<button class="orbit-previous"><span class="show-for-sr">Previous Slide</span>&#9664;&#xFE0E;</button>
   				<button class="orbit-next"><span class="show-for-sr">Next Slide</span>&#9654;&#xFE0E;</button>
				<li class="is-active orbit-slide dsa-slide dsa-slide-1">
					<div class="row padslidetop">
						<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_slide1', true)); ?>
					</div>

				</li><!-- end slide one -->
				<li class="orbit-slide dsa-slide dsa-slide-2">
					<div class="row padslidetop">
						<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_slide2', true)); ?>
					</div>
					
				</li><!-- end slide two -->
				<li class="orbit-slide dsa-slide dsa-slide-3">
					<div class="row padslidetop">
							<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_slide3', true)); ?>
					</div>

				</li><!-- end slide three -->
			</ul>

			<nav class="orbit-bullets">
	    		<button class="is-active" data-slide="0"><span class="show-for-sr">First slide details.</span><span class="show-for-sr">Current Slide</span></button>
	   			<button data-slide="1"><span class="show-for-sr">Second slide details.</span></button>
	    		<button data-slide="2"><span class="show-for-sr">Third slide details.</span></button>
	  		</nav> <!-- end of orbit navigation-->
		</div> <!-- end of orbit region / dsa fullheight -->
	
		<div id="inner-content" class="row">
	
		    <main id="main" class="large-12 medium-12 columns dsa-main" role="main">
				
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php get_template_part( 'parts/loop', 'page' ); ?>
					
				<?php endwhile; endif; ?>							

			</main> <!-- end #main -->

			<div class="large-6 medium-6 columns dsa-column-left">
				<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_column_left', true)); ?>
			</div>

			<div class="large-6 medium-6 columns dsa-column-right">
				<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_column_right', true)); ?>
			</div>
		    
		</div> <!-- end #inner-content -->
	
	</div> <!-- end #content -->

<?php get_footer(); ?>