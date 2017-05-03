<?php
// Theme support options
require_once(get_template_directory().'/assets/functions/theme-support.php'); 

// WP Head and other cleanup functions
require_once(get_template_directory().'/assets/functions/cleanup.php'); 

// Register scripts and stylesheets
require_once(get_template_directory().'/assets/functions/enqueue-scripts.php'); 

// Register custom menus and menu walkers
require_once(get_template_directory().'/assets/functions/menu.php'); 

// Register sidebars/widget areas
require_once(get_template_directory().'/assets/functions/sidebar.php'); 

// Makes WordPress comments suck less
require_once(get_template_directory().'/assets/functions/comments.php'); 

// Replace 'older/newer' post links with numbered navigation
require_once(get_template_directory().'/assets/functions/page-navi.php');

// Adds support for multiple languages
require_once(get_template_directory().'/assets/translation/translation.php'); 


/*****

/* Define the custom box */
add_action( 'add_meta_boxes', 'myplugin_add_custom_box' );

/* Do something with the data entered */
add_action( 'save_post', 'myplugin_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function myplugin_add_custom_box() {
  global $post;
    if ( 'template-single-slide-one-column.php' == get_post_meta( $post->ID, '_wp_page_template', true ) ) {
       		add_meta_box( 'wp_editor_test_1_box', 'Slide', 'wp_editor_meta_box_1' );
  	}
  	if ( 'template-single-slide-one-to-two-column.php' == get_post_meta( $post->ID, '_wp_page_template', true ) ) {
       		add_meta_box( 'wp_editor_test_1_box', 'Slide', 'wp_editor_meta_box_1' );
  			add_meta_box( 'wp_editor_test_3_box', 'DSA Left Column', 'wp_editor_meta_box_3' );
  			add_meta_box( 'wp_editor_test_4_box', 'DSA Right Column', 'wp_editor_meta_box_4' );
  	}
    if ( 'template-homepage-v1.php' == get_post_meta( $post->ID, '_wp_page_template', true ) ) {
          add_meta_box( 'wp_editor_test_8_box', 'DSA Feature Box', 'wp_editor_meta_box_8' );
          add_meta_box( 'wp_editor_test_5_box', 'Slide 1', 'wp_editor_meta_box_5' );
          add_meta_box( 'wp_editor_test_6_box', 'Slide 2', 'wp_editor_meta_box_6' );
          add_meta_box( 'wp_editor_test_7_box', 'Slide 3', 'wp_editor_meta_box_7' );
          add_meta_box( 'wp_editor_test_9_box', 'Homepage Row 2', 'wp_editor_meta_box_9' );
          add_meta_box( 'wp_editor_test_10_box', 'Homepage Row 3', 'wp_editor_meta_box_10' );
    }
  	if ( 'template-fullwidth-to-two-column.php' == get_post_meta( $post->ID, '_wp_page_template', true ) ) {
  			add_meta_box( 'wp_editor_test_3_box', 'DSA Left Column', 'wp_editor_meta_box_3' );
  			add_meta_box( 'wp_editor_test_4_box', 'DSA Right Column', 'wp_editor_meta_box_4' );
  	}
  	if ( 'template-two-column.php' == get_post_meta( $post->ID, '_wp_page_template', true ) ) {
  			add_meta_box( 'wp_editor_test_4_box', 'DSA Right Column', 'wp_editor_meta_box_4' );
  	}
    if ( 'template-three-slides-to-two-columns.php' == get_post_meta( $post->ID, '_wp_page_template', true ) ) {
        add_meta_box( 'wp_editor_test_3_box', 'DSA Left Column', 'wp_editor_meta_box_3' );
        add_meta_box( 'wp_editor_test_4_box', 'DSA Right Column', 'wp_editor_meta_box_4' );
        add_meta_box( 'wp_editor_test_5_box', 'Slide 1', 'wp_editor_meta_box_5' );
        add_meta_box( 'wp_editor_test_6_box', 'Slide 2', 'wp_editor_meta_box_6' );
        add_meta_box( 'wp_editor_test_7_box', 'Slide 3', 'wp_editor_meta_box_7' );
    }
}

/* Prints the box content */
function wp_editor_meta_box_1( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_slide0', false );
  wp_editor( $field_value[0], '_dsa_slide0' );

}

