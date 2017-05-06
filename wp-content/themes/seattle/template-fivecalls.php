<?php
/*
Template Name: Five Calls
*/
?>

<?php get_header(); ?>
			
	<div id="content">
	
		<div id="inner-content" class="row">

			<div class="large-4 medium-4 columns bg-DSAwhite">
				<div>
					the_logo
				</div> <!-- end Branding / Location -->
				<div>
					<h2>the_issue</h2>
				</div> <!-- end Issue / Call # -->
			</div><!--end of upper left box -->
	
		    <main id="main" class="large-8 medium-8 columns bg-DSAwhite" role="main">
				
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php get_template_part( 'parts/loop', 'page' ); ?>
					
				<?php endwhile; endif; ?>

				<div class="row">
					<div class="large-4 medium-4 small=3 columns">
						contact_img
					</div>
					<div class="large-8 medium-8 small=9 columns">
						<h2>Call this office:</h2>
						<p class="5calls-contact">the_contact</p>
					</div>
				</div>		

				<div class="row">
					<p class="5calls-phone">the_phone</p>
					<h2>Why You're Calling</h2>
					<p>the_reason</p>
					<h2>Your Script:</h2>
					<blockquote>the_script</blockquote>
					<h2>Enter your call result to get next call</h2>
					<div class="button-group">
					  <a class="alert button">Unvailable</a>
					  <a class="success button">Left_Voicemail</a>
					  <a class="success button">Made_Contact</a>
					  <a class="warning button">Skip</a>
					</div>
					<p>the_callsleft Calls left on issue | Tweet this Issue | Share this issue</p>
				</div>		

			</main> <!-- end #main -->
		    
		</div> <!-- end #inner-content -->
	
	</div> <!-- end #content -->

<?php get_footer(); ?>
