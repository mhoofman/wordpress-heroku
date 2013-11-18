<?php
/*
Plugin Name: WordPress Google Search
Plugin URI: http://www.seedprod.com
Description: Enables a Google Custom Search Engine for your site via a widget. To add goto Appearance > Widgets
Version: 1.0.1
Author: SeedProd
Author URI: http://www.seedprod.com
License: GPLv2
Copyright 2011  John Turner (email : john@seedprod.com, twitter : @johnturner)
*/

// Hook into the wpquery to fake like we have a post so we can filter the content of the search results page
add_action('template_redirect', 'seed_glcse_hook_search_results');

function seed_glcse_hook_search_results(){
	if(is_search() && $_GET['cref']){
		global $wp_query; 
		$wp_query->current_post = -1;
		$wp_query->post_count = 1;
	}
}

// Add a body class filter when shown
add_filter( 'body_class', 'seed_glcse_body_class_filter',10,2);

function seed_glcse_body_class_filter($classes,$class) {
	if(is_search() && $_GET['cref']){
		$classes[] = 'google-search';
		return $classes;
	}else{
		return $classes;
	}
}


// Use the Google search query instead of the default WP which would be empty
add_filter( 'get_search_query', 'seed_glcse_search_query_filter' );

function seed_glcse_search_query_filter($q) {
	if(is_search() && $_GET['cref']){
		return $_GET['q'];
	}
}

// Replace the content of the search results page if it's a google searh query
add_filter( 'the_content', 'seed_glcse_content_filter' );

function seed_glcse_content_filter($content) {
	if(is_search() && $_GET['cref']){
		$output ='
			<style type="text/css">
			.post-meta{
				display:none;
			}
			#cse-search-results iframe{
				width:100%;
			}
			</style>
			<div id="cse-search-results"></div>
		  	<script type="text/javascript">
		    var googleSearchIframeName = "cse-search-results";
		    var googleSearchFormName = "cse-search-box";
		    var googleSearchFrameWidth = "100%";
		    var googleSearchDomain = "www.google.com";
		    var googleSearchPath = "/cse";
		  	</script>
		  	<script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>
		  	';
  		echo $output;
	}else{
  		return $content;
  	}
}


// Replace the content of the search results page if it's a google searh query
add_filter( 'the_excerpt', 'seed_glcse_excerpt_filter' );

function seed_glcse_excerpt_filter($content) {
	if(is_search() && $_GET['cref']){
		$output ='
			<style type="text/css">
			.post-meta{
				display:none;
			}
			#cse-search-results iframe{
				width:100%;
			}
			</style>
			<div id="cse-search-results"></div>
		  	<script type="text/javascript">
		    var googleSearchIframeName = "cse-search-results";
		    var googleSearchFormName = "cse-search-box";
		    var googleSearchFrameWidth = "100%";
		    var googleSearchDomain = "www.google.com";
		    var googleSearchPath = "/cse";
		  	</script>
		  	<script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>
		  	';
  		echo $output;
	}else{
  		return $content;
  	}
}


// Render the cse.xml for the google linked custom search to use when the correct query is passed in http://www.google.com/cse/docs/cref.html
add_action('template_redirect', 'seed_glcse_render_cse');

function seed_glcse_render_cse(){
	if($_GET['glcse'] == '1'){
		header ("Content-Type:text/xml"); 
		echo '<?xml version="1.0" encoding="UTF-8" ?>'."\r\n";
?>
<GoogleCustomizations> 
  <CustomSearchEngine volunteers="false" visible="false" encoding="utf-8"> 
    <Title><?php echo get_bloginfo('name'); ?></Title> 
    <Description><?php echo get_bloginfo('description'); ?></Description> 
    <Context> 
      <BackgroundLabels> 
        <Label name="cse_include" mode="FILTER" /> 
        <Label name="cse_exclude" mode="ELIMINATE" /> 
      </BackgroundLabels> 
    </Context> 
    <LookAndFeel nonprofit="false" /> 
  </CustomSearchEngine>  
  <Annotations> 
    <Annotation about="<?php echo home_url(); ?>/*"> 
      <Label name="cse_include" /> 
    </Annotation> 
  </Annotations> 
</GoogleCustomizations><?php
	exit();
	}
}


// Register the Google Search widget
add_action( 'widgets_init', create_function( '', 'register_widget( "seed_glcse_widget" );' ) );

class seed_glcse_widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'seed_glcse_widget', 
			'Google Search',
			array( 'description' => __( 'Displays a Google Search widget on your site.', 'seed_glcse' ), )
		);
	}

	public function widget( $args, $instance ) {
		extract($args);
		echo $before_widget;
		?>
		<div class="glcse-widget">
		<form id="glcse-search-form" class="glcse-search" action="<?php echo home_url(); ?>" method="get">
        <input type="hidden" name="cref" value="<?php echo home_url().'/?glcse=1'; ?>" />
        <input type="hidden" name="cof" value="FORID:11" />
        <input type="hidden" name="ie" value="UTF-8" />
        <input type="text" name="q" size="31" />
        <input type="hidden" name="s" value="glcse" />
    	</form>
    	<script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=glcse-search-form&lang=en"></script>
    	</div>
    	<?php
		echo $after_widget;
	}

} 