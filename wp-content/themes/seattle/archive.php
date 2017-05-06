<?php get_header(); ?>
			
	<div id="content">
	
		<div id="inner-content" class="row">
		
		    <main id="main" class="large-8 medium-8 columns" role="main">
			    
		    	<header>
		    		<h1 class="page-title"> <?php single_term_title(); ?> </h1>
					<?php the_archive_description('<div class="taxonomy-description">', '</div>');?>
		    	</header>
		    	<div class="button-group expanded"><!-- Chapter and Category Specific Subnavigation -->
					<a href="http://seattledsa.org/dispatches/" class="button">All</a>
					<a href="http://seattledsa.org/dispatches/actions/" class="button">Actions</a>
					<a href="http://seattledsa.org/dispatches/education/" class="button">Education</a>
					<a href="http://seattledsa.org/dispatches/minutes/" class="button">Minutes</a>
				</div>
				<hr />
		    	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			 
					<!-- To see additional archive styles, visit the /parts directory -->
					<?php get_template_part( 'parts/loop', 'archive' ); ?>
				    
				<?php endwhile; ?>	

					<?php joints_page_navi(); ?>
					
				<?php else : ?>
											
					<?php get_template_part( 'parts/content', 'missing' ); ?>
						
				<?php endif; ?>
		
			</main> <!-- end #main -->
	
			<?php get_sidebar(); ?>
	    
	    </div> <!-- end #inner-content -->
	    
	</div> <!-- end #content -->

<?php get_footer(); ?>