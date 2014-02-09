<?php
/**
 * Edit post administration panel.
 *
 * Manage Post actions: post, edit, delete, etc.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

$parent_file = 'edit.php';
$submenu_file = 'edit.php';

wp_reset_vars( array( 'action' ) );

if ( isset( $_GET['post'] ) )
 	$post_id = $post_ID = (int) $_GET['post'];
elseif ( isset( $_POST['post_ID'] ) )
 	$post_id = $post_ID = (int) $_POST['post_ID'];
else
 	$post_id = $post_ID = 0;

$post = $post_type = $post_type_object = null;

if ( $post_id )
	$post = get_post( $post_id );

if ( $post ) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
}

/**
 * Redirect to previous page.
 *
 * @param int $post_id Optional. Post ID.
 */
function redirect_post($post_id = '') {
	if ( isset($_POST['save']) || isset($_POST['publish']) ) {
		$status = get_post_status( $post_id );

		if ( isset( $_POST['publish'] ) ) {
			switch ( $status ) {
				case 'pending':
					$message = 8;
					break;
				case 'future':
					$message = 9;
					break;
				default:
					$message = 6;
			}
		} else {
				$message = 'draft' == $status ? 10 : 1;
		}

		$location = add_query_arg( 'message', $message, get_edit_post_link( $post_id, 'url' ) );
	} elseif ( isset($_POST['addmeta']) && $_POST['addmeta'] ) {
		$location = add_query_arg( 'message', 2, wp_get_referer() );
		$location = explode('#', $location);
		$location = $location[0] . '#postcustom';
	} elseif ( isset($_POST['deletemeta']) && $_POST['deletemeta'] ) {
		$location = add_query_arg( 'message', 3, wp_get_referer() );
		$location = explode('#', $location);
		$location = $location[0] . '#postcustom';
	} else {
		$location = add_query_arg( 'message', 4, get_edit_post_link( $post_id, 'url' ) );
	}

	/**
	 * Filter the post redirect destination URL.
	 *
	 * @since 2.9.0
	 *
	 * @param string $location The destination URL.
	 * @param int    $post_id  The post ID.
	 */
	wp_redirect( apply_filters( 'redirect_post_location', $location, $post_id ) );
	exit;
}

if ( isset( $_POST['deletepost'] ) )
	$action = 'delete';
elseif ( isset($_POST['wp-preview']) && 'dopreview' == $_POST['wp-preview'] )
	$action = 'preview';

$sendback = wp_get_referer();
if ( ! $sendback ||
     strpos( $sendback, 'post.php' ) !== false ||
     strpos( $sendback, 'post-new.php' ) !== false ) {
	if ( 'attachment' == $post_type ) {
		$sendback = admin_url( 'upload.php' );
	} else {
		$sendback = admin_url( 'edit.php' );
		$sendback .= ( ! empty( $post_type ) ) ? '?post_type=' . $post_type : '';
	}
} else {
	$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), $sendback );
}

switch($action) {
case 'post-quickdraft-save':
	// Check nonce and capabilities
	$nonce = $_REQUEST['_wpnonce'];
	$error_msg = false;
	if ( ! wp_verify_nonce( $nonce, 'add-post' ) )
		$error_msg = __( 'Unable to submit this form, please refresh and try again.' );

	if ( ! current_user_can( 'edit_posts' ) )
		$error_msg = __( 'Oops, you don&#8217;t have access to add new drafts.' );

	if ( $error_msg )
		return wp_dashboard_quick_press( $error_msg );

	$post = get_post( $_REQUEST['post_ID'] );
	check_admin_referer( 'add-' . $post->post_type );

	$_POST['comment_status'] = get_option( 'default_comment_status' );
	$_POST['ping_status'] = get_option( 'default_ping_status' );

	edit_post();
	// output the quickdraft dashboard widget
	require_once(ABSPATH . 'wp-admin/includes/dashboard.php');
	wp_dashboard_quick_press();
	exit;
	break;

case 'postajaxpost':
case 'post':
	check_admin_referer( 'add-' . $post_type );
	$post_id = 'postajaxpost' == $action ? edit_post() : write_post();
	redirect_post( $post_id );
	exit();
	break;

case 'edit':
	$editing = true;

	if ( empty( $post_id ) ) {
		wp_redirect( admin_url('post.php') );
		exit();
	}

	if ( ! $post )
		wp_die( __( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?' ) );

	if ( ! $post_type_object )
		wp_die( __( 'Unknown post type.' ) );

	if ( ! current_user_can( 'edit_post', $post_id ) )
		wp_die( __( 'You are not allowed to edit this item.' ) );

	if ( 'trash' == $post->post_status )
		wp_die( __( 'You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.' ) );

	if ( ! empty( $_GET['get-post-lock'] ) ) {
		wp_set_post_lock( $post_id );
		wp_redirect( get_edit_post_link( $post_id, 'url' ) );
		exit();
	}

	$post_type = $post->post_type;
	if ( 'post' == $post_type ) {
		$parent_file = "edit.php";
		$submenu_file = "edit.php";
		$post_new_file = "post-new.php";
	} elseif ( 'attachment' == $post_type ) {
		$parent_file = 'upload.php';
		$submenu_file = 'upload.php';
		$post_new_file = 'media-new.php';
	} else {
		if ( isset( $post_type_object ) && $post_type_object->show_in_menu && $post_type_object->show_in_menu !== true )
			$parent_file = $post_type_object->show_in_menu;
		else
			$parent_file = "edit.php?post_type=$post_type";
		$submenu_file = "edit.php?post_type=$post_type";
		$post_new_file = "post-new.php?post_type=$post_type";
	}

	if ( ! wp_check_post_lock( $post->ID ) ) {
		$active_post_lock = wp_set_post_lock( $post->ID );

		if ( 'attachment' !== $post_type )
			wp_enqueue_script('autosave');
	}

	if ( is_multisite() ) {
		add_action( 'admin_footer', '_admin_notice_post_locked' );
	} else {
		$check_users = get_users( array( 'fields' => 'ID', 'number' => 2 ) );

		if ( count( $check_users ) > 1 )
			add_action( 'admin_footer', '_admin_notice_post_locked' );

		unset( $check_users );
	}

	$title = $post_type_object->labels->edit_item;
	$post = get_post($post_id, OBJECT, 'edit');

	if ( post_type_supports($post_type, 'comments') ) {
		wp_enqueue_script('admin-comments');
		enqueue_comment_hotkeys_js();
	}

	include( ABSPATH . 'wp-admin/edit-form-advanced.php' );

	break;

case 'editattachment':
	check_admin_referer('update-post_' . $post_id);

	// Don't let these be changed
	unset($_POST['guid']);
	$_POST['post_type'] = 'attachment';

	// Update the thumbnail filename
	$newmeta = wp_get_attachment_metadata( $post_id, true );
	$newmeta['thumb'] = $_POST['thumb'];

	wp_update_attachment_metadata( $post_id, $newmeta );

case 'editpost':
	check_admin_referer('update-post_' . $post_id);

	$post_id = edit_post();

	// Session cookie flag that the post was saved
	if ( isset( $_COOKIE['wp-saving-post-' . $post_id] ) )
		setcookie( 'wp-saving-post-' . $post_id, 'saved' );

	redirect_post($post_id); // Send user on their way while we keep working

	exit();
	break;

case 'trash':
	check_admin_referer('trash-post_' . $post_id);

	if ( ! $post )
		wp_die( __( 'The item you are trying to move to the Trash no longer exists.' ) );

	if ( ! $post_type_object )
		wp_die( __( 'Unknown post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		wp_die( __( 'You are not allowed to move this item to the Trash.' ) );

	if ( $user_id = wp_check_post_lock( $post_id ) ) {
		$user = get_userdata( $user_id );
		wp_die( sprintf( __( 'You cannot move this item to the Trash. %s is currently editing.' ), $user->display_name ) );
	}

	if ( ! wp_trash_post( $post_id ) )
		wp_die( __( 'Error in moving to Trash.' ) );

	wp_redirect( add_query_arg( array('trashed' => 1, 'ids' => $post_id), $sendback ) );
	exit();
	break;

case 'untrash':
	check_admin_referer('untrash-post_' . $post_id);

	if ( ! $post )
		wp_die( __( 'The item you are trying to restore from the Trash no longer exists.' ) );

	if ( ! $post_type_object )
		wp_die( __( 'Unknown post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		wp_die( __( 'You are not allowed to move this item out of the Trash.' ) );

	if ( ! wp_untrash_post( $post_id ) )
		wp_die( __( 'Error in restoring from Trash.' ) );

	wp_redirect( add_query_arg('untrashed', 1, $sendback) );
	exit();
	break;

case 'delete':
	check_admin_referer('delete-post_' . $post_id);

	if ( ! $post )
		wp_die( __( 'This item has already been deleted.' ) );

	if ( ! $post_type_object )
		wp_die( __( 'Unknown post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		wp_die( __( 'You are not allowed to delete this item.' ) );

	$force = ! EMPTY_TRASH_DAYS;
	if ( $post->post_type == 'attachment' ) {
		$force = ( $force || ! MEDIA_TRASH );
		if ( ! wp_delete_attachment( $post_id, $force ) )
			wp_die( __( 'Error in deleting.' ) );
	} else {
		if ( ! wp_delete_post( $post_id, $force ) )
			wp_die( __( 'Error in deleting.' ) );
	}

	wp_redirect( add_query_arg('deleted', 1, $sendback) );
	exit();
	break;

case 'preview':
	check_admin_referer( 'autosave', 'autosavenonce' );

	$url = post_preview();

	wp_redirect($url);
	exit();
	break;

default:
	wp_redirect( admin_url('edit.php') );
	exit();
	break;
} // end switch
include( ABSPATH . 'wp-admin/admin-footer.php' );