function wp_editor_meta_box_2( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_slide_right', false );
  wp_editor( $field_value[0], '_dsa_slide_right' );
}

function wp_editor_meta_box_3( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_column_left', false );
  wp_editor( $field_value[0], '_dsa_column_left' );
}


function wp_editor_meta_box_4( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_column_right', false );
  wp_editor( $field_value[0], '_dsa_column_right' );
}

function wp_editor_meta_box_5( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_slide1', false );
  wp_editor( $field_value[0], '_dsa_slide1' );
}

function wp_editor_meta_box_6( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_slide2', false );
  wp_editor( $field_value[0], '_dsa_slide2' );
}

function wp_editor_meta_box_7( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_slide3', false );
  wp_editor( $field_value[0], '_dsa_slide3' );
}

function wp_editor_meta_box_8( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_feature_box', false );
  wp_editor( $field_value[0], '_dsa_feature_box' );
}

function wp_editor_meta_box_9( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_homepage_row_2', false );
  wp_editor( $field_value[0], '_dsa_homepage_row_2' );
}

function wp_editor_meta_box_10( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  $field_value = get_post_meta( $post->ID, '_dsa_homepage_row_3', false );
  wp_editor( $field_value[0], '_dsa_homepage_row_3' );
}


/* When the post is saved, saves our custom data */
function myplugin_save_postdata( $post_id ) {

  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( ( isset ( $_POST['myplugin_noncename'] ) ) && ( ! wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) ) )
      return;

  // Check permissions
  if ( ( isset ( $_POST['post_type'] ) ) && ( 'page' == $_POST['post_type'] )  ) {
    if ( ! current_user_can( 'edit_page', $post_id ) ) {
      return;
    }    
  }
  else {
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
      return;
    }
  }

  // OK, we're authenticated: we need to find and save the data
	if ( isset ( $_POST['_dsa_slide0'] ) ) {
    update_post_meta( $post_id, '_dsa_slide0', $_POST['_dsa_slide0'] );
  }

  if ( isset ( $_POST['_dsa_column_left'] ) ) {
    update_post_meta( $post_id, '_dsa_column_left', $_POST['_dsa_column_left'] );
  }

  if ( isset ( $_POST['_dsa_column_right'] ) ) {
    update_post_meta( $post_id, '_dsa_column_right', $_POST['_dsa_column_right'] );
  }

  if ( isset ( $_POST['_dsa_slide1'] ) ) {
    update_post_meta( $post_id, '_dsa_slide1', $_POST['_dsa_slide1'] );
  }

  if ( isset ( $_POST['_dsa_slide2'] ) ) {
    update_post_meta( $post_id, '_dsa_slide2', $_POST['_dsa_slide2'] );
  }

  if ( isset ( $_POST['_dsa_slide3'] ) ) {
    update_post_meta( $post_id, '_dsa_slide3', $_POST['_dsa_slide3'] );
  }
  if ( isset ( $_POST['_dsa_feature_box'] ) ) {
    update_post_meta( $post_id, '_dsa_feature_box', $_POST['_dsa_feature_box'] );
  }
   if ( isset ( $_POST['_dsa_homepage_row_2'] ) ) {
    update_post_meta( $post_id, '_dsa_homepage_row_2', $_POST['_dsa_homepage_row_2'] );
  }
   if ( isset ( $_POST['_dsa_homepage_row_3'] ) ) {
    update_post_meta( $post_id, '_dsa_homepage_row_3', $_POST['_dsa_homepage_row_3'] );
  }

}


/*****



// Remove 4.2 Emoji Support
// require_once(get_template_directory().'/assets/functions/disable-emoji.php'); 

// Adds site styles to the WordPress editor
//require_once(get_template_directory().'/assets/functions/editor-styles.php'); 

// Related post function - no need to rely on plugins
// require_once(get_template_directory().'/assets/functions/related-posts.php'); 

// Use this as a template for custom post types
// require_once(get_template_directory().'/assets/functions/custom-post-type.php');

// Customize the WordPress login menu
// require_once(get_template_directory().'/assets/functions/login.php'); 

// Customize the WordPress admin
// require_once(get_template_directory().'/assets/functions/admin.php'); 