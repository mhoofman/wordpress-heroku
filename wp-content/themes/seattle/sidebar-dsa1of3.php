<div id="dsa1of3" class="sidebar large-4 medium-4 small-12 columns" role="complementary">

	<?php if ( is_active_sidebar( 'dsa1of3' ) ) : ?>

		<?php dynamic_sidebar( 'dsa1of3' ); ?>

	<?php else : ?>

	<!-- This content shows up if there are no widgets defined in the backend. -->
						
	<div class="alert help">
		<p><?php _e( '&nbsp;', 'jointswp' );  ?></p>
	</div>

	<?php endif; ?>

</div>