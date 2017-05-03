<?php
/*
Template Name: Hompage v1
*/
?>

<?php get_header(); ?>

	<div id="content">

		<div id="dsa-home-row-1">
			<div  class="row ease dsa-home-row-3-edit" data-animate="fade-in fade-out">
				<div class="large-6 medium-4 small-12 columns home-whitespace">
					&nbsp;
				</div>
				<div id="inner-content" class="large-6 medium-8 small-12 columns card-gray bdr-stripe-gray" aria-expanded="true">
					<main id="main" role="main">
						<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
							<?php get_template_part( 'parts/loop', 'pagealt' ); ?> 
						<?php endwhile; endif; ?>		
					</main> <!-- end #main -->					
				</div>
				<div class="large-6 medium-6 small-12 columns card-gray bdr-stripe-red">
					<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_feature_box', true)); ?>
				</div><!-- End DSA Feature Box -->
				<div class="large-6 medium-6 small-12 columns card-red bdr-stripe-black">
					<div class="orbit eva-orbit" role="region" aria-label="Slides" data-orbit>
						<ul class="orbit-container">
							<button class="orbit-previous"><span class="show-for-sr">Previous Slide</span>&#9664;&#xFE0E;</button>
			   				<button class="orbit-next"><span class="show-for-sr">Next Slide</span>&#9654;&#xFE0E;</button>
							<li class="is-active orbit-slide dsa-eva-slide dsa-eva-slide-1">
								<div class="txt-center txt-white">
									<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_slide1', true)); ?>
								</div>

							</li><!-- end slide one -->
							<li class="orbit-slide dsa-eva-slide dsa-eva-slide-2">
								<div class="txt-center txt-white">
									<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_slide2', true)); ?>
								</div>
								
							</li><!-- end slide two -->
							<li class="orbit-slide dsa-eva-slide dsa-eva-slide-3">
								<div class="txt-center txt-white">
										<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_slide3', true)); ?>
								</div>

							</li><!-- end slide three -->
						</ul>

						<nav class="orbit-bullets">
				    		<button class="is-active" data-slide="0"><span class="show-for-sr">First slide details.</span><span class="show-for-sr">Current Slide</span></button>
				   			<button data-slide="1"><span class="show-for-sr">Second slide details.</span></button>
				    		<button data-slide="2"><span class="show-for-sr">Third slide details.</span></button>
				  		</nav> <!-- end of orbit navigation-->
					</div> <!-- end of orbit region -->
				</div><!-- end DSA Slides -->
			</div><!-- end #inner-content -->
		</div>
	
		<div id="dsa-home-row-2" class="bg-DSAred">
			<div class="row dsa-home-row-2-edit">
				<div class="text-center"><?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_homepage_row_2', true)); ?></div>
				<?php // Retrieve the next 5 upcoming events
					$events = tribe_get_events( array(
					    'posts_per_page' => 2,
					    'start_date' => date( 'Y-m-d H:i:s' ),
					    'tax_query'=> array(
		                	array(
			                    'taxonomy' => 'tribe_events_cat',
			                    'field' => 'slug',
			                    'terms' => 'general'
		               		)
		                )
					) );
					 
					// Loop through the events, displaying the title
					// and content for each
					foreach ( $events as $event ) {
					    echo "<div class=\"card-gray bdr-stripe-red-left\"><h4 class=\"txt-bold\">";
					    echo tribe_get_event_link( $event->ID, $full_link=true);
						echo "</h4><p>";
					    echo tribe_events_event_schedule_details( $event->ID );
					    echo "<br>";
					    echo tribe_get_venue_single_line_address ( $event->ID, $link = false );
					    echo "  ";
					    echo tribe_get_map_link_html ( $event->ID  );
					    echo "</p><p>";
					    echo $event->post_content;
					    echo "</p><a href=\"";
					    echo tribe_get_event_link ( $event->ID  );
					    echo "\">Find out more &rsaquo;</a></div><br>";
					}

					echo "<div class=\"text-center\"><a class=\"button\" href=\"";
					echo tribe_events_get_list_widget_view_all_link ();
					echo "\">See All</a></div>";
				?>
			</div>
		</div> 

		<div id="dsa-home-row-3" class="bg-dark-1">
			<div class="row dsa-home-row-3-edit text-center txt-white">
				<?php echo apply_filters('the_content', get_post_meta($post->ID, '_dsa_homepage_row_3', true)); ?>
			</div>
			<div class="row">
				<?php
					$how_many_last_posts = intval(get_post_meta($post->ID, 'archived-posts-no', true));

					/* Here, we're making sure that the number fetched is reasonable. In case it's higher than 200 or lower than 2, we're just resetting it to the default value of 15. */
					if($how_many_last_posts > 200 || $how_many_last_posts < 2) $how_many_last_posts = 2;

					$my_query = new WP_Query('post_type=post&nopaging=1');
					if($my_query->have_posts()) {
					  echo '<div class="archives-latest-section">';
					  $counter = 1;
					  while($my_query->have_posts() && $counter <= $how_many_last_posts) {
					    $my_query->the_post(); 
					    ?>
					    <div class="large-6 medium-6 small-12 columns"><div class="large-12 medium-12 small-12 columns card-gray bdr-stripe-black"><h4 class="txt-bold"><a href="<?php the_permalink() ?>" rel="bookmark" title="Read <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h4><br/><b>By <?php the_author() ?> / <?php the_time('F j, Y') ?></b></div></div>
					    <?php
					    $counter++;
					  }
					  echo '</div>';
					  wp_reset_postdata();
					}
					?>
			</div><br>
			<div class="row text-center">
				<?php
   					// Get the ID of a given category
   					$category_id = get_cat_ID( 'dispatches' );
 
				    // Get the URL of this category
				    $category_link = get_category_link( $category_id );
				?>
 
				<!-- Print a link to this category -->
				<a href="<?php echo esc_url( $category_link ); ?>" class="button" title="Dispatches">See All</a>
			</div>
		</div>
	
	</div> <!-- end #content -->

<?php get_footer(); ?>