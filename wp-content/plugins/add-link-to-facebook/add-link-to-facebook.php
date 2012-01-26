<?php
/*
Plugin Name: Add Link to Facebook
Plugin URI: http://wordpress.org/extend/plugins/add-link-to-facebook/
Description: Automatically add links to published posts to your Facebook wall or pages
Version: 1.132
Author: Marcel Bokhorst
Author URI: http://blog.bokhorst.biz/about/
*/

/*
	GNU General Public License version 3

	Copyright (c) 2011, 2012 Marcel Bokhorst

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Enabled all notices
if (get_option(c_al2fb_option_debug))
	error_reporting(E_ALL);

// Check PHP version
if (version_compare(PHP_VERSION, '5.0.0', '<'))
	die('Add Link to Facebook requires at least PHP 5, installed version is ' . PHP_VERSION);

// Debug console
if (get_option('al2fb_debug')) {
	//require_once('PhpConsole.php');
	//PhpConsole::start();
}

// Auto load widget class
if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
	function __autoload_al2fb($class_name) {
		if ($class_name == 'AL2FB_Widget')
			require_once('add-link-to-facebook-widget.php');
	}
	spl_autoload_register('__autoload_al2fb');
}
else {
	if (function_exists('__autoload')) {
		// Another plugin is using __autoload too
		require_once('add-link-to-facebook-widget.php');
	}
	else {
		function __autoload($class_name) {
			if ($class_name == 'AL2FB_Widget')
				require_once('add-link-to-facebook-widget.php');
		}
	}
}

// Include support class
require_once('add-link-to-facebook-class.php');

// Check pre-requisites
WPAL2Facebook::Check_prerequisites();

// Start plugin
global $wp_al2fb;
if (empty($wp_al2fb)) {
	$wp_al2fb = new WPAL2Facebook();
	register_activation_hook(__FILE__, array(&$wp_al2fb, 'Activate'));
}

// Schedule cron if needed
if (get_option(c_al2fb_option_cron_enabled)) {
	if (!wp_next_scheduled('al2fb_cron')) {
		$min = intval(time() / 60) + 1;
		wp_schedule_event($min * 60, 'al2fb_schedule', 'al2fb_cron');
	}
}
else
	wp_clear_scheduled_hook('al2fb_cron');

add_action('al2fb_cron', 'al2fb_cron');
if (!function_exists('al2fb_cron')) {
	function al2fb_cron() {
		global $wp_al2fb;
		$wp_al2fb->Cron();
	}
}

// Template tag for likers
if (!function_exists('al2fb_likers')) {
	function al2fb_likers($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_likers($post);
	}
}

// Template tag for like count
if (!function_exists('al2fb_like_count')) {
	function al2fb_like_count($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_like_count($post);
	}
}

// Template tag for Facebook like button
if (!function_exists('al2fb_like_button')) {
	function al2fb_like_button($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_like_button($post, false);
	}
}

// Template tag for Facebook like box
if (!function_exists('al2fb_like_box')) {
	function al2fb_like_box($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_like_button($post, true);
	}
}

// Template tag for Facebook send button
if (!function_exists('al2fb_send_button')) {
	function al2fb_send_button($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_send_button($post);
	}
}

// Template tag for Facebook comments plugins
if (!function_exists('al2fb_comments_plugin')) {
	function al2fb_comments_plugin($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_comments_plugin($post);
	}
}

// Template tag for Facebook face pile
if (!function_exists('al2fb_face_pile')) {
	function al2fb_face_pile($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_face_pile($post);
	}
}

// Template tag for profile link
if (!function_exists('al2fb_profile_link')) {
	function al2fb_profile_link($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_profile_link($post);
	}
}

// Template tag for Facebook registration
if (!function_exists('al2fb_registration')) {
	function al2fb_registration($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_registration($post);
	}
}

// Template tag for Facebook login
if (!function_exists('al2fb_login')) {
	function al2fb_login($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_login($post);
	}
}

// Template tag for Facebook activity feed
if (!function_exists('al2fb_activity_feed')) {
	function al2fb_activity_feed($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_activity_feed($post);
	}
}

// User meta per blog (multi site installs)

add_filter('add_user_metadata', 'al2fb_add_user_metadata', 10, 5);
add_filter('update_user_metadata', 'al2fb_update_user_metadata', 10, 5);
add_filter('delete_user_metadata', 'al2fb_delete_user_metadata', 10, 4);
add_filter('get_user_metadata', 'al2fb_get_user_metadata', 10, 4);

function al2fb_add_user_metadata($meta_type = null, $user_id, $meta_key, $meta_value, $unique = false) {
	global $blog_id;
	if (!empty($blog_id) && $blog_id > 1 && strpos($meta_key, 'al2fb_') === 0)
		return add_user_meta($user_id, 'blog_' . $blog_id . '_' . $meta_key, $meta_value, $unique);
	return null;
}

function al2fb_update_user_metadata($meta_type = null, $user_id, $meta_key, $meta_value, $prev_value = '') {
	global $blog_id;
	if (!empty($blog_id) && $blog_id > 1 && strpos($meta_key, 'al2fb_') === 0)
		return update_user_meta($user_id, 'blog_' . $blog_id . '_' . $meta_key, $meta_value, $prev_value);
	return null;
}

function al2fb_delete_user_metadata($meta_type = null, $user_id, $meta_key, $meta_value = '') {
	global $blog_id;
	if (!empty($blog_id) && $blog_id > 1 && strpos($meta_key, 'al2fb_') === 0)
		return delete_user_meta($user_id, 'blog_' . $blog_id . '_' . $meta_key, $meta_value);
	return null;
}

function al2fb_get_user_metadata($meta_type = null, $user_id, $meta_key, $single = false) {
	global $blog_id;
	if (!empty($blog_id) && $blog_id > 1 && strpos($meta_key, 'al2fb_') === 0)
		return get_user_meta($user_id, 'blog_' . $blog_id . '_' . $meta_key, $single);
	return null;
}

// That's it!

?>
