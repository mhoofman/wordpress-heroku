<?php

// Fire all our initial functions at the start
add_action('after_setup_theme','joints_start', 16);

function joints_start() {

    // launching operation cleanup
    add_action('init', 'joints_head_cleanup');
    
    // remove pesky injected css for recent comments widget
    add_filter( 'wp_head', 'joints_remove_wp_widget_recent_comments_style', 1 );
    
    // clean up comment styles in the head
    add_action('wp_head', 'joints_remove_recent_comments_style', 1);
    
    // clean up gallery output in wp
    add_filter('gallery_style', 'joints_gallery_style');
    
    // adding sidebars to Wordpress
    add_action( 'widgets_init', 'joints_register_sidebars' );
    
    // cleaning up excerpt
    add_filter('excerpt_more', 'joints_excerpt_more');

} /* end joints start */

//The default wordpress head is a mess. Let's clean it up by removing all the junk we don't need.
function joints_head_cleanup() {
	// Remove category feeds
	// remove_action( 'wp_head', 'feed_links_extra', 3 );
	// Remove post and comment feeds
	// remove_action( 'wp_head', 'feed_links', 2 );
	// Remove EditURI link
	remove_action( 'wp_head', 'rsd_link' );
	// Remove Windows live writer
	remove_action( 'wp_head', 'wlwmanifest_link' );
	// Remove index link
	remove_action( 'wp_head', 'index_rel_link' );
	// Remove previous link
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	// Remove start link
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	// Remove links for adjacent posts
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	// Remove WP version
	remove_action( 'wp_head', 'wp_generator' );
} /* end Joints head cleanup */

// Remove injected CSS for recent comments widget
function joints_remove_wp_widget_recent_comments_style() {
   if ( has_filter('wp_head', 'wp_widget_recent_comments_style') ) {
      remove_filter('wp_head', 'wp_widget_recent_comments_style' );
   }
}

// Remove injected CSS from recent comments widget
function joints_remove_recent_comments_style() {
  global $wp_widget_factory;
  if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
    remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
  }
}

// Remove injected CSS from gallery
function joints_gallery_style($css) {
  return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
}

// This removes the annoying […] to a Read More link
function joints_excerpt_more($more) {
	global $post;
	// edit here if you like
return '<a class="excerpt-read-more" href="'. get_permalink($post->ID) . '" title="'. __('Read', 'jointswp') . get_the_title($post->ID).'">'. __('... Read more &raquo;', 'jointswp') .'</a>';
}

//  Stop WordPress from using the sticky class (which conflicts with Foundation), and style WordPress sticky posts using the .wp-sticky class instead
function remove_sticky_class($classes) {
	if(in_array('sticky', $classes)) {
		$classes = array_diff($classes, array("sticky"));
		$classes[] = 'wp-sticky';
	}
	
	return $classes;
}
add_filter('post_class','remove_sticky_class');

//This is a modified the_author_posts_link() which just returns the link. This is necessary to allow usage of the usual l10n process with printf()
function joints_get_the_author_posts_link() {
	global $authordata;
	if ( !is_object( $authordata ) )
		return false;
	$link = sprintf(
		'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
		get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
		esc_attr( sprintf( __( 'Posts by %s', 'jointswp' ), get_the_author() ) ), // No further l10n needed, core will take care of this one
		get_the_author()
	);
	return $link;
}