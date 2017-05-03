<?php
/*
Template Name: Two Column (No Sidebar)
*/
?>

<?php get_header(); ?>

	<div id="content">

		<div id="inner-content" class="row">
			<div class="large-112 medium-12 small-12 columns">
				<h1><?php the_title(); ?></h1>
			</div>
		    <main id="main" class="large-6 medium-6 small-12 columns" role="main">
				
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php get_template_part( 'parts/loop', 'pagealt' ); ?>
					
				<?php endwhile; endif; ?>							

			</main> <!-- end #main -->
		    
		    <div class="large-6 medium-6 small-12 columns dsa-right-fullheight">
					<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_column_right', true)); ?>
			</div>

		</div> <!-- end #inner-content -->
	
	</div> <!-- end #content -->

<?php get_footer(); ?>