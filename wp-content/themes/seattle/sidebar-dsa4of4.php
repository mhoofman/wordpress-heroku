<div id="dsa4of4" class="sidebar large-3 medium-6 columns" role="complementary">

	<?php if ( is_active_sidebar( 'dsa4of4' ) ) : ?>

		<?php dynamic_sidebar( 'dsa4of4' ); ?>

	<?php else : ?>

	<!-- This content shows up if there are no widgets defined in the backend. -->
						
	<div class="alert help">
		<p><?php _e( ' ', 'jointswp' );  ?></p>
	</div>

	<?php endif; ?>

</div>