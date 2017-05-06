<div id="dsa2of2" class="sidebar large-3 medium-6 columns" role="complementary">

	<?php if ( is_active_sidebar( 'dsa2of2' ) ) : ?>

		<?php dynamic_sidebar( 'dsa2of2' ); ?>

	<?php else : ?>

	<!-- This content shows up if there are no widgets defined in the backend. -->
						
	<div class="alert help">
		<p><?php _e( 'Please activate some Widgets.', 'jointswp' );  ?></p>
	</div>

	<?php endif; ?>

</div>