<?php

/*
	Support class Add Link to Facebook plugin
	Copyright (c) 2011, 2012 by Marcel Bokhorst
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

// Define constants
define('c_al2fb_text_domain', 'add-link-to-facebook');
define('c_al2fb_nonce_form', 'al2fb-nonce-form');

// Global options
define('c_al2fb_option_version', 'al2fb_version');
define('c_al2fb_option_timeout', 'al2fb_timeout');
define('c_al2fb_option_nonotice', 'al2fb_nonotice');
define('c_al2fb_option_min_cap', 'al2fb_min_cap');
define('c_al2fb_option_min_cap_comment', 'al2fb_min_cap_comment');
define('c_al2fb_option_msg_refresh', 'al2fb_comment_refresh');
define('c_al2fb_option_msg_maxage', 'al2fb_msg_maxage');
define('c_al2fb_option_max_descr', 'al2fb_max_msg');
define('c_al2fb_option_max_text', 'al2fb_max_text');
define('c_al2fb_option_exclude_type', 'al2fb_exclude_type');
define('c_al2fb_option_exclude_cat', 'al2fb_exclude_cat');
define('c_al2fb_option_exclude_tag', 'al2fb_exclude_tag');
define('c_al2fb_option_exclude_author', 'al2fb_exclude_author');
define('c_al2fb_option_metabox_type', 'al2fb_metabox_type');
define('c_al2fb_option_noverifypeer', 'al2fb_noverifypeer');
define('c_al2fb_option_shortcode_widget', 'al2fb_shortcode_widget');
define('c_al2fb_option_noshortcode', 'al2fb_noshortcode');
define('c_al2fb_option_nofilter', 'al2fb_nofilter');
define('c_al2fb_option_nofilter_comments', 'al2fb_nofilter_comments');
define('c_al2fb_option_use_ssp', 'al2fb_use_ssp');
define('c_al2fb_option_ssp_info', 'al2fb_ssp_info');
define('c_al2fb_option_filter_prio', 'al2fb_filter_prio');
define('c_al2fb_option_noscript', 'al2fb_noscript');
define('c_al2fb_option_clean', 'al2fb_clean');
define('c_al2fb_option_css', 'al2fb_css');
define('c_al2fb_option_siteurl', 'al2fb_siteurl');
define('c_al2fb_option_nocurl', 'al2fb_nocurl');
define('c_al2fb_option_use_pp', 'al2fb_use_pp');
define('c_al2fb_option_debug', 'al2fb_debug');

define('c_al2fb_option_cron_enabled', 'al2fb_cron_enabled');
define('c_al2fb_option_cron_time', 'al2fb_cron_time');
define('c_al2fb_option_cron_posts', 'al2fb_cron_posts');
define('c_al2fb_option_cron_comments', 'al2fb_cron_comments');
define('c_al2fb_option_cron_likes', 'al2fb_cron_likes');

// Site options
define('c_al2fb_option_app_share', 'al2fb_app_share');

// Transient options
define('c_al2fb_transient_cache', 'al2fb_cache_');

// User meta
define('c_al2fb_meta_client_id', 'al2fb_client_id');
define('c_al2fb_meta_app_secret', 'al2fb_app_secret');
define('c_al2fb_meta_access_token', 'al2fb_access_token');
define('c_al2fb_meta_picture_type', 'al2fb_picture_type');
define('c_al2fb_meta_picture', 'al2fb_picture');
define('c_al2fb_meta_picture_default', 'al2fb_picture_default');
define('c_al2fb_meta_page', 'al2fb_page');
define('c_al2fb_meta_page_owner', 'al2fb_page_owner');
define('c_al2fb_meta_use_groups', 'al2fb_use_groups');
define('c_al2fb_meta_group', 'al2fb_group');
define('c_al2fb_meta_caption', 'al2fb_caption');
define('c_al2fb_meta_msg', 'al2fb_msg');
define('c_al2fb_meta_shortlink', 'al2fb_shortlink');
define('c_al2fb_meta_add_new_page', 'al2fb_add_to_page');
define('c_al2fb_meta_trailer', 'al2fb_trailer');
define('c_al2fb_meta_hyperlink', 'al2fb_hyperlink');
define('c_al2fb_meta_share_link', 'al2fb_share_link');
define('c_al2fb_meta_fb_comments', 'al2fb_fb_comments');
define('c_al2fb_meta_fb_comments_postback', 'al2fb_fb_comments_postback');
define('c_al2fb_meta_fb_comments_copy', 'al2fb_fb_comments_copy');
define('c_al2fb_meta_fb_comments_nolink', 'al2fb_fb_comments_nolink');
define('c_al2fb_meta_fb_likes', 'al2fb_fb_likes');
define('c_al2fb_meta_post_likers', 'al2fb_post_likers');
define('c_al2fb_meta_post_like_button', 'al2fb_post_like_button');
define('c_al2fb_meta_like_nohome', 'al2fb_like_nohome');
define('c_al2fb_meta_like_noposts', 'al2fb_like_noposts');
define('c_al2fb_meta_like_nopages', 'al2fb_like_nopages');
define('c_al2fb_meta_like_noarchives', 'al2fb_like_noarchives');
define('c_al2fb_meta_like_nocategories', 'al2fb_like_nocategories');
define('c_al2fb_meta_like_layout', 'al2fb_like_layout');
define('c_al2fb_meta_like_faces', 'al2fb_like_faces');
define('c_al2fb_meta_like_width', 'al2fb_like_width');
define('c_al2fb_meta_like_action', 'al2fb_like_action');
define('c_al2fb_meta_like_font', 'al2fb_like_font');
define('c_al2fb_meta_like_colorscheme', 'al2fb_like_colorscheme');
define('c_al2fb_meta_like_link', 'al2fb_like_link');
define('c_al2fb_meta_like_top', 'al2fb_like_top');
define('c_al2fb_meta_like_iframe', 'al2fb_like_iframe');
define('c_al2fb_meta_post_send_button', 'al2fb_post_send_button');
define('c_al2fb_meta_post_combine_buttons', 'al2fb_post_combine_buttons');
define('c_al2fb_meta_like_box_width', 'al2fb_box_width');
define('c_al2fb_meta_like_box_border', 'al2fb_box_border');
define('c_al2fb_meta_like_box_noheader', 'al2fb_box_noheader');
define('c_al2fb_meta_like_box_nostream', 'al2fb_box_nostream');
define('c_al2fb_meta_comments_posts', 'al2fb_comments_posts');
define('c_al2fb_meta_comments_width', 'al2fb_comments_width');
define('c_al2fb_meta_comments_auto', 'al2fb_comments_auto');
define('c_al2fb_meta_pile_size', 'al2fb_pile_size');
define('c_al2fb_meta_pile_width', 'al2fb_pile_width');
define('c_al2fb_meta_pile_rows', 'al2fb_pile_rows');
define('c_al2fb_meta_reg_width', 'al2fb_reg_width');
define('c_al2fb_meta_login_width', 'al2fb_login_width');
define('c_al2fb_meta_login_regurl', 'al2fb_login_regurl');
define('c_al2fb_meta_login_redir', 'al2fb_login_redir');
define('c_al2fb_meta_login_html', 'al2fb_login_html');
define('c_al2fb_meta_act_width', 'al2fb_act_width');
define('c_al2fb_meta_act_height', 'al2fb_act_height');
define('c_al2fb_meta_act_header', 'al2fb_act_header');
define('c_al2fb_meta_act_recommend', 'al2fb_act_recommend');
define('c_al2fb_meta_open_graph', 'al2fb_open_graph');
define('c_al2fb_meta_open_graph_type', 'al2fb_open_graph_type');
define('c_al2fb_meta_open_graph_admins', 'al2fb_open_graph_admins');
define('c_al2fb_meta_exclude_default', 'al2fb_exclude_default');
define('c_al2fb_meta_not_post_list', 'al2fb_like_not_list');
define('c_al2fb_meta_fb_encoding', 'al2fb_fb_encoding');
define('c_al2fb_meta_fb_locale', 'al2fb_fb_locale');
define('c_al2fb_meta_donated', 'al2fb_donated');
define('c_al2fb_meta_rated0', 'al2fb_rated');
define('c_al2fb_meta_rated', 'al2fb_rated1');
define('c_al2fb_meta_stat', 'al2fb_stat');
define('c_al2fb_meta_week', 'al2fb_week');

// Post meta
define('c_al2fb_meta_link_id', 'al2fb_facebook_link_id');
define('c_al2fb_meta_link_time', 'al2fb_facebook_link_time');
define('c_al2fb_meta_link_picture', 'al2fb_facebook_link_picture');
define('c_al2fb_meta_exclude', 'al2fb_facebook_exclude');
define('c_al2fb_meta_error', 'al2fb_facebook_error');
define('c_al2fb_meta_error_time', 'al2fb_facebook_error_time');
define('c_al2fb_meta_image_id', 'al2fb_facebook_image_id');
define('c_al2fb_meta_nolike', 'al2fb_facebook_nolike');
define('c_al2fb_meta_nointegrate', 'al2fb_facebook_nointegrate');
define('c_al2fb_meta_excerpt', 'al2fb_facebook_excerpt');
define('c_al2fb_meta_text', 'al2fb_facebook_text');
define('c_al2fb_meta_log', 'al2fb_log');

define('c_al2fb_action_update', 'al2fb_action_update');
define('c_al2fb_action_delete', 'al2fb_action_delete');
define('c_al2fb_action_clear', 'al2fb_action_clear');

// Comment meta
define('c_al2fb_meta_fb_comment_id', 'al2fb_facebook_comment_id');

// Logging
define('c_al2fb_log_redir_init', 'al2fb_redir_init');
define('c_al2fb_log_redir_check', 'al2fb_redir_check');
define('c_al2fb_log_redir_time', 'al2fb_redir_time');
define('c_al2fb_log_redir_ref', 'al2fb_redir_ref');
define('c_al2fb_log_redir_from', 'al2fb_redir_from');
define('c_al2fb_log_redir_to', 'al2fb_redir_to');
define('c_al2fb_log_get_token', 'al2fb_get_token');
define('c_al2fb_log_auth_time', 'al2fb_auth_time');
define('c_al2fb_last_error', 'al2fb_last_error');
define('c_al2fb_last_error_time', 'al2fb_last_error_time');
define('c_al2fb_last_request', 'al2fb_last_request');
define('c_al2fb_last_request_time', 'al2fb_last_request_time');
define('c_al2fb_last_response', 'al2fb_last_response');
define('c_al2fb_last_response_time', 'al2fb_last_response_time');
define('c_al2fb_last_texts', 'al2fb_last_texts');

// User meta
define('c_al2fb_meta_facebook_id', 'al2fb_facebook_id');

// Mail
define('c_al2fb_mail_name', 'al2fb_debug_name');
define('c_al2fb_mail_email', 'al2fb_debug_email');
define('c_al2fb_mail_topic', 'al2fb_debug_topic');
define('c_al2fb_mail_msg', 'al2fb_debug_msg');

define('USERPHOTO_APPROVED', 2);

// Define class
if (!class_exists('WPAL2Facebook')) {
	class WPAL2Facebook {
		// Class variables
		var $main_file = null;
		var $plugin_url = null;
		var $php_error = null;
		var $debug = null;
		var $site_id = '';
		var $blog_id = '';

		// Constructor
		function __construct() {
			global $wp_version, $blog_id;

			// Get main file name
			$this->main_file = str_replace('-class', '', __FILE__);

			// Get plugin url
			$this->plugin_url = WP_PLUGIN_URL . '/' . basename(dirname($this->main_file));
			if (strpos($this->plugin_url, 'http') === 0 && is_ssl())
				$this->plugin_url = str_replace('http://', 'https://', $this->plugin_url);

			// Log
			$this->debug = get_option(c_al2fb_option_debug);

			// Get site & blog id
			if (is_multisite()) {
				$current_site = get_current_site();
				$this->site_id = $current_site->id;
			}
			$this->blog_id = $blog_id;

			// register activation actions
			//register_activation_hook($this->main_file, array(&$this, 'Activate'));
			register_deactivation_hook($this->main_file, array(&$this, 'Deactivate'));

			// Register actions
			add_action('init', array(&$this, 'Init'), 0);
			if (is_admin()) {
				add_action('admin_menu', array(&$this, 'Admin_menu'));
				add_filter('plugin_action_links', array(&$this, 'Plugin_action_links'), 10, 2);
				add_action('admin_notices', array(&$this, 'Admin_notices'));
				add_action('post_submitbox_misc_actions', array(&$this, 'Post_submitbox_misc_actions'));
				add_filter('manage_posts_columns', array(&$this, 'Manage_posts_columns'));
				add_action('manage_posts_custom_column', array(&$this, 'Manage_posts_custom_column'), 10, 2);
				add_filter('manage_pages_columns', array(&$this, 'Manage_posts_columns'));
				add_action('manage_pages_custom_column', array(&$this, 'Manage_posts_custom_column'), 10, 2);
				add_action('add_meta_boxes', array(&$this, 'Add_meta_boxes'));
				//add_action('save_post', array(&$this, 'Save_post'));
				add_action('personal_options', array(&$this, 'Personal_options'));
				add_action('personal_options_update', array(&$this, 'Personal_options_update'));
				add_action('edit_user_profile_update', array(&$this, 'Personal_options_update'));
			}

			add_action('transition_post_status', array(&$this, 'Transition_post_status'), 10, 3);
			add_action('xmlrpc_publish_post', array(&$this, 'Remote_publish'));
			add_action('app_publish_post', array(&$this, 'Remote_publish'));
			add_action('future_to_publish', array(&$this, 'Future_to_publish'));
			add_action('before_delete_post', array(&$this, 'Before_delete_post'));
			add_action('al2fb_publish', array(&$this, 'Remote_publish'));

			if (get_option(c_al2fb_option_use_pp))
				add_action('publish_post', array(&$this, 'Remote_publish'));

			add_action('comment_post', array(&$this, 'Comment_post'), 999);
			add_action('comment_unapproved_to_approved', array(&$this, 'Comment_approved'));
			add_action('comment_approved_to_unapproved', array(&$this, 'Comment_unapproved'));
			add_action('delete_comment', array(&$this, 'Delete_comment'));

			$fprio = intval(get_option(c_al2fb_option_filter_prio));
			if ($fprio <= 0)
				$fprio = 999;

			// Content
			add_action('wp_head', array(&$this, 'WP_head'));
			add_filter('the_content', array(&$this, 'The_content'), $fprio);
			add_filter('comments_array', array(&$this, 'Comments_array'), 10, 2);
			add_filter('get_comments_number', array(&$this, 'Get_comments_number'), 10, 2);
			add_filter('comment_class', array(&$this, 'Comment_class'));
			add_filter('get_avatar', array(&$this, 'Get_avatar'), 10, 5);

			// Shortcodes
			add_shortcode('al2fb_likers', array(&$this, 'Shortcode_likers'));
			add_shortcode('al2fb_like_count', array(&$this, 'Shortcode_like_count'));
			add_shortcode('al2fb_like_button', array(&$this, 'Shortcode_like_button'));
			add_shortcode('al2fb_like_box', array(&$this, 'Shortcode_like_box'));
			add_shortcode('al2fb_send_button', array(&$this, 'Shortcode_send_button'));
			add_shortcode('al2fb_comments_plugin', array(&$this, 'Shortcode_comments_plugin'));
			add_shortcode('al2fb_face_pile', array(&$this, 'Shortcode_face_pile'));
			add_shortcode('al2fb_profile_link', array(&$this, 'Shortcode_profile_link'));
			add_shortcode('al2fb_registration', array(&$this, 'Shortcode_registration'));
			add_shortcode('al2fb_login', array(&$this, 'Shortcode_login'));
			add_shortcode('al2fb_activity_feed', array(&$this, 'Shortcode_activity_feed'));
			if (get_option(c_al2fb_option_shortcode_widget))
				add_filter('widget_text', 'do_shortcode');

			// Custom filters
			add_filter('al2fb_excerpt', array(&$this, 'Filter_excerpt'), 10, 2);
			add_filter('al2fb_content', array(&$this, 'Filter_content'), 10, 2);
			add_filter('al2fb_comment', array(&$this, 'Filter_comment'), 10, 3);
			add_filter('al2fb_fb_feed', array(&$this, 'Filter_feed'), 10, 1);

			// Widget
			add_action('widgets_init', create_function('', 'return register_widget("AL2FB_Widget");'));
			if (!is_admin())
				add_action('wp_print_styles', array(&$this, 'WP_print_styles'));

			// Cron
			add_filter('cron_schedules', array(&$this, 'Cron_schedules'));
		}

		// Handle plugin activation
		function Activate() {
			global $wpdb;
			$version = get_option(c_al2fb_option_version);
			if (empty($version))
				update_option(c_al2fb_option_siteurl, true);
			if ($version <= 1) {
				delete_option(c_al2fb_meta_client_id);
				delete_option(c_al2fb_meta_app_secret);
				delete_option(c_al2fb_meta_access_token);
				delete_option(c_al2fb_meta_picture_type);
				delete_option(c_al2fb_meta_picture);
				delete_option(c_al2fb_meta_page);
				delete_option(c_al2fb_meta_donated);
			}
			if ($version <= 2) {
				$rows = $wpdb->get_results("SELECT user_id, meta_value FROM " . $wpdb->usermeta . " WHERE meta_key='al2fb_integrate'");
				foreach ($rows as $row) {
					update_user_meta($row->user_id, c_al2fb_meta_fb_comments, $row->meta_value);
					update_user_meta($row->user_id, c_al2fb_meta_fb_likes, $row->meta_value);
					delete_user_meta($row->user_id, 'al2fb_integrate');
				}
			}
			if ($version <= 3) {
				global $wpdb;
				$rows = $wpdb->get_results("SELECT ID FROM " . $wpdb->users);
				foreach ($rows as $row)
					update_user_meta($row->ID, c_al2fb_meta_like_faces, true);
			}
			if ($version <= 4) {
				$rows = $wpdb->get_results("SELECT user_id, meta_value FROM " . $wpdb->usermeta . " WHERE meta_key='" . c_al2fb_meta_trailer . "'");
				foreach ($rows as $row) {
					$value = get_user_meta($row->user_id, c_al2fb_meta_trailer, true);
					update_user_meta($row->user_id, c_al2fb_meta_trailer, ' ' . $value);
				}
			}
			if ($version <= 5) {
				if (!get_option(c_al2fb_option_css))
					update_option(c_al2fb_option_css,
'.al2fb_widget_comments { }
.al2fb_widget_comments li { }
.al2fb_widget_picture { width: 32px; height: 32px; }
.al2fb_widget_name { }
.al2fb_widget_comment { }
.al2fb_widget_date { font-size: smaller; }
');
			}
			if ($version <= 7) {
				update_option(c_al2fb_option_noshortcode, true);
				update_option(c_al2fb_option_nofilter, true);
			}
			if ($version <= 8)
				update_option(c_al2fb_option_nofilter_comments, true);

			update_option(c_al2fb_option_version, 9);
		}

		// Handle plugin deactivation
		function Deactivate() {
			// Stop cron job
			wp_clear_scheduled_hook('al2fb_cron');

			// Cleanup data
			if (get_option(c_al2fb_option_clean)) {
				global $wpdb;
				// Delete options
				$rows = $wpdb->get_results("SELECT option_name FROM " . $wpdb->options . " WHERE option_name LIKE 'al2fb_%'");
				foreach ($rows as $row)
					delete_option($row->option_name);

				// Delete user meta values
				$rows = $wpdb->get_results("SELECT user_id, meta_key FROM " . $wpdb->usermeta . " WHERE meta_key LIKE 'al2fb_%'");
				foreach ($rows as $row)
					delete_user_meta($row->user_id, $row->meta_key);
			}
		}

		// Initialization
		function Init() {
			// I18n
			load_plugin_textdomain(c_al2fb_text_domain, false, dirname(plugin_basename(__FILE__)) . '/language/');

			// Image request
			if (isset($_GET['al2fb_image'])) {
				$img = dirname(__FILE__) . '/wp-blue-s.png';
				header('Content-type: image/png');
				readfile($img);
  				exit();
			}

			// Facebook registration
			if (isset($_REQUEST['al2fb_reg'])) {
				self::Facebook_registration();
				exit();
			}

			// Facebook login
			if (isset($_REQUEST['al2fb_login'])) {
				self::Facebook_login();
				exit();
			}

			// Facebook subscription
			if (isset($_REQUEST['al2fb_subscription'])) {
				self::Handle_fb_subscription();
				exit();
			}

			// Set default capability
			if (!get_option(c_al2fb_option_min_cap))
				update_option(c_al2fb_option_min_cap, 'edit_posts');

			// Enqueue style sheet
			if (is_admin()) {
				$css_name = $this->Change_extension(basename($this->main_file), '-admin.css');
				$css_url = $this->plugin_url . '/' . $css_name;
				wp_register_style('al2fb_style_admin', $css_url);
				wp_enqueue_style('al2fb_style_admin');
			}
			else {
				$upload_dir = wp_upload_dir();
				$css_name = $this->Change_extension(basename($this->main_file), '.css');
				if (file_exists($upload_dir['basedir'] . '/' . $css_name))
					$css_url = $upload_dir['baseurl'] . '/' . $css_name;
				else if (file_exists(TEMPLATEPATH . '/' . $css_name))
					$css_url = get_bloginfo('template_directory') . '/' . $css_name;
				else
					$css_url = $this->plugin_url . '/' . $css_name;
				wp_register_style('al2fb_style', $css_url);
				wp_enqueue_style('al2fb_style');
			}

			if (get_option(c_al2fb_option_use_ssp) || is_admin())
				wp_enqueue_script('jquery');

			// Social share privacy
			if (get_option(c_al2fb_option_use_ssp))
				wp_enqueue_script('socialshareprivacy', $this->plugin_url . '/js/jquery.socialshareprivacy.js', array('jquery'));

			// Check user capability
			if (current_user_can(get_option(c_al2fb_option_min_cap))) {
				if (is_admin()) {
					// Initiate Facebook authorization
					if (isset($_REQUEST['al2fb_action']) && $_REQUEST['al2fb_action'] == 'init') {
						// Debug info
						update_option(c_al2fb_log_redir_init, date('c'));

						// Get current user
						global $user_ID;
						get_currentuserinfo();

						// Redirect
						$auth_url = self::Authorize_url($user_ID);
						try {
							// Check
							if (ini_get('safe_mode') || ini_get('open_basedir') || $this->debug)
								update_option(c_al2fb_log_redir_check, 'No');
							else {
								$response = self::Request($auth_url, '', 'GET');
								update_option(c_al2fb_log_redir_check, date('c'));
							}
							// Redirect
							wp_redirect($auth_url);
							exit();
						}
						catch (Exception $e) {
							// Register error
							update_option(c_al2fb_log_redir_check, $e->getMessage());
							update_option(c_al2fb_last_error, $e->getMessage());
							update_option(c_al2fb_last_error_time, date('c'));
							// Redirect
							$error_url = admin_url('tools.php?page=' . plugin_basename($this->main_file));
							$error_url .= '&al2fb_action=error';
							$error_url .= '&error=' . urlencode($e->getMessage());
							wp_redirect($error_url);
							exit();
						}
					}
				}

				// Handle Facebook authorization
				self::Authorize();
			}
		}

		// Display admin messages
		function Admin_notices() {
			// Check user capability
			if (current_user_can(get_option(c_al2fb_option_min_cap))) {
				// Get current user
				global $user_ID;
				get_currentuserinfo();

				// Check actions
				if (isset($_REQUEST['al2fb_action'])) {
					// Configuration
					if ($_REQUEST['al2fb_action'] == 'config')
						self::Action_config();

					// Authorization
					else if ($_REQUEST['al2fb_action'] == 'authorize')
						self::Action_authorize();

					// Mail debug info
					else if ($_REQUEST['al2fb_action'] == 'mail')
						self::Action_mail();
				}

				self::Check_config();
			}
		}

		// Save settings
		function Action_config() {
			// Security check
			check_admin_referer(c_al2fb_nonce_form);

			// Get current user
			global $user_ID;
			get_currentuserinfo();

			// Default values
			$consts = get_defined_constants(true);
			foreach ($consts['user'] as $name => $value) {
				if (strpos($value, 'al2fb_') === 0 && $value != c_al2fb_meta_trailer)
					if (is_string($_POST[$value]))
						$_POST[$value] = trim($_POST[$value]);
					else if (empty($_POST[$value]))
						$_POST[$value] = null;
			}

			if (empty($_POST[c_al2fb_meta_picture_type]))
				$_POST[c_al2fb_meta_picture_type] = 'post';

			// Prevent losing selected page
			if (!self::Is_authorized($user_ID) ||
				(get_user_meta($user_ID, c_al2fb_meta_use_groups, true) &&
				get_user_meta($user_ID, c_al2fb_meta_group, true)))
				$_POST[c_al2fb_meta_page] = get_user_meta($user_ID, c_al2fb_meta_page, true);

			// Prevent losing selected group
			if (!self::Is_authorized($user_ID) || !get_user_meta($user_ID, c_al2fb_meta_use_groups, true))
				$_POST[c_al2fb_meta_group] = get_user_meta($user_ID, c_al2fb_meta_group, true);

			// App ID or secret changed
			if (get_user_meta($user_ID, c_al2fb_meta_client_id, true) != $_POST[c_al2fb_meta_client_id] ||
				get_user_meta($user_ID, c_al2fb_meta_app_secret, true) != $_POST[c_al2fb_meta_app_secret])
				delete_user_meta($user_ID, c_al2fb_meta_access_token);

			// Page owner changed
			if ($_POST[c_al2fb_meta_page_owner] && !get_user_meta($user_ID, c_al2fb_meta_page_owner, true))
				delete_user_meta($user_ID, c_al2fb_meta_access_token);

			// Use groups changed
			if ($_POST[c_al2fb_meta_use_groups] && !get_user_meta($user_ID, c_al2fb_meta_use_groups, true))
				if (!get_user_meta($user_ID, c_al2fb_meta_group, true))
					delete_user_meta($user_ID, c_al2fb_meta_access_token);

			// Like or send button enabled
			if ((!get_user_meta($user_ID, c_al2fb_meta_post_like_button, true) && !empty($_POST[c_al2fb_meta_post_like_button])) ||
				(!get_user_meta($user_ID, c_al2fb_meta_post_send_button, true) && !empty($_POST[c_al2fb_meta_post_send_button])))
				$_POST[c_al2fb_meta_open_graph] = true;

			// Update user options
			update_user_meta($user_ID, c_al2fb_meta_client_id, $_POST[c_al2fb_meta_client_id]);
			update_user_meta($user_ID, c_al2fb_meta_app_secret, $_POST[c_al2fb_meta_app_secret]);
			update_user_meta($user_ID, c_al2fb_meta_picture_type, $_POST[c_al2fb_meta_picture_type]);
			update_user_meta($user_ID, c_al2fb_meta_picture, $_POST[c_al2fb_meta_picture]);
			update_user_meta($user_ID, c_al2fb_meta_picture_default, $_POST[c_al2fb_meta_picture_default]);
			update_user_meta($user_ID, c_al2fb_meta_page, $_POST[c_al2fb_meta_page]);
			update_user_meta($user_ID, c_al2fb_meta_page_owner, $_POST[c_al2fb_meta_page_owner]);
			update_user_meta($user_ID, c_al2fb_meta_use_groups, $_POST[c_al2fb_meta_use_groups]);
			update_user_meta($user_ID, c_al2fb_meta_group, $_POST[c_al2fb_meta_group]);
			update_user_meta($user_ID, c_al2fb_meta_caption, $_POST[c_al2fb_meta_caption]);
			update_user_meta($user_ID, c_al2fb_meta_msg, $_POST[c_al2fb_meta_msg]);
			update_user_meta($user_ID, c_al2fb_meta_shortlink, $_POST[c_al2fb_meta_shortlink]);
			update_user_meta($user_ID, c_al2fb_meta_add_new_page, $_POST[c_al2fb_meta_add_new_page]);
			update_user_meta($user_ID, c_al2fb_meta_trailer, $_POST[c_al2fb_meta_trailer]);
			update_user_meta($user_ID, c_al2fb_meta_hyperlink, $_POST[c_al2fb_meta_hyperlink]);
			update_user_meta($user_ID, c_al2fb_meta_share_link, $_POST[c_al2fb_meta_share_link]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments, $_POST[c_al2fb_meta_fb_comments]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments_postback, $_POST[c_al2fb_meta_fb_comments_postback]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments_copy, $_POST[c_al2fb_meta_fb_comments_copy]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, $_POST[c_al2fb_meta_fb_comments_nolink]);
			update_user_meta($user_ID, c_al2fb_meta_fb_likes, $_POST[c_al2fb_meta_fb_likes]);
			update_user_meta($user_ID, c_al2fb_meta_post_likers, $_POST[c_al2fb_meta_post_likers]);
			update_user_meta($user_ID, c_al2fb_meta_post_like_button, $_POST[c_al2fb_meta_post_like_button]);
			update_user_meta($user_ID, c_al2fb_meta_like_nohome, $_POST[c_al2fb_meta_like_nohome]);
			update_user_meta($user_ID, c_al2fb_meta_like_noposts, $_POST[c_al2fb_meta_like_noposts]);
			update_user_meta($user_ID, c_al2fb_meta_like_nopages, $_POST[c_al2fb_meta_like_nopages]);
			update_user_meta($user_ID, c_al2fb_meta_like_noarchives, $_POST[c_al2fb_meta_like_noarchives]);
			update_user_meta($user_ID, c_al2fb_meta_like_nocategories, $_POST[c_al2fb_meta_like_nocategories]);
			update_user_meta($user_ID, c_al2fb_meta_like_layout, $_POST[c_al2fb_meta_like_layout]);
			update_user_meta($user_ID, c_al2fb_meta_like_faces, $_POST[c_al2fb_meta_like_faces]);
			update_user_meta($user_ID, c_al2fb_meta_like_width, $_POST[c_al2fb_meta_like_width]);
			update_user_meta($user_ID, c_al2fb_meta_like_action, $_POST[c_al2fb_meta_like_action]);
			update_user_meta($user_ID, c_al2fb_meta_like_font, $_POST[c_al2fb_meta_like_font]);
			update_user_meta($user_ID, c_al2fb_meta_like_colorscheme, $_POST[c_al2fb_meta_like_colorscheme]);
			update_user_meta($user_ID, c_al2fb_meta_like_link, $_POST[c_al2fb_meta_like_link]);
			update_user_meta($user_ID, c_al2fb_meta_like_top, $_POST[c_al2fb_meta_like_top]);
			update_user_meta($user_ID, c_al2fb_meta_post_send_button, $_POST[c_al2fb_meta_post_send_button]);
			update_user_meta($user_ID, c_al2fb_meta_post_combine_buttons, $_POST[c_al2fb_meta_post_combine_buttons]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_width, $_POST[c_al2fb_meta_like_box_width]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_border, $_POST[c_al2fb_meta_like_box_border]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_noheader, $_POST[c_al2fb_meta_like_box_noheader]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_nostream, $_POST[c_al2fb_meta_like_box_nostream]);
			update_user_meta($user_ID, c_al2fb_meta_comments_posts, $_POST[c_al2fb_meta_comments_posts]);
			update_user_meta($user_ID, c_al2fb_meta_comments_width, $_POST[c_al2fb_meta_comments_width]);
			update_user_meta($user_ID, c_al2fb_meta_comments_auto, $_POST[c_al2fb_meta_comments_auto]);
			update_user_meta($user_ID, c_al2fb_meta_pile_size, $_POST[c_al2fb_meta_pile_size]);
			update_user_meta($user_ID, c_al2fb_meta_pile_width, $_POST[c_al2fb_meta_pile_width]);
			update_user_meta($user_ID, c_al2fb_meta_pile_rows, $_POST[c_al2fb_meta_pile_rows]);
			update_user_meta($user_ID, c_al2fb_meta_reg_width, $_POST[c_al2fb_meta_reg_width]);
			update_user_meta($user_ID, c_al2fb_meta_login_width, $_POST[c_al2fb_meta_login_width]);
			update_user_meta($user_ID, c_al2fb_meta_login_regurl, $_POST[c_al2fb_meta_login_regurl]);
			update_user_meta($user_ID, c_al2fb_meta_login_redir, $_POST[c_al2fb_meta_login_redir]);
			update_user_meta($user_ID, c_al2fb_meta_login_html, $_POST[c_al2fb_meta_login_html]);
			update_user_meta($user_ID, c_al2fb_meta_act_width, $_POST[c_al2fb_meta_act_width]);
			update_user_meta($user_ID, c_al2fb_meta_act_height, $_POST[c_al2fb_meta_act_height]);
			update_user_meta($user_ID, c_al2fb_meta_act_header, $_POST[c_al2fb_meta_act_header]);
			update_user_meta($user_ID, c_al2fb_meta_act_recommend, $_POST[c_al2fb_meta_act_recommend]);
			update_user_meta($user_ID, c_al2fb_meta_open_graph, $_POST[c_al2fb_meta_open_graph]);
			update_user_meta($user_ID, c_al2fb_meta_open_graph_type, $_POST[c_al2fb_meta_open_graph_type]);
			update_user_meta($user_ID, c_al2fb_meta_open_graph_admins, $_POST[c_al2fb_meta_open_graph_admins]);
			update_user_meta($user_ID, c_al2fb_meta_exclude_default, $_POST[c_al2fb_meta_exclude_default]);
			update_user_meta($user_ID, c_al2fb_meta_not_post_list, $_POST[c_al2fb_meta_not_post_list]);
			update_user_meta($user_ID, c_al2fb_meta_fb_encoding, $_POST[c_al2fb_meta_fb_encoding]);
			update_user_meta($user_ID, c_al2fb_meta_fb_locale, $_POST[c_al2fb_meta_fb_locale]);
			update_user_meta($user_ID, c_al2fb_meta_donated, $_POST[c_al2fb_meta_donated]);
			update_user_meta($user_ID, c_al2fb_meta_rated, $_POST[c_al2fb_meta_rated]);
			if ($_POST[c_al2fb_meta_rated])
				delete_user_meta($user_ID, c_al2fb_meta_rated0);

			if (isset($_REQUEST['debug'])) {
				if (empty($_POST[c_al2fb_meta_access_token]))
					$_POST[c_al2fb_meta_access_token] = null;
				$_POST[c_al2fb_meta_access_token] = trim($_POST[c_al2fb_meta_access_token]);
				update_user_meta($user_ID, c_al2fb_meta_access_token, $_POST[c_al2fb_meta_access_token]);
			}

			// Update admin options
			if (current_user_can('manage_options')) {
				if (empty($_POST[c_al2fb_option_app_share]))
					$_POST[c_al2fb_option_app_share] = null;
				else
					$_POST[c_al2fb_option_app_share] = $user_ID;
				if (is_multisite())
					update_site_option(c_al2fb_option_app_share, $_POST[c_al2fb_option_app_share]);
				else
					update_option(c_al2fb_option_app_share, $_POST[c_al2fb_option_app_share]);

				update_option(c_al2fb_option_timeout, $_POST[c_al2fb_option_timeout]);
				update_option(c_al2fb_option_nonotice, $_POST[c_al2fb_option_nonotice]);
				update_option(c_al2fb_option_min_cap, $_POST[c_al2fb_option_min_cap]);
				update_option(c_al2fb_option_min_cap_comment, $_POST[c_al2fb_option_min_cap_comment]);
				update_option(c_al2fb_option_msg_refresh, $_POST[c_al2fb_option_msg_refresh]);
				update_option(c_al2fb_option_msg_maxage, $_POST[c_al2fb_option_msg_maxage]);
				update_option(c_al2fb_option_cron_enabled, $_POST[c_al2fb_option_cron_enabled]);
				update_option(c_al2fb_option_max_descr, $_POST[c_al2fb_option_max_descr]);
				update_option(c_al2fb_option_max_text, $_POST[c_al2fb_option_max_text]);
				update_option(c_al2fb_option_exclude_type, $_POST[c_al2fb_option_exclude_type]);
				update_option(c_al2fb_option_exclude_cat, $_POST[c_al2fb_option_exclude_cat]);
				update_option(c_al2fb_option_exclude_tag, $_POST[c_al2fb_option_exclude_tag]);
				update_option(c_al2fb_option_exclude_author, $_POST[c_al2fb_option_exclude_author]);
				update_option(c_al2fb_option_metabox_type, $_POST[c_al2fb_option_metabox_type]);
				update_option(c_al2fb_option_noverifypeer, $_POST[c_al2fb_option_noverifypeer]);
				update_option(c_al2fb_option_shortcode_widget, $_POST[c_al2fb_option_shortcode_widget]);
				update_option(c_al2fb_option_noshortcode, $_POST[c_al2fb_option_noshortcode]);
				update_option(c_al2fb_option_nofilter, $_POST[c_al2fb_option_nofilter]);
				update_option(c_al2fb_option_nofilter_comments, $_POST[c_al2fb_option_nofilter_comments]);
				update_option(c_al2fb_option_use_ssp, $_POST[c_al2fb_option_use_ssp]);
				update_option(c_al2fb_option_ssp_info, $_POST[c_al2fb_option_ssp_info]);
				update_option(c_al2fb_option_filter_prio, $_POST[c_al2fb_option_filter_prio]);
				update_option(c_al2fb_option_noscript, $_POST[c_al2fb_option_noscript]);
				update_option(c_al2fb_option_clean, $_POST[c_al2fb_option_clean]);
				update_option(c_al2fb_option_css, $_POST[c_al2fb_option_css]);

				if (isset($_REQUEST['debug'])) {
					update_option(c_al2fb_option_siteurl, $_POST[c_al2fb_option_siteurl]);
					update_option(c_al2fb_option_nocurl, $_POST[c_al2fb_option_nocurl]);
					update_option(c_al2fb_option_use_pp, $_POST[c_al2fb_option_use_pp]);
					update_option(c_al2fb_option_debug, $_POST[c_al2fb_option_debug]);
				}
			}

			// Show result
			echo '<div id="message" class="updated fade al2fb_notice"><p>' . __('Settings updated', c_al2fb_text_domain) . '</p></div>';
		}

		// Get token
		function Action_authorize() {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			// Server-side flow authorization
			if (isset($_REQUEST['code'])) {
				try {
					// Get & store token
					$access_token = self::Get_fb_token($user_ID);
					update_option(c_al2fb_log_auth_time, date('c'));
					update_user_meta($user_ID, c_al2fb_meta_access_token, $access_token);
					if (get_option(c_al2fb_option_version) <= 6)
						update_option(c_al2fb_option_version, 7);
					delete_option(c_al2fb_last_error);
					delete_option(c_al2fb_last_error_time);
					echo '<div id="message" class="updated fade al2fb_notice"><p>' . __('Authorized, go posting!', c_al2fb_text_domain) . '</p></div>';
				}
				catch (Exception $e) {
					delete_user_meta($user_ID, c_al2fb_meta_access_token);
					update_option(c_al2fb_last_error, $e->getMessage());
					update_option(c_al2fb_last_error_time, date('c'));
					echo '<div id="message" class="error fade al2fb_error"><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, get_bloginfo('charset')) . '</p></div>';
				}
			}

			// Authorization error
			else if (isset($_REQUEST['error'])) {
				delete_user_meta($user_ID, c_al2fb_meta_access_token);
				$faq = 'http://wordpress.org/extend/plugins/add-link-to-facebook/faq/';
				$msg = stripslashes($_REQUEST['error_description']);
				$msg .= ' error: ' . stripslashes($_REQUEST['error']);
				$msg .= ' reason: ' . stripslashes($_REQUEST['error_reason']);
				update_option(c_al2fb_last_error, $msg);
				update_option(c_al2fb_last_error_time, date('c'));
				$msg .= '<br /><br />Most errors are described in <a href="' . $faq . '" target="_blank">the FAQ</a>';
				echo '<div id="message" class="error fade al2fb_error"><p>' . htmlspecialchars($msg, ENT_QUOTES, get_bloginfo('charset')) . '</p></div>';
			}
		}

		// Send debug info
		function Action_mail() {
			// Check security
			check_admin_referer(c_al2fb_nonce_form);
			require_once('add-link-to-facebook-debug.php');

			if (empty($_POST[c_al2fb_mail_topic]) ||
				!(strpos($_POST[c_al2fb_mail_topic], 'http://') === 0 ||
				strpos($_POST[c_al2fb_mail_topic], 'https://') === 0))
				echo '<div id="message" class="error fade al2fb_error"><p>' . __('Forum topic link is mandatory', c_al2fb_text_domain) . '</p></div>';
			else {
				// Build headers
				$headers = 'From: ' . stripslashes($_POST[c_al2fb_mail_name]) . ' <' . stripslashes($_POST[c_al2fb_mail_email]) . '>' . "\r\n";
				$headers .= 'Reply-To: ' . stripslashes($_POST[c_al2fb_mail_name]) . ' <' . stripslashes($_POST[c_al2fb_mail_email]) . '>' . "\r\n";
				//$headers .= 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=' . get_bloginfo('charset') . "\r\n";

				// Build message
				$message = '<html><head><title>Add Link to Facebook</title></head><body>';
				$message .= '<p>' . nl2br(htmlspecialchars(stripslashes($_POST[c_al2fb_mail_msg]), ENT_QUOTES, get_bloginfo('charset'))) . '</p>';
				$message .= '<a href="' . stripslashes($_POST[c_al2fb_mail_topic]) . '">' . stripslashes($_POST[c_al2fb_mail_topic]) . '</a>';
				$message .= '<hr />';
				$message .= al2fb_debug_info($this);
				$message .= '<hr />';
				$message .= '</body></html>';
				if (wp_mail('al2fb@bokhorst.biz', '[Add Link to Facebook] Debug information', $message, $headers)) {
					echo '<div id="message" class="updated fade al2fb_notice"><p>' . __('Debug information sent', c_al2fb_text_domain) . '</p></div>';
					if ($this->debug)
						echo '<pre>' . nl2br(htmlspecialchars($headers, ENT_QUOTES, get_bloginfo('charset'))) . '</pre>';
				}
				else
					echo '<div id="message" class="error fade al2fb_error"><p>' . __('Sending debug information failed', c_al2fb_text_domain) . '</p></div>';
			}
		}

		// Display notices
		function Check_config() {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			// Check config/authorization
			$uri = $_SERVER['REQUEST_URI'];
			$url = 'tools.php?page=' . plugin_basename($this->main_file);

			$nonotice = get_option(c_al2fb_option_nonotice);
			if (is_multisite())
				$nonotice = $nonotice || get_site_option(c_al2fb_option_app_share);
			else
				$nonotice = $nonotice || get_option(c_al2fb_option_app_share);
			$donotice = ($nonotice ? strpos($uri, $url) !== false : true);

			if ($donotice) {
				if (!get_user_meta($user_ID, c_al2fb_meta_client_id, true) ||
					!get_user_meta($user_ID, c_al2fb_meta_app_secret, true)) {
					$notice = __('needs configuration', c_al2fb_text_domain);
					$anchor = 'configure';
				}
				else if (!self::Is_authorized($user_ID)) {
					$notice = __('needs authorization', c_al2fb_text_domain);
					$anchor = 'authorize';
				}
				else {
					$version = get_option(c_al2fb_option_version);
					if ($version && $version <= 6) {
						$notice = __('should be authorized again to show Facebook messages in the widget', c_al2fb_text_domain);
						$anchor = 'authorize';
					}
				}
				if (!empty($notice)) {
					echo '<div class="error fade al2fb_error"><p>';
					_e('Add Link to Facebook', c_al2fb_text_domain);
					echo ' <a href="' . $url . '#' . $anchor . '">' . $notice . '</a></p></div>';
				}
			}

			// Check for error
			if (isset($_REQUEST['al2fb_action']) && $_REQUEST['al2fb_action'] == 'error') {
				$faq = 'http://wordpress.org/extend/plugins/add-link-to-facebook/faq/';
				$msg = htmlspecialchars(stripslashes($_REQUEST['error']), ENT_QUOTES, get_bloginfo('charset'));
				$msg .= '<br /><br />Most errors are described in <a href="' . $faq . '" target="_blank">the FAQ</a>';
				echo '<div id="message" class="error fade al2fb_error"><p>' . $msg . '</p></div>';
			}

			// Check for post errors
			$posts = new WP_Query(array(
				'author' => $user_ID,
				'meta_key' => c_al2fb_meta_error,
				'posts_per_page' => 5));
			while ($posts->have_posts()) {
				$posts->next_post();
				$error = get_post_meta($posts->post->ID, c_al2fb_meta_error, true);
				if (!empty($error)) {
					echo '<div id="message" class="error fade al2fb_error"><p>';
					echo __('Add Link to Facebook', c_al2fb_text_domain) . ' - ';
					edit_post_link(get_the_title($posts->post->ID), null, null, $posts->post->ID);
					echo ': ' . htmlspecialchars($error, ENT_QUOTES, get_bloginfo('charset'));
					echo '@ ' . get_post_meta($posts->post->ID, c_al2fb_meta_error_time, true);
					echo '</p></div>';
				}
			}

			// Check for rating notice
			if ($donotice && !get_user_meta($user_ID, c_al2fb_meta_rated, true)) {
				echo '<div id="message" class="error fade al2fb_error"><p>';
				$msg = __('If you like the Add Link to Facebook plugin, please rate it on <a href="[wordpress]" target="_blank">wordpress.org</a>.<br />If the average rating is low, it makes no sense to support this plugin any longer.<br />You can disable this notice by checking the option "I have rated this plugin" on the <a href="[settings]">settings page</a>.', c_al2fb_text_domain);
				if (get_user_meta($user_ID, c_al2fb_meta_rated0, true)) {
					$msg .= '<br /><br /><em>';
					$msg .= __('Through a mishap on the WordPress.org systems, previous ratings for the plugin were lost.<br />If you\'ve rated the plugin in the past, your rating was accidentally removed.<br />So if you would be so kind as to rate the plugin again, I\'d appreciate it. Thanks!', c_al2fb_text_domain);
					$msg .= '</em>';
				}
				$msg = str_replace('[wordpress]', 'http://wordpress.org/extend/plugins/add-link-to-facebook/', $msg);
				$msg = str_replace('[settings]', $url . '&rate', $msg);
				echo $msg . '</p></div>';
			}
		}

		// Register options page
		function Admin_menu() {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			if (function_exists('add_management_page'))
				add_management_page(
					__('Add Link to Facebook', c_al2fb_text_domain) . ' ' . __('Administration', c_al2fb_text_domain),
					__('Add Link to Facebook', c_al2fb_text_domain),
					get_option(c_al2fb_option_min_cap),
					$this->main_file,
					array(&$this, 'Administration'));
		}

		function Plugin_action_links($links, $file) {
			if ($file == plugin_basename($this->main_file)) {
				if (current_user_can(get_option(c_al2fb_option_min_cap))) {
					// Get current user
					global $user_ID;
					get_currentuserinfo();

					// Check for shared app
					if (is_multisite())
						$shared_user_ID = get_site_option(c_al2fb_option_app_share);
					else
						$shared_user_ID = get_option(c_al2fb_option_app_share);
					if (!$shared_user_ID || $shared_user_ID == $user_ID) {
						// Add settings link
						$config_url = admin_url('tools.php?page=' . plugin_basename($this->main_file));
						$links[] = '<a href="' . $config_url . '">' . __('Settings', c_al2fb_text_domain) . '</a>';
					}
				}
			}
			return $links;
		}

		// Handle option page
		function Administration() {
			// Security check
			if (!current_user_can(get_option(c_al2fb_option_min_cap)))
				die('Unauthorized');

			require_once('add-link-to-facebook-admin.php');
			al2fb_render_admin($this);
		}

		// Get Facebook authorize address
		function Authorize_url($user_ID) {
			// http://developers.facebook.com/docs/authentication/permissions
			$url = 'https://graph.facebook.com/oauth/authorize';
			$url = apply_filters('al2fb_url', $url);
			$url .= '?client_id=' . urlencode(get_user_meta($user_ID, c_al2fb_meta_client_id, true));
			$url .= '&redirect_uri=' . urlencode(self::Redirect_uri());
			$url .= '&scope=read_stream,publish_stream,offline_access';

			if (get_user_meta($user_ID, c_al2fb_meta_page_owner, true))
				$url .= ',manage_pages';

			if (get_user_meta($user_ID, c_al2fb_meta_use_groups, true))
				$url .= ',user_groups';

			$url .= '&state=' . self::Authorize_secret();
			return $url;
		}

		// Get Facebook return addess
		function Redirect_uri() {
			// WordPress Address -> get_site_url() -> WordPress folder
			// Blog Address -> get_home_url() -> Home page
			if (get_option(c_al2fb_option_siteurl))
				return get_site_url(null, '/');
			else
				return get_home_url(null, '/');
		}

		// Generate authorization secret
		function Authorize_secret() {
			return 'al2fb_auth_' . substr(md5(AUTH_KEY ? AUTH_KEY : get_bloginfo('url')), 0, 10);
		}

		// Handle Facebook authorization
		function Authorize() {
			parse_str($_SERVER['QUERY_STRING'], $query);
			if (isset($query['state']) && strpos($query['state'], self::Authorize_secret()) !== false) {
				// Build new url
				$query['state'] = '';
				$query['al2fb_action'] = 'authorize';
				$url = admin_url('tools.php?page=' . plugin_basename($this->main_file));
				$url .= '&' . http_build_query($query, '', '&');

				// Debug info
				update_option(c_al2fb_log_redir_time, date('c'));
				update_option(c_al2fb_log_redir_ref, (empty($_SERVER['HTTP_REFERER']) ? null : $_SERVER['HTTP_REFERER']));
				update_option(c_al2fb_log_redir_from, $_SERVER['REQUEST_URI']);
				update_option(c_al2fb_log_redir_to, $url);

				// Redirect
				wp_redirect($url);
				exit();
			}
		}

		// Request token
		function Get_fb_token($user_ID) {
			$url = 'https://graph.facebook.com/oauth/access_token';
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'client_id' => get_user_meta($user_ID, c_al2fb_meta_client_id, true),
				'redirect_uri' => self::Redirect_uri(),
				'client_secret' => get_user_meta($user_ID, c_al2fb_meta_app_secret, true),
				'code' => $_REQUEST['code']
			), '', '&');
			update_option(c_al2fb_log_get_token, $url . '?' . $query);
			$response = self::Request($url, $query, 'GET');
			$key = 'access_token=';
			$access_token = substr($response, strpos($response, $key) + strlen($key));
			$access_token = explode('&', $access_token);
			$access_token = $access_token[0];
			return $access_token;
		}

		// Get application properties
		function Get_fb_application($user_ID) {
			$app_id = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
			$url = 'https://graph.facebook.com/' . $app_id;
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'access_token' => get_user_meta($user_ID, c_al2fb_meta_access_token, true)
			), '', '&');
			$response = self::Request($url, $query, 'GET');
			$app = json_decode($response);
			return $app;
		}

		// Get wall, page or group name and cache
		function Get_fb_me_cached($user_ID, $self) {
			$page_id = self::Get_page_id($user_ID, $self);
			$me_key = c_al2fb_transient_cache . md5('me' . $user_ID . $page_id);
			$me = get_transient($me_key);
			if ($me === false) {
				$me = self::Get_fb_me($user_ID, $self);
				if ($me != null) {
					$duration = self::Get_duration(false);
					set_transient($me_key, $me, $duration);
				}
			}
			return $me;
		}

		// Get wall, page or group name
		function Get_fb_me($user_ID, $self) {
			$page_id = self::Get_page_id($user_ID, $self);
			$url = 'https://graph.facebook.com/' . $page_id;
			$url = apply_filters('al2fb_url', $url);
			$token = self::Get_access_token_by_page($user_ID, $page_id);
			if (empty($token))
				return null;
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = self::Request($url, $query, 'GET');
			$me = json_decode($response);
			if ($me) {
				if (empty($me->link))	// Group
					$me->link = 'http://www.facebook.com/home.php?sk=group_' . $page_id;
				return $me;
			}
			else
				throw new Exception('Page "' . $page_id . '" not found');
		}

		function Get_page_id($user_ID, $self) {
			if (get_user_meta($user_ID, c_al2fb_meta_use_groups, true))
				$page_id = get_user_meta($user_ID, c_al2fb_meta_group, true);
			if (empty($page_id))
				$page_id = get_user_meta($user_ID, c_al2fb_meta_page, true);
			if ($self || empty($page_id))
				$page_id = 'me';
			return $page_id;
		}

		// Get page list
		function Get_fb_pages($user_ID) {
			$url = 'https://graph.facebook.com/me/accounts';
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'access_token' => get_user_meta($user_ID, c_al2fb_meta_access_token, true)
			), '', '&');
			$response = self::Request($url, $query, 'GET');
			$accounts = json_decode($response);
			return $accounts;
		}

		// Get group list
		function Get_fb_groups($user_ID) {
			$url = 'https://graph.facebook.com/me/groups';
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'access_token' => get_user_meta($user_ID, c_al2fb_meta_access_token, true)
			), '', '&');
			$response = self::Request($url, $query, 'GET');
			$groups = json_decode($response);
			return $groups;
		}

		// Get comments and cache
		function Get_fb_comments_cached($user_ID, $link_id, $cached = true) {
			$fb_key = c_al2fb_transient_cache . md5( 'c' . $link_id);
			$fb_comments = get_transient($fb_key);
			if ($this->debug || !$cached)
				$fb_comments = false;
			if ($fb_comments === false) {
				$fb_comments = self::Get_fb_comments($user_ID, $link_id);
				$duration = self::Get_duration(true);
				set_transient($fb_key, $fb_comments, $duration);
			}
			return $fb_comments;
		}

		// Get comments
		function Get_fb_comments($user_ID, $id) {
			$url = 'https://graph.facebook.com/' . $id . '/comments';
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'access_token' => get_user_meta($user_ID, c_al2fb_meta_access_token, true)
			), '', '&');
			$response = self::Request($url, $query, 'GET');
			$comments = json_decode($response);
			$comments = apply_filters('al2fb_fb_comments', $comments);
			return $comments;
		}

		// Get likes and cache
		function Get_fb_likes_cached($user_ID, $link_id, $cached = true) {
			$fb_key = c_al2fb_transient_cache . md5('l' . $link_id);
			$fb_likes = get_transient($fb_key);
			if ($this->debug || !$cached)
				$fb_likes = false;
			if ($fb_likes === false) {
				$fb_likes = self::Get_fb_likes($user_ID, $link_id);
				$duration = self::Get_duration(true);
				set_transient($fb_key, $fb_likes, $duration);
			}
			return $fb_likes;
		}

		// Get likes
		function Get_fb_likes($user_ID, $id) {
			$url = 'https://graph.facebook.com/' . $id . '/likes';
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'access_token' => get_user_meta($user_ID, c_al2fb_meta_access_token, true)
			), '', '&');
			$response = self::Request($url, $query, 'GET');
			$likes = json_decode($response);
			$likes = apply_filters('al2fb_fb_likes', $likes);
			return $likes;
		}

		// Get messages and cache
		function Get_fb_feed_cached($user_ID) {
			$page_id = self::Get_page_id($user_ID, false);
			$fb_key = c_al2fb_transient_cache . md5( 'f' . $user_ID . $page_id);
			$fb_feed = get_transient($fb_key);
			if ($this->debug)
				$fb_feed = false;
			if ($fb_feed === false) {
				$fb_feed = self::Get_fb_feed($user_ID);
				$duration = self::Get_duration(false);
				set_transient($fb_key, $fb_feed, $duration);
			}
			return $fb_feed;
		}

		// Get messages
		function Get_fb_feed($user_ID) {
			$page_id = self::Get_page_id($user_ID, false);
			$url = 'https://graph.facebook.com/' . $page_id . '/feed';
			$url = apply_filters('al2fb_url', $url);
			$token = self::Get_access_token_by_page($user_ID, $page_id);
			if (empty($token))
				return null;

			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = self::Request($url, $query, 'GET');
			$posts = json_decode($response);
			$posts = apply_filters('al2fb_fb_feed', $posts);
			return $posts;
		}

		// Filter messages
		function Filter_feed($fb_messages) {
			if (isset($fb_messages) && isset($fb_messages->data))
				for ($i = 0; $i < count($fb_messages->data); $i++)
					if ($fb_messages->data[$i]->type != 'status')
						unset($fb_messages->data[$i]);
			return $fb_messages;
		}

		// Get Facebook picture
		function Get_fb_picture_url_cached($id, $size) {
			$fb_key = c_al2fb_transient_cache . md5('p' . $id);
			$fb_url = get_transient($fb_key);
			if ($this->debug)
				$fb_url = false;
			if ($fb_url === false) {
				$fb_url = self::Get_fb_picture_url($id, 'normal');
				$duration = self::Get_duration(false);
				set_transient($fb_key, $fb_url, $duration);
			}
			return $fb_url;
		}

		// Subscribe comments
		function Subscribe_fb_page($user_ID) {
			// http://developers.facebook.com/docs/reference/api/realtime/
			$token = get_user_meta($user_ID, c_al2fb_meta_access_token, true);
			if (empty($token))
				return null;

			// Get application data
			$app_id = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
			$app_secret = get_user_meta($user_ID, c_al2fb_meta_app_secret, true);

			// Get application  token
			$url = 'https://graph.facebook.com/oauth/access_token';
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'client_id' => $app_id,
				'client_secret' => $app_secret,
				'grant_type' => 'client_credentials'
			), '', '&');
			$response = self::Request($url, $query, 'GET');

			// Decode application token
			$key = 'access_token=';
			$token = substr($response, strpos($response, $key) + strlen($key));
			$token = explode('&', $token);
			$token = $token[0];

			// Subscribe request
			$url = 'https://graph.facebook.com/' . $app_id . '/subscriptions';
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'access_token' => $token,
				'object' => 'user',
				'fields' => 'feed,likes',
				'callback_url' => self::Redirect_uri() . '?al2fb_subscription=true',
				'verify_token' => self::Authorize_secret()
			), '', '&');
			self::Request($url, $query, 'POST'); // no response

			// Check subscription
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = self::Request($url, $query, 'GET');
			$subscription = json_decode($response);
			return $subscription;
		}

		function Handle_fb_subscription() {
			if (isset($_REQUEST['hub_mode']) && $_REQUEST['hub_mode'] == 'subscribe') {
				if ($_REQUEST['hub_verify_token'] == self::Authorize_secret()) {
					header('Content-type: text/plain');
					echo $_REQUEST['hub_challenge'];
					exit();
				}
			}
			else {
				// Real-time update
			}
		}

		// Get Facebook picture
		// Returns a HTTP 302 with the URL of the user's profile picture
		// (use ?type=square | small | normal | large to request a different photo)
		function Get_fb_picture_url($id, $size) {
			$url = 'https://graph.facebook.com/' . $id . '/picture?' . $size;
			$url = apply_filters('al2fb_url', $url);
			if (function_exists('curl_init') && !get_option(c_al2fb_option_nocurl)) {
				$timeout = get_option(c_al2fb_option_timeout);
				if (!$timeout)
					$timeout = 25;

				$c = curl_init();
				curl_setopt($c, CURLOPT_URL, $url);
				curl_setopt($c, CURLOPT_HEADER, 1);
				curl_setopt($c, CURLOPT_NOBODY, 1);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
				$headers = curl_exec($c);
				curl_close ($c);
				if (preg_match('/Location: (.*)/', $headers, $location)) {
					$location = trim($location[1]);
					$location = apply_filters('al2fb_fb_picture', $location);
					return $location;
				}
				else
					return false;
			}
			else if (function_exists('get_header') && ini_get('allow_url_fopen')) {
				$headers = get_headers($url, true);
				if (isset($headers['Location'])) {
					$location = $headers['Location'];
					$location = apply_filters('al2fb_fb_picture', $location);
					return $location;
				}
				else
					return false;
			}
			else
				return false;
		}

		// Get cache duration
		function Get_duration($cron = false) {
			$duration = intval(get_option(c_al2fb_option_msg_refresh));
			if (!$duration)
				$duration = 10;
			if ($cron && get_option(c_al2fb_option_cron_enabled))
				$duration += 10;
			return $duration * 60;
		}

		// Add checkboxes
		function Post_submitbox_misc_actions() {
			global $post;

			// Check exclusion
			$ex_custom_types = explode(',', get_option(c_al2fb_option_exclude_type));
			if (in_array($post->post_type, $ex_custom_types))
				return;

			// Get user
			$user_ID = self::Get_user_ID($post);

			// Get exclude indication
			$exclude = get_post_meta($post->ID, c_al2fb_meta_exclude, true);
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			if (!$link_id && get_user_meta($user_ID, c_al2fb_meta_exclude_default, true))
				$exclude = true;
			$chk_exclude = ($exclude ? ' checked' : '');

			// Get no like button indication
			$chk_nolike = (get_post_meta($post->ID, c_al2fb_meta_nolike, true) ? ' checked' : '');
			$chk_nointegrate = (get_post_meta($post->ID, c_al2fb_meta_nointegrate, true) ? ' checked' : '');

			// Check if errors
			$error = get_post_meta($post->ID, c_al2fb_meta_error, true);

			global $wp_version;
			if (version_compare($wp_version, '3.2') < 0) {
?>
				<div class="misc-pub-section"></div>
<?php		} ?>
			<div class="al2fb_post_submit">
			<div class="misc-pub-section">
<?php
			wp_nonce_field(plugin_basename(__FILE__), c_al2fb_nonce_form);
?>
			<input id="al2fb_exclude" type="checkbox" name="<?php echo c_al2fb_meta_exclude; ?>"<?php echo $chk_exclude; ?> />
			<label for="al2fb_exclude"><?php _e('Do not add link to Facebook', c_al2fb_text_domain); ?></label>
			<br />
			<input id="al2fb_nolike" type="checkbox" name="<?php echo c_al2fb_meta_nolike; ?>"<?php echo $chk_nolike; ?> />
			<label for="al2fb_nolike"><?php _e('Do not add like button', c_al2fb_text_domain); ?></label>
			<br />
			<input id="al2fb_nointegrate" type="checkbox" name="<?php echo c_al2fb_meta_nointegrate; ?>"<?php echo $chk_nointegrate; ?> />
			<label for="al2fb_nointegrate"><?php _e('Do not integrate comments', c_al2fb_text_domain); ?></label>

<?php		if (!empty($link_id)) { ?>
				<br />
				<input id="al2fb_update" type="checkbox" name="<?php echo c_al2fb_action_update; ?>"/>
				<label for="al2fb_update"><?php _e('Update existing Facebook link', c_al2fb_text_domain); ?></label>
				<br />
				<span class="al2fb_explanation"><?php _e('Comments and likes will be lost!', c_al2fb_text_domain); ?></span>
				<br />
				<input id="al2fb_delete" type="checkbox" name="<?php echo c_al2fb_action_delete; ?>"/>
				<label for="al2fb_delete"><?php _e('Delete existing Facebook link', c_al2fb_text_domain); ?></label>
				<br />
				<a href="<?php echo self::Get_fb_permalink($link_id); ?>" target="_blank"><?php _e('Link on Facebook', c_al2fb_text_domain); ?></a>
<?php		} ?>
<?php		if (!empty($error)) { ?>
				<br />
				<input id="al2fb_clear" type="checkbox" name="<?php echo c_al2fb_action_clear; ?>"/>
				<label for="al2fb_clear"><?php _e('Clear error messages', c_al2fb_text_domain); ?></label>
<?php		} ?>
			</div>
			</div>
<?php
		}

		// Add post Facebook column
		function Manage_posts_columns($posts_columns) {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			if (current_user_can(get_option(c_al2fb_option_min_cap)) &&
				!get_user_meta($user_ID, c_al2fb_meta_not_post_list, true))
				$posts_columns['al2fb'] = __('Facebook', c_al2fb_text_domain);
			return $posts_columns;
		}

		function Is_recent($post) {
			// Maximum age for Facebook comments/likes
			$maxage = intval(get_option(c_al2fb_option_msg_maxage));
			if (!$maxage)
				$maxage = 7;

			// Link added time
			$link_time = strtotime(get_post_meta($post->ID, c_al2fb_meta_link_time, true));
			if ($link_time <= 0)
				$link_time = strtotime($post->post_date_gmt);

			$old = ($link_time + ($maxage * 24 * 60 * 60) < time());

			return !$old;
		}

		// Populate post facebook column
		function Manage_posts_custom_column($column_name, $post_ID) {
			if ($column_name == 'al2fb') {
				$link_id = get_post_meta($post_ID, c_al2fb_meta_link_id, true);
				if ($link_id)
					echo '<a href="' . self::Get_fb_permalink($link_id) . '" target="_blank">' . __('Yes', c_al2fb_text_domain) . '</a>';
				else
					echo '<span>' . __('No', c_al2fb_text_domain) . '</span>';

				if ($link_id) {
					$post = get_post($post_ID);
					if (self::Is_recent($post)) {
						$user_ID = self::Get_user_ID($post);

						// Show number of comments
						if (get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) {
							$fb_comments = self::Get_comments_or_likes($post, false);
							if (!empty($fb_comments))
								echo '<br /><span>' . count($fb_comments->data) . ' ' . __('comments', c_al2fb_text_domain) . '</span>';
						}

						// Show number of likes
						if (get_user_meta($user_ID, c_al2fb_meta_fb_likes, true)) {
							$fb_likes = self::Get_comments_or_likes($post, true);
							if (!empty($fb_likes))
								echo '<br /><span>' . count($fb_comments->data) . ' ' . __('likes', c_al2fb_text_domain) . '</span>';
						}
					}
				}
			}
		}

		// Add post meta box
		function Add_meta_boxes() {
			$types = explode(',', get_option(c_al2fb_option_metabox_type));
			$types[] = 'post';
			$types[] = 'page';
			foreach ($types as $type)
				add_meta_box(
					'al2fb_meta',
					__('Add Link to Facebook', c_al2fb_text_domain),
					array(&$this, 'Meta_box'),
					$type);
		}

		// Display attached image selector
		function Meta_box() {
			global $post;
			if (!empty($post)) {
				$user_ID = self::Get_user_ID($post);
				$texts = self::Get_texts($post);

				// Security
				wp_nonce_field(plugin_basename(__FILE__), c_al2fb_nonce_form);

				if ($this->debug) {
					echo '<strong>Type:</strong> ' . $post->post_type . '<br />';;
					$texts = self::Get_texts($post);
					echo '<strong>Original:</strong> ' . htmlspecialchars($post->post_content, ENT_QUOTES, get_bloginfo('charset')) . '<br />';
					echo '<strong>Processed:</strong> ' . htmlspecialchars($texts['content'], ENT_QUOTES, get_bloginfo('charset')) . '<br />';
				}

				if (function_exists('wp_get_attachment_image_src')) {
					// Get attached images
					$images = &get_children('post_type=attachment&post_mime_type=image&order=ASC&post_parent=' . $post->ID);
					if (empty($images))
						echo '<span>' . __('No images in the media library for this post', c_al2fb_text_domain) . '</span><br />';
					else {
						// Display image selector
						$image_id = get_post_meta($post->ID, c_al2fb_meta_image_id, true);

						// Header
						echo '<h4>' . __('Select link image:', c_al2fb_text_domain) . '</h4>';
						echo '<div class="al2fb_images">';

						// None
						echo '<div class="al2fb_image">';
						echo '<input type="radio" name="al2fb_image_id" id="al2fb_image_0"';
						if (empty($image_id))
							echo ' checked';
						echo ' value="0">';
						echo '<br />';
						echo '<label for="al2fb_image_0">';
						echo __('None', c_al2fb_text_domain) . '</label>';
						echo '</div>';

						// Images
						if ($images)
							foreach ($images as $attachment_id => $attachment) {
								$picture = wp_get_attachment_image_src($attachment_id, 'thumbnail');

								echo '<div class="al2fb_image">';
								echo '<input type="radio" name="al2fb_image_id" id="al2fb_image_' . $attachment_id . '"';
								if ($attachment_id == $image_id)
									echo ' checked';
								echo ' value="' . $attachment_id . '">';
								echo '<br />';
								echo '<label for="al2fb_image_' . $attachment_id . '">';
								echo '<img src="' . $picture[0] . '" alt=""></label>';
								echo '<br />';
								echo '<span>' . $picture[1] . ' x ' . $picture[2] . '</span>';
								echo '</div>';
							}
						echo '</div>';
					}
				}
				else
					echo 'wp_get_attachment_image_src does not exist';

				if ($this->debug)
					echo '<p>' . print_r($texts, true) . '</p>';

				// Custom excerpt
				$excerpt = get_post_meta($post->ID, c_al2fb_meta_excerpt, true);
				echo '<h4>' . __('Custom excerpt', c_al2fb_text_domain) . '</h4>';
				echo '<textarea id="al2fb_excerpt" name="al2fb_excerpt" cols="40" rows="1" class="attachmentlinks">';
				echo $excerpt . '</textarea>';

				// Custom text
				$text = get_post_meta($post->ID, c_al2fb_meta_text, true);
				echo '<h4>' . __('Custom text', c_al2fb_text_domain) . '</h4>';
				echo '<textarea id="al2fb_text" name="al2fb_text" cols="40" rows="1" class="attachmentlinks">';
				echo $text . '</textarea>';

				echo '<h4>' . __('Link picture', c_al2fb_text_domain) . '</h4>';

				$picture_info = self::Get_link_picture($post, $user_ID);
				if (!empty($picture_info['picture']))
					echo '<img src="' . $picture_info['picture'] . '" alt="Link picture">';
				if ($this->debug)
					echo '<br /><span style="font-size: smaller;">' . $picture_info['picture_type'] . '</span>';
			}
		}

		// Save indications & selected attached image
		function Save_post($post_id) {
			if ($this->debug)
				add_post_meta($post_id, c_al2fb_meta_log, date('c') . ' Save post');

			// Security checks
			$nonce = (isset($_POST[c_al2fb_nonce_form]) ? $_POST[c_al2fb_nonce_form] : null);
			if (!wp_verify_nonce($nonce, plugin_basename(__FILE__)))
				return $post_id;
			if (!current_user_can('edit_post', $post_id))
				return $post_id;

			// Skip auto save
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				return $post_id;

			// Check exclusion
			$post = get_post($post_id);
			$ex_custom_types = explode(',', get_option(c_al2fb_option_exclude_type));
			if (in_array($post->post_type, $ex_custom_types))
				return $post_id;

			// Process exclude indication
			if (isset($_POST[c_al2fb_meta_exclude]) && $_POST[c_al2fb_meta_exclude])
				update_post_meta($post_id, c_al2fb_meta_exclude, true);
			else
				delete_post_meta($post_id, c_al2fb_meta_exclude);

			// Process no like indication
			if (isset($_POST[c_al2fb_meta_nolike]) && $_POST[c_al2fb_meta_nolike])
				update_post_meta($post_id, c_al2fb_meta_nolike, true);
			else
				delete_post_meta($post_id, c_al2fb_meta_nolike);

			// Process no integrate indication
			if (isset($_POST[c_al2fb_meta_nointegrate]) && $_POST[c_al2fb_meta_nointegrate])
				update_post_meta($post_id, c_al2fb_meta_nointegrate, true);
			else
				delete_post_meta($post_id, c_al2fb_meta_nointegrate);

			// Clear errors
			if (isset($_POST[c_al2fb_action_clear]) && $_POST[c_al2fb_action_clear]) {
				delete_post_meta($post_id, c_al2fb_meta_error);
				delete_post_meta($post_id, c_al2fb_meta_error_time);
			}

			// Persist data
			if (empty($_POST['al2fb_image_id']))
				delete_post_meta($post_id, c_al2fb_meta_image_id);
			else
				update_post_meta($post_id, c_al2fb_meta_image_id, $_POST['al2fb_image_id']);

			if (isset($_POST['al2fb_excerpt']) && !empty($_POST['al2fb_excerpt']))
				update_post_meta($post_id, c_al2fb_meta_excerpt, trim($_POST['al2fb_excerpt']));
			else
				delete_post_meta($post_id, c_al2fb_meta_excerpt);

			if (isset($_POST['al2fb_text']) && !empty($_POST['al2fb_text']))
				update_post_meta($post_id, c_al2fb_meta_text, trim($_POST['al2fb_text']));
			else
				delete_post_meta($post_id, c_al2fb_meta_text);
		}

		// Remote publish & custom action
		function Remote_publish($post_ID) {
			if ($this->debug)
				add_post_meta($post_ID, c_al2fb_meta_log, date('c') . ' Remote publish');

			$post = get_post($post_ID);

			// Only if published
			if ($post->post_status == 'publish')
				self::Publish_post($post);
		}

		// Workaround
		function Future_to_publish($post_ID) {
			if ($this->debug)
				add_post_meta($post_ID, c_al2fb_meta_log, date('c') . ' Future to publish');

			$post = get_post($post_ID);

			// Delegate
			self::Transition_post_status('publish', 'future', $post);
		}
		function Before_delete_post($post_ID) {
			if ($this->debug)
				add_post_meta($post_ID, c_al2fb_meta_log, date('c') . ' Before delete post');

			$post = get_post($post_ID);
			$user_ID = self::Get_user_ID($post);
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			if (!empty($link_id) && self::Is_authorized($user_ID))
				self::Delete_fb_link($post);
		}

		// Handle post status change
		function Transition_post_status($new_status, $old_status, $post) {
			if ($this->debug)
				add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' ' . $old_status . '->' . $new_status);

			self::Save_post($post->ID);

			$user_ID = self::Get_user_ID($post);
			$update = (isset($_POST[c_al2fb_action_update]) && $_POST[c_al2fb_action_update]);
			$delete = (isset($_POST[c_al2fb_action_delete]) && $_POST[c_al2fb_action_delete]);
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);

			// Security check
			if (self::user_can($user_ID, get_option(c_al2fb_option_min_cap)) &&
				self::Is_authorized($user_ID)) {
				// Add, update or delete link
				if ($update || $delete || $new_status == 'trash') {
					if (!empty($link_id))
						self::Delete_fb_link($post);
				}
				if (!$delete) {
					// Check post status
					if (empty($link_id) &&
						$new_status == 'publish' &&
						($new_status != $old_status || $update ||
						get_post_meta($post->ID, c_al2fb_meta_error, true)))
						self::Publish_post($post);
				}
			}
		}

		// Handle publish post / XML-RPC publish post
		function Publish_post($post) {
			if ($this->debug)
				add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' Publish');

			$user_ID = self::Get_user_ID($post);

			// Checks
			if (self::user_can($user_ID, get_option(c_al2fb_option_min_cap))) {
				// Check if not added
				if (self::Is_authorized($user_ID) &&
					!get_post_meta($post->ID, c_al2fb_meta_link_id, true) &&
					!get_post_meta($post->ID, c_al2fb_meta_exclude, true)) {

					$add_new_page = get_user_meta($user_ID, c_al2fb_meta_add_new_page, true);

					// Exclude categories
					$exclude_category = false;
					$categories = get_the_category($post->ID);
					$excluding_categories = explode(',', get_option(c_al2fb_option_exclude_cat));
					if ($categories)
						foreach ($categories as $category)
							if (in_array($category->cat_ID, $excluding_categories))
								$exclude_category = true;

					// Exclude post types
					$exclude_type = self::Is_excluded_post_type($post);

					// Exclude tags
					$exclude_tag = false;
					$tags = get_the_tags($post->ID);
					$excluding_tags = explode(',', get_option(c_al2fb_option_exclude_tag));
					if ($tags)
						foreach ($tags as $tag)
							if (in_array($tag->name, $excluding_tags))
								$exclude_tag = true;

					// Exclude authors
					$excluding_authors = explode(',', get_option(c_al2fb_option_exclude_author));
					$author = get_the_author_meta('user_login', $post->post_author);
					$exclude_author = in_array($author, $excluding_authors);

					// Check if public post
					if (empty($post->post_password) &&
						($post->post_type != 'page' || $add_new_page) &&
						!$exclude_type && !$exclude_category && !$exclude_tag && !$exclude_author)
						self::Add_fb_link($post);
				}
			}
		}

		function Is_excluded_post_type($post) {
			$ex_custom_types = explode(',', get_option(c_al2fb_option_exclude_type));

			// Compatibility
			$ex_custom_types[] = 'nav_menu_item';
			$ex_custom_types[] = 'recipe';
			$ex_custom_types[] = 'recipeingredient';
			$ex_custom_types[] = 'recipestep';
			$ex_custom_types[] = 'wpcf7_contact_form';
			$ex_custom_types[] = 'feedback';
			$ex_custom_types[] = 'spam';
			$ex_custom_types[] = 'twitter';
			$ex_custom_types[] = 'mscr_ban';
			// bbPress
			$ex_custom_types[] = 'forum';
			$ex_custom_types[] = 'topic';
			$ex_custom_types[] = 'reply';

			$ex_custom_types = apply_filters('al2fb_excluded_post_types', $ex_custom_types);

			return in_array($post->post_type, $ex_custom_types);
		}

		// Build texts for link/ogp
		function Get_texts($post) {
			$user_ID = self::Get_user_ID($post);

			// Filter excerpt
			$excerpt = get_post_meta($post->ID, c_al2fb_meta_excerpt, true);
			if (empty($excerpt)) {
				$excerpt = $post->post_excerpt;
				if (!get_option(c_al2fb_option_nofilter))
					$excerpt = apply_filters('the_excerpt', $excerpt);
			}
			$excerpt = apply_filters('al2fb_excerpt', $excerpt, $post);

			// Filter post text
			$content = get_post_meta($post->ID, c_al2fb_meta_text, true);
			if (empty($content)) {
				$content = $post->post_content;
				if (!get_option(c_al2fb_option_nofilter))
					$content = apply_filters('the_content', $content);
			}
			$content = apply_filters('al2fb_content', $content, $post);

			// Get body
			$description = '';
			if (get_user_meta($user_ID, c_al2fb_meta_msg, true))
				$description = $content;
			else
				$description = ($excerpt ? $excerpt : $content);

			// Trailer: limit body size
			$trailer = get_user_meta($user_ID, c_al2fb_meta_trailer, true);
			if ($trailer) {
				$trailer = preg_replace('/<[^>]*>/', '', $trailer);

				// Get maximum FB description size
				$maxlen = get_option(c_al2fb_option_max_descr);
				if (!$maxlen)
					$maxlen = 256;

				// Add maximum number of sentences
				$lines = explode('.', $description);
				if ($lines) {
					$count = 0;
					$description = '';
					foreach ($lines as $sentence) {
						$line = $sentence;
						if ($count + 1 < count($lines))
							$line .= '.';
						if (strlen($description) + strlen($line) + strlen($trailer) < $maxlen)
							$description .= $line;
						else
							break;
					}
					if (empty($description) && count($lines) > 0)
						$description = substr($lines[0], 0, $maxlen - strlen($trailer));
					$description .= $trailer;
				}
			}

			// Build result
			$texts = array(
				'excerpt' => $excerpt,
				'content' => $content,
				'description' => $description
			);
			return $texts;
		}

		// Get link picture
		function Get_link_picture($post, $user_ID) {
			// Get selected image
			$image_id = get_post_meta($post->ID, c_al2fb_meta_image_id, true);
			if (!empty($image_id) && function_exists('wp_get_attachment_thumb_url')) {
				$picture_type = 'meta';
				$picture = wp_get_attachment_thumb_url($image_id);
				// Workaround
				if (strpos($picture, 'http') === false)
					$picture = content_url($picture);
			}

			if (empty($picture)) {
				// Default picture
				$picture = get_user_meta($user_ID, c_al2fb_meta_picture_default, true);
				if (empty($picture))
					$picture = self::Redirect_uri() . '?al2fb_image=1';

				// Check picture type
				$picture_type = get_user_meta($user_ID, c_al2fb_meta_picture_type, true);
				if ($picture_type == 'media') {
					$images = array_values(get_children('post_type=attachment&post_mime_type=image&order=ASC&post_parent=' . $post->ID));
					if (!empty($images) && function_exists('wp_get_attachment_image_src')) {
						$picture = wp_get_attachment_image_src($images[0]->ID, 'thumbnail');
						if ($picture && $picture[0])
							$picture = $picture[0];
					}
				}
				else if ($picture_type == 'featured') {
					if (current_theme_supports('post-thumbnails') &&
						function_exists('get_post_thumbnail_id') &&
						function_exists('wp_get_attachment_image_src')) {
						$picture_id = get_post_thumbnail_id($post->ID);
						if ($picture_id) {
							if (stripos($picture_id, 'ngg-') !== false && class_exists('nggdb')) {
								$nggMeta = new nggMeta(str_replace('ngg-', '', $picture_id));
								$picture = $nggMeta->image->imageURL;
							}
							else {
								$picture = wp_get_attachment_image_src($picture_id, 'thumbnail');
								if ($picture && $picture[0])
									$picture = $picture[0];
							}
						}
					}
				}
				else if ($picture_type == 'facebook')
					$picture = '';
				else if ($picture_type == 'post' || empty($picture_type)) {
					$content = $post->post_content;
					if (!get_option(c_al2fb_option_nofilter))
						$content = apply_filters('the_content', $content);
					if (preg_match('/< *img[^>]*src *= *["\']([^"\']*)["\']/i', $content, $matches))
						$picture = $matches[1];
				}
				else if ($picture_type == 'avatar') {
					$userdata = get_userdata($post->post_author);
					$avatar = get_avatar($userdata->user_email);
					if (!empty($avatar))
						if (preg_match('/< *img[^>]*src *= *["\']([^"\']*)["\']/i', $avatar, $matches))
							$picture = $matches[1];
				}
				else if ($picture_type == 'userphoto') {
					$userdata = get_userdata($post->post_author);
					if ($userdata->userphoto_approvalstatus == USERPHOTO_APPROVED) {
						$image_file = $userdata->userphoto_image_file;
						$upload_dir = wp_upload_dir();
						$picture = trailingslashit($upload_dir['baseurl']) . 'userphoto/' . $image_file;
					}
				}
				else if ($picture_type == 'custom') {
					$custom = get_user_meta($user_ID, c_al2fb_meta_picture, true);
					if ($custom)
						$picture = $custom;
				}
			}

			$picture = apply_filters('al2fb_picture', $picture, $post);

			return array(
				'picture' => $picture,
				'picture_type' => $picture_type
			);
		}

		function Get_fb_profilelink($id) {
			if (empty($id))
				return '';
			return 'http://www.facebook.com/profile.php?id=' . $id;
		}

		function Get_fb_permalink($link_id) {
			if (empty($link_id))
				return '';
			$ids = explode('_', $link_id);
			return 'http://www.facebook.com/permalink.php?story_fbid=' . $ids[1] . '&id=' . $ids[0];
		}

		function Filter_excerpt($excerpt, $post) {
			return self::Filter_standard($excerpt, $post);
		}

		function Filter_content($content, $post) {
			return self::Filter_standard($content, $post);
		}

		function Filter_comment($message, $comment, $post) {
			return self::Filter_standard($message, $post);
		}

		function Filter_standard($text, $post) {
			$user_ID = self::Get_user_ID($post);

			// Convert to UTF-8 if needed
			$text = self::Convert_encoding($user_ID, $text);

			// Execute shortcodes
			if (!get_option(c_al2fb_option_noshortcode))
				$text = do_shortcode($text);

			// http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php

			// Remove scripts
			$text = preg_replace('/<script.+?<\/script>/ims', '', $text);

			// Remove styles
			$text = preg_replace('/<style.+?<\/style>/ims', '', $text);

			// Replace hyperlinks
			if (get_user_meta($user_ID, c_al2fb_meta_hyperlink, true))
				$text = preg_replace('/< *a[^>]*href *= *["\']([^"\']*)["\'][^<]*/i', '$1<a>', $text);

			// Remove image captions
			$text = preg_replace('/<p[^>]*class="wp-caption-text"[^>]*>[^<]*<\/p>/i', '', $text);

			// Get plain texts
			$text = preg_replace('/<[^>]*>/', '', $text);

			// Decode HTML entities
			$text = html_entity_decode($text, ENT_QUOTES, get_bloginfo('charset'));

			// Prevent starting with with space
			$text = trim($text);

			// Truncate text
			if (!empty($text)) {
				$maxtext = get_option(c_al2fb_option_max_text);
				if (!$maxtext)
					$maxtext = 10000;
				$text = substr($text, 0, $maxtext);
			}

			return $text;
		}

		// Convert charset
		function Convert_encoding($user_ID, $text) {
			$blog_encoding = get_option('blog_charset');
			$fb_encoding = get_user_meta($user_ID, c_al2fb_meta_fb_encoding, true);
			if (empty($fb_encoding))
				$fb_encoding = 'UTF-8';

			if ($blog_encoding != $fb_encoding && function_exists('mb_convert_encoding'))
				return @mb_convert_encoding($text, $fb_encoding, $blog_encoding);
			else
				return $text;
		}

		// Add Link to Facebook
		function Add_fb_link($post) {
			$user_ID = self::Get_user_ID($post);

			// Get url
			if (get_user_meta($user_ID, c_al2fb_meta_shortlink, true))
				$link = wp_get_shortlink($post->ID);
			if (empty($link))
				$link = get_permalink($post->ID);
			$link = apply_filters('al2fb_link', $link, $post);

			// Get processed texts
			$texts = self::Get_texts($post);
			$excerpt = $texts['excerpt'];
			$content = $texts['content'];
			$description = $texts['description'];
			if (!$description)
				$description = ' ';

			// Get name
			$name = html_entity_decode(get_the_title($post->ID), ENT_QUOTES, get_bloginfo('charset'));
			$name = self::Convert_encoding($user_ID, $name);
			$name = apply_filters('al2fb_name', $name, $post);

			// Get caption
			$caption = '';
			if (get_user_meta($user_ID, c_al2fb_meta_caption, true)) {
				$caption = html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset'));
				$caption = self::Convert_encoding($user_ID, $caption);
				$caption = apply_filters('al2fb_caption', $caption, $post);
			}

			// Get link picture
			$picture_info = self::Get_link_picture($post, $user_ID);
			$picture = $picture_info['picture'];
			$picture_type = $picture_info['picture_type'];

			// Get user note
			$message = '';
			if (get_user_meta($user_ID, c_al2fb_meta_msg, true))
				$message = $excerpt;

			// Do not disturb WordPress
			try {
				// Build request
				if (get_user_meta($user_ID, c_al2fb_meta_use_groups, true))
					$page_id = get_user_meta($user_ID, c_al2fb_meta_group, true);
				if (empty($page_id))
					$page_id = get_user_meta($user_ID, c_al2fb_meta_page, true);
				if (empty($page_id))
					$page_id = 'me';
				$url = 'https://graph.facebook.com/' . $page_id . '/feed';
				$url = apply_filters('al2fb_url', $url);

				$query_array = array(
					'access_token' => self::Get_access_token_by_post($post),
					'link' => $link,
					'name' => $name,
					'caption' => $caption,
					'description' => $description,
					'message' => $message
				);

				if ($picture)
					$query_array['picture'] = $picture;

				// Add share link
				if (get_user_meta($user_ID, c_al2fb_meta_share_link, true)) {
					// http://forum.developers.facebook.net/viewtopic.php?id=50049
					// http://bugs.developers.facebook.net/show_bug.cgi?id=9075
					$actions = array(
						'name' => __('Share', c_al2fb_text_domain),
						'link' => 'http://www.facebook.com/share.php?u=' . urlencode($link) . '&t=' . rawurlencode($name)
					);
					$query_array['actions'] = json_encode($actions);
				}

				// Build request
				$query = http_build_query($query_array, '', '&');

				// Log request
				update_option(c_al2fb_last_request, print_r($query_array, true) . $query);
				update_option(c_al2fb_last_request_time, date('c'));
				update_option(c_al2fb_last_texts, print_r($texts, true) . $query);
				if ($this->debug) {
					add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' request=' . print_r($query_array, true));
					add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' texts=' . print_r($texts, true));
				}

				// Execute request
				$response = self::Request($url, $query, 'POST');

				// Log response
				update_option(c_al2fb_last_response, $response);
				update_option(c_al2fb_last_response_time, date('c'));
				if ($this->debug)
					add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' response=' . $response);

				// Decode response
				$fb_link = json_decode($response);

				// Register link/date
				add_post_meta($post->ID, c_al2fb_meta_link_id, $fb_link->id);
				update_post_meta($post->ID, c_al2fb_meta_link_time, date('c'));
				update_post_meta($post->ID, c_al2fb_meta_link_picture, $picture_type . '=' . $picture);
				delete_post_meta($post->ID, c_al2fb_meta_error);
				delete_post_meta($post->ID, c_al2fb_meta_error_time);
			}
			catch (Exception $e) {
				update_post_meta($post->ID, c_al2fb_meta_error, 'Add link: ' . $e->getMessage());
				update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
				update_post_meta($post->ID, c_al2fb_meta_link_picture, $picture_type . '=' . $picture);
			}
		}

		// Delete Link from Facebook
		function Delete_fb_link($post) {
			// Do not disturb WordPress
			try {
				// Build request
				// http://developers.facebook.com/docs/reference/api/link/
				$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
				$url = 'https://graph.facebook.com/' . $link_id;
				$url = apply_filters('al2fb_url', $url);
				$query = http_build_query(array(
					'access_token' => self::Get_access_token_by_post($post),
					'method' => 'delete'
				), '', '&');

				if ($this->debug)
					add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' request=' . print_r($query_array, true));

				// Execute request
				$response = self::Request($url, $query, 'POST');

				if ($this->debug)
					add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' response=' . $response);

				// Delete meta data
				delete_post_meta($post->ID, c_al2fb_meta_link_id);
				delete_post_meta($post->ID, c_al2fb_meta_link_time);
				delete_post_meta($post->ID, c_al2fb_meta_link_picture);
				delete_post_meta($post->ID, c_al2fb_meta_error);
				delete_post_meta($post->ID, c_al2fb_meta_error_time);
			}
			catch (Exception $e) {
				update_post_meta($post->ID, c_al2fb_meta_error, 'Delete link: ' . $e->getMessage());
				update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
			}
		}

		// Delete Link from Facebook
		function Delete_fb_link_comment($comment) {
			// Get data
			$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
			if (empty($fb_comment_id))
				return;
			$post = get_post($comment->comment_post_ID);
			if (empty($post))
				return;

			// Do not disturb WordPress
			try {
				// Build request
				$url = 'https://graph.facebook.com/' . $fb_comment_id;
				$url = apply_filters('al2fb_url', $url);
				$query = http_build_query(array(
					'access_token' => self::Get_access_token_by_post($post),
					'method' => 'delete'
				), '', '&');

				// Execute request
				$response = self::Request($url, $query, 'POST');

				// Delete meta data
				delete_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id);
			}
			catch (Exception $e) {
				update_post_meta($post->ID, c_al2fb_meta_error, 'Delete comment: ' . $e->getMessage());
				update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
			}
		}

		// New comment
		function Comment_post($comment_ID) {
			$comment = get_comment($comment_ID);
			if ($comment->comment_approved == '1' && $comment->comment_agent != 'AL2FB')
				self::Add_fb_link_comment($comment);
		}

		// Approved comment
		function Comment_approved($comment) {
			if ($comment->comment_agent != 'AL2FB')
				self::Add_fb_link_comment($comment);
		}

		// Disapproved comment
		function Comment_unapproved($comment) {
			self::Delete_fb_link_comment($comment);
		}

		// Permanently delete comment
		function Delete_comment($comment_ID) {
			// Get data
			$comment = get_comment($comment_ID);
			$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);

			// Save Facebook ID
			if (!empty($fb_comment_id))
				add_post_meta($comment->comment_post_ID, c_al2fb_meta_fb_comment_id, $fb_comment_id, false);
		}

		// Add comment to link
		function Add_fb_link_comment($comment) {
			// Get data
			$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
			if (!empty($fb_comment_id))
				return;
			$post = get_post($comment->comment_post_ID);
			if (empty($post))
				return;
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			if (empty($link_id))
				return;
			if (get_post_meta($post->ID, c_al2fb_meta_nointegrate, true))
				return;
			$user_ID = self::Get_user_ID($post);
			if (!get_user_meta($user_ID, c_al2fb_meta_fb_comments_postback, true))
				return;
			if (self::Is_excluded_post_type($post))
				return;

			// Build message
			$message = $comment->comment_author . ' ' .  __('commented on', c_al2fb_text_domain) . ' ';
			$message .= html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset')) . ":\n\n";
			$message .= $comment->comment_content;
			$message = apply_filters('al2fb_comment', $message, $comment, $post);

			// Do not disturb WordPress
			try {
				$url = 'https://graph.facebook.com/' . $link_id . '/comments';
				$url = apply_filters('al2fb_url', $url);

				$query_array = array(
					'access_token' => self::Get_access_token_by_post($post),
					'message' => $message
				);

				// http://developers.facebook.com/docs/reference/api/Comment/
				$query = http_build_query($query_array, '', '&');

				// Execute request
				$response = self::Request($url, $query, 'POST');

				// Process response
				$fb_comment = json_decode($response);
				add_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, $fb_comment->id);
			}
			catch (Exception $e) {
				update_post_meta($post->ID, c_al2fb_meta_error, 'Add comment: ' . $e->getMessage());
				update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
			}
		}

		function Is_authorized($user_ID) {
			return get_user_meta($user_ID, c_al2fb_meta_access_token, true);
		}

		// Get correct access for post
		function Get_access_token_by_post($post) {
			$user_ID = self::Get_user_ID($post);
			$page_id = get_user_meta($user_ID, c_al2fb_meta_page, true);
			return self::Get_access_token_by_page($user_ID, $page_id);
		}

		// Get access token for page
		function Get_access_token_by_page($user_ID, $page_id) {
			$access_token = get_user_meta($user_ID, c_al2fb_meta_access_token, true);
			if ($page_id && $page_id != 'me' &&
				get_user_meta($user_ID, c_al2fb_meta_page_owner, true)) {
				$found = false;
				$pages = self::Get_fb_pages($user_ID);
				if ($pages->data)
					foreach ($pages->data as $page)
						if ($page->id == $page_id) {
							$found = true;
							$access_token = $page->access_token;
						}
			}
			return $access_token;
		}

		// HTML header
		function WP_head() {
			if (is_single() || is_page()) {
				global $post;
				$user_ID = self::Get_user_ID($post);
				if (get_user_meta($user_ID, c_al2fb_meta_open_graph, true)) {
					$charset = get_bloginfo('charset');
					$title = html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset'));
					$post_title = html_entity_decode(get_the_title($post->ID), ENT_QUOTES, get_bloginfo('charset'));

					// Get link picture
					$link_picture = get_post_meta($post->ID, c_al2fb_meta_link_picture, true);
					if (empty($link_picture)) {
						$picture_info = self::Get_link_picture($post, $user_ID);
						$picture = $picture_info['picture'];
					}
					else
						$picture = substr($link_picture, strpos($link_picture, '=') + 1);
					if (empty($picture))
						$picture = self::Redirect_uri() . '?al2fb_image=1';

					// Get type
					$ogp_type = get_user_meta($user_ID, c_al2fb_meta_open_graph_type, true);
					if (empty($ogp_type))
						$ogp_type = 'article';

					// Generate meta tags
					echo '<!-- Start AL2FB OGP -->' . PHP_EOL;
					echo '<meta property="og:title" content="' . htmlspecialchars($post_title, ENT_COMPAT, $charset) . '" />' . PHP_EOL;
					echo '<meta property="og:type" content="' . $ogp_type . '" />' . PHP_EOL;
					echo '<meta property="og:image" content="' . $picture . '" />' . PHP_EOL;
					echo '<meta property="og:url" content="' . get_permalink($post->ID) . '" />' . PHP_EOL;
					echo '<meta property="og:site_name" content="' . htmlspecialchars($title, ENT_COMPAT, $charset) . '" />' . PHP_EOL;

					$texts = self::Get_texts($post);
					$maxlen = get_option(c_al2fb_option_max_descr);
					$description = substr($texts['description'], 0, $maxlen ? $maxlen : 256);
					echo '<meta property="og:description" content="' . htmlspecialchars($description, ENT_COMPAT, $charset) . '" />' . PHP_EOL;

					$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
					if (!empty($appid))
						echo '<meta property="fb:app_id" content="' . $appid . '" />' . PHP_EOL;

					$admins = get_user_meta($user_ID, c_al2fb_meta_open_graph_admins, true);
					if (!empty($admins))
						echo '<meta property="fb:admins" content="' . $admins . '" />' . PHP_EOL;

					// Facebook i18n
					echo '<meta property="og:locale" content="' . self::Get_locale($user_ID) . '" />' . PHP_EOL;
					echo '<!-- End AL2FB OGP -->' . PHP_EOL;
				}
			}

			else if (is_home())
			{
				// Check if any user has enabled the OGP
				global $wpdb;
				$opg = 0;
				$user_ID = null;
				$rows = $wpdb->get_results("SELECT user_id, meta_value FROM " . $wpdb->usermeta . " WHERE meta_key='" . c_al2fb_meta_open_graph . "'");
				foreach ($rows as $row)
					if ($row->meta_value) {
						$opg++;
						$user_ID = $row->user_id;
					}

				if ($opg) {
					$charset = get_bloginfo('charset');
					$title = html_entity_decode(get_bloginfo('title'), ENT_QUOTES, $charset);
					$description = html_entity_decode(get_bloginfo('description'), ENT_QUOTES, $charset);

					// Get link picture
					$picture_type = get_user_meta($user_ID, c_al2fb_meta_picture_type, true);
					if ($picture_type == 'custom')
						$picture = get_user_meta($user_ID, c_al2fb_meta_picture, true);
					if (empty($picture)) {
						$picture = get_user_meta($user_ID, c_al2fb_meta_picture_default, true);
						if (empty($picture))
							$picture = self::Redirect_uri() . '?al2fb_image=1';
					}

					// Generate meta tags
					echo '<!-- Start AL2FB OGP -->' . PHP_EOL;
					echo '<meta property="og:title" content="' . htmlspecialchars($title, ENT_COMPAT, $charset) . '" />' . PHP_EOL;
					echo '<meta property="og:type" content="blog" />' . PHP_EOL;
					echo '<meta property="og:image" content="' . $picture . '" />' . PHP_EOL;
					echo '<meta property="og:url" content="' . get_home_url() . '" />' . PHP_EOL;
					echo '<meta property="og:site_name" content="' . htmlspecialchars($title, ENT_COMPAT, $charset) . '" />' . PHP_EOL;
					if (!empty($description))
						echo '<meta property="og:description" content="' . htmlspecialchars($description, ENT_COMPAT, $charset) . '" />' . PHP_EOL;

					// Single user blog
					if ($opg == 1) {
						$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
						if (!empty($appid))
							echo '<meta property="fb:app_id" content="' . $appid . '" />' . PHP_EOL;

						$admins = get_user_meta($user_ID, c_al2fb_meta_open_graph_admins, true);
						if (!empty($admins))
							echo '<meta property="fb:admins" content="' . $admins . '" />' . PHP_EOL;

						// Facebook i18n
						echo '<meta property="og:locale" content="' . self::Get_locale($user_ID) . '" />' . PHP_EOL;
					}
					echo '<!-- End AL2FB OGP -->' . PHP_EOL;
				}
			}
		}

		// Additional styles
		function WP_print_styles() {
			$css = get_option(c_al2fb_option_css);
			if (!empty($css)) {
				echo '<!-- AL2FB CSS -->' . PHP_EOL;
				echo '<style type="text/css" media="screen">' . PHP_EOL;
				echo $css;
				echo '</style>' . PHP_EOL;
			}
		}

		// Post content
		function The_content($content = '') {
			global $post;

			// Excluded post types
			if (self::Is_excluded_post_type($post))
				return $content;

			$user_ID = self::Get_user_ID($post);
			if (!(get_user_meta($user_ID, c_al2fb_meta_like_nohome, true) && is_home()) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_noposts, true) && is_single()) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_nopages, true) && is_page()) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_noarchives, true) && is_archive()) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_nocategories, true) && is_category())) {

				// Show likers
				if (get_user_meta($user_ID, c_al2fb_meta_post_likers, true)) {
					$likers = self::Get_likers($post);
					if (!empty($likers))
						if (get_user_meta($user_ID, c_al2fb_meta_like_top, true))
							$content = $likers . $content;
						else
							$content .= $likers;
				}

				// Show like button
				if (!get_post_meta($post->ID, c_al2fb_meta_nolike, true)) {
					if (get_user_meta($user_ID, c_al2fb_meta_post_like_button, true))
						$button = self::Get_like_button($post, false);
					if (get_user_meta($user_ID, c_al2fb_meta_post_send_button, true) &&
						!get_user_meta($user_ID, c_al2fb_meta_post_combine_buttons, true))
						$button .= self::Get_send_button($post);
				}
				if (!empty($button))
					if (get_user_meta($user_ID, c_al2fb_meta_like_top, true))
						$content = $button . $content;
					else
						$content .= $button;

				// Show comments plugin
				if (get_user_meta($user_ID, c_al2fb_meta_comments_auto, true))
					$content .= self::Get_comments_plugin($post);
			}

			return $content;
		}

		// Shortcode likers names
		function Shortcode_likers($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_likers($post);
			else
				return '';
		}

		// Shortcode like count
		function Shortcode_like_count($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_like_count($post);
			else
				return '';
		}

		// Shortcode like button
		function Shortcode_like_button($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_like_button($post, false);
			else
				return '';
		}

		// Shortcode like box
		function Shortcode_like_box($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_like_button($post, true);
			else
				return '';
		}

		// Shortcode send button
		function Shortcode_send_button($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_send_button($post);
			else
				return '';
		}

		// Shortcode comments plugin
		function Shortcode_comments_plugin($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_comments_plugin($post);
			else
				return '';
		}

		// Shortcode face pile
		function Shortcode_face_pile($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_face_pile($post);
			else
				return '';
		}

		// Shortcode profile link
		function Shortcode_profile_link($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_profile_link($post);
			else
				return '';
		}

		// Shortcode Facebook registration
		function Shortcode_registration($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_registration($post);
			else
				return '';
		}

		// Shortcode Facebook login
		function Shortcode_login($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_login($post);
			else
				return '';
		}

		// Shortcode Facebook activity feed
		function Shortcode_activity_feed($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_activity_feed($post);
			else
				return '';
		}

		// Get HTML for likers
		function Get_likers($post) {
			$likers = '';
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {
				$charset = get_bloginfo('charset');
				$fb_likes = self::Get_comments_or_likes($post, true);
				if ($fb_likes)
					foreach ($fb_likes->data as $fb_like) {
						if (!empty($likers))
							$likers .= ', ';
						if (get_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, true) == 'author') {
							$link = self::Get_fb_profilelink($fb_like->id);
							$likers .= '<a href="' . $link . '" rel="nofollow">' . htmlspecialchars($fb_like->name, ENT_QUOTES, $charset) . '</a>';
						}
						else
							$likers .= htmlspecialchars($fb_like->name, ENT_QUOTES, $charset);
					}

				if (!empty($likers)) {
					$likers .= ' <span class="al2fb_liked">' . _n('liked this post', 'liked this post', count($fb_likes->data), c_al2fb_text_domain) . '</span>';
					$likers = '<div class="al2fb_likers">' . $likers . '</div>';
				}
			}
			return $likers;
		}

		// Get HTML for like count
		function Get_like_count($post) {
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {
				$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
				$fb_likes = self::Get_comments_or_likes($post, true);
				if ($fb_likes && count($fb_likes->data) > 0)
					return '<div class="al2fb_like_count"><a href="' . self::Get_fb_permalink($link_id) . '" rel="nofollow">' . count($fb_likes->data) . ' ' . _n('liked this post', 'liked this post', count($fb_likes->data), c_al2fb_text_domain) . '</a></div>';
			}
			return '';
		}

		// Get language code for Facebook
		function Get_locale($user_ID) {
			$locale = get_user_meta($user_ID, c_al2fb_meta_fb_locale, true);
			if (empty($locale)) {
				$locale = defined('WPLANG') ? WPLANG : '';
				$locale = str_replace('-', '_', $locale);
				if (empty($locale) || strlen($locale) != 5)
					$locale = 'en_US';
			}
			return $locale;
		}

		function Get_fb_script($user_ID) {
			if (get_option(c_al2fb_option_noscript))
				return '<!-- AL2FB no script -->';
			$lang = self::Get_locale($user_ID);
			$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
			if ($appid)
				$url = 'http://connect.facebook.net/' . $lang . '/all.js#appId=' . $appid . '&amp;xfbml=1';
			else
				$url = 'http://connect.facebook.net/' . $lang . '/all.js#xfbml=1';
			return '<script src="' . $url . '" type="text/javascript"></script>' . PHP_EOL;
		}

		// Get HTML for like button
		function Get_like_button($post, $box) {
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {
				// Get options
				$layout = get_user_meta($user_ID, c_al2fb_meta_like_layout, true);
				$faces = get_user_meta($user_ID, c_al2fb_meta_like_faces, true);
				if ($box)
					$width = get_user_meta($user_ID, c_al2fb_meta_like_box_width, true);
				else
					$width = get_user_meta($user_ID, c_al2fb_meta_like_width, true);
				$action = get_user_meta($user_ID, c_al2fb_meta_like_action, true);
				$font = get_user_meta($user_ID, c_al2fb_meta_like_font, true);
				$colorscheme = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
				$border = get_user_meta($user_ID, c_al2fb_meta_like_box_border, true);
				$noheader = get_user_meta($user_ID, c_al2fb_meta_like_box_noheader, true);
				$nostream = get_user_meta($user_ID, c_al2fb_meta_like_box_nostream, true);

				$link = get_user_meta($user_ID, c_al2fb_meta_like_link, true);
				if (empty($link))
					if ($box) {
						// Get page
						if (self::Is_authorized($user_ID) &&
							!get_user_meta($user_ID, c_al2fb_meta_use_groups, true) &&
							get_user_meta($user_ID, c_al2fb_meta_page, true))
							try {
								$page = self::Get_fb_me_cached($user_ID, false);
								$link = $page->link;
							}
							catch (Exception $e) {
							}
					}
					else
						$link = get_permalink($post->ID);

				$combine = get_user_meta($user_ID, c_al2fb_meta_post_combine_buttons, true);
				$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
				$lang = $this->Get_locale($user_ID);
				$txtinfo = (empty($action) || $action == 'like' ? __('Like', c_al2fb_text_domain) : __('Recommend', c_al2fb_text_domain));
				$infolink = get_option(c_al2fb_option_ssp_info);
				if (empty($infolink))
					$infolink = 'http://yro.slashdot.org/story/11/09/03/0115241/Heises-Two-Clicks-For-More-Privacy-vs-Facebook';

				// Build content
				if ($appid && !$combine && !$box && get_option(c_al2fb_option_use_ssp)) {
					$content = '<div id="al2fb_ssp' . $post->ID . '"></div>' . PHP_EOL;
					$content .= '<script type="text/javascript">' . PHP_EOL;
					$content .= '	jQuery(document).ready(function($) {' . PHP_EOL;
					$content .= '		$("#al2fb_ssp' . $post->ID . '").socialSharePrivacy({' . PHP_EOL;
					$content .= '			services : {' . PHP_EOL;
					$content .= '				facebook : {' . PHP_EOL;
					$content .= '					"status" : "on",' . PHP_EOL;
					$content .= '					"dummy_img" : "' . $this->plugin_url . '/js/socialshareprivacy/images/dummy_facebook.png",' . PHP_EOL;
					if ($lang != 'de_DE') {
						$content .= '					"txt_info" : "' . $txtinfo . '",';
						$content .= '					"txt_fb_off" : "",';
						$content .= '					"txt_fb_on" : "",';
					}
					$content .= '					"perma_option" : "off",' . PHP_EOL;
					if ($lang != 'de_DE')
						$content .= '					"display_name" : "Facebook",' . PHP_EOL;
					$content .= '					"referrer_track" : "",' . PHP_EOL;
					$content .= '					"language" : "' . $lang . '",' . PHP_EOL;
					$content .= '					"action" : "' . (empty($action) ? 'like' : $action) . '"' . PHP_EOL;
					$content .= '				},';
					$content .= '				twitter : {' . PHP_EOL;
					$content .= '					"status" : "off",' . PHP_EOL;
					$content .= '					"dummy_img" : "' . $this->plugin_url . '/js/socialshareprivacy/images/dummy_twitter.png",' . PHP_EOL;
					$content .= '					"perma_option" : "off"' . PHP_EOL;
					$content .= '				 },' . PHP_EOL;
					$content .= '				gplus : {' . PHP_EOL;
					$content .= '					"status" : "off",' . PHP_EOL;
					$content .= '					"dummy_img" : "' . $this->plugin_url . '/js/socialshareprivacy/images/dummy_gplus.png",' . PHP_EOL;
					$content .= '					"perma_option" : "off"' . PHP_EOL;
					$content .= '				 },' . PHP_EOL;
					$content .= '			},';
					$content .= '			"info_link" : "' . $infolink . '",';
					if ($lang != 'de_DE')
						$content .= '			"txt_help" : "' . __('Information', c_al2fb_text_domain) . '",' . PHP_EOL;
					$content .= '			"css_path" : "' . $this->plugin_url . '/js/socialshareprivacy/socialshareprivacy.css",' . PHP_EOL;
					$content .= '			"uri" : "' . $link . '"' . PHP_EOL;
					$content .= '		});' . PHP_EOL;
					$content .= '	});' . PHP_EOL;
					$content .= '</script>' . PHP_EOL;
					$content = apply_filters('al2fb_heise', $content);
				}
				else {
					$content = ($box ? '<div class="al2fb_like_box">' : '<div class="al2fb_like_button">');
					$content .= '<div id="fb-root"></div>';
					$content .= self::Get_fb_script($user_ID);
					$content .= ($box ? '<fb:like-box' : '<fb:like');
					$content .= ' href="' . $link . '"';
					if (!$box && $combine)
						$content .= ' send="true"';
					if (!$box)
						$content .= ' layout="' . (empty($layout) ? 'standard' : $layout) . '"';
					$content .= ' show_faces="' . ($faces ? 'true' : 'false') . '"';
					$content .= ' width="' . (empty($width) ? ($box ? '292' : '450') : $width) . '"';
					if (!$box) {
						$content .= ' action="' . (empty($action) ? 'like' : $action) . '"';
						$content .= ' font="' . (empty($font) ? 'arial' : $font) . '"';
					}
					$content .= ' colorscheme="' . (empty($colorscheme) ? 'light' : $colorscheme) . '"';
					if (!$box)
						$content .= ' ref="AL2FB"';
					if ($box) {
						$content .= ' border_color="' . $border . '"';
						$content .= ' stream="' . ($nostream ? 'false' : 'true') . '"';
						$content .= ' header="' . ($noheader ? 'false' : 'true') . '"';
					}
					$content .= ($box ? '></fb:like-box>' : '></fb:like>');
					$content .= '</div>';
				}

				return $content;
			}
			else
				return '';
		}

		// Get HTML for like button
		function Get_send_button($post) {
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {
				// Get options
				$font = get_user_meta($user_ID, c_al2fb_meta_like_font, true);
				$colorscheme = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
				$link = get_user_meta($user_ID, c_al2fb_meta_like_link, true);
				if (empty($link))
					$link = get_permalink($post->ID);

				// Send button
				$content = '<div class="al2fb_send_button">';
				$content .= '<div id="fb-root"></div>';
				$content .= self::Get_fb_script($user_ID);
				$content .= '<fb:send ref="AL2FB"';
				$content .= ' font="' . (empty($font) ? 'arial' : $font) . '"';
				$content .= ' colorscheme="' . (empty($colorscheme) ? 'light' : $colorscheme) . '"';
				$content .= ' href="' . $link . '"></fb:send>';
				$content .= '</div>';

				return $content;
			}
			else
				return '';
		}

		// Get HTML for comments plugin
		function Get_comments_plugin($post) {
			if (get_post_meta($post->ID, c_al2fb_meta_nointegrate, true))
				return '';

			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {
				// Get options
				$posts = get_user_meta($user_ID, c_al2fb_meta_comments_posts, true);
				$width = get_user_meta($user_ID, c_al2fb_meta_comments_width, true);
				$colorscheme = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
				$link = get_user_meta($user_ID, c_al2fb_meta_like_link, true);
				if (empty($link))
					$link = get_permalink($post->ID);

				// Send button
				$content = '<div class="al2fb_comments_plugin">';
				$content .= '<div id="fb-root"></div>';
				$content .= self::Get_fb_script($user_ID);
				$content .= '<fb:comments';
				$content .= ' num_posts="' . (empty($posts) ? '2' : $posts) . '"';
				$content .= ' width="' . (empty($width) ? '500' : $width) . '"';
				$content .= ' colorscheme="' . (empty($colorscheme) ? 'light' : $colorscheme) . '"';
				$content .= ' href="' . $link . '"></fb:comments>';
				$content .= '</div>';

				return $content;
			}
			else
				return '';
		}

		// Get HTML face pile
		function Get_face_pile($post) {
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {
				// Get options
				$size = get_user_meta($user_ID, c_al2fb_meta_pile_size, true);
				$width = get_user_meta($user_ID, c_al2fb_meta_pile_width, true);
				$rows = get_user_meta($user_ID, c_al2fb_meta_pile_rows, true);
				$link = get_user_meta($user_ID, c_al2fb_meta_like_link, true);
				if (empty($link))
					$link = get_permalink($post->ID);

				// Face pile
				$content = '<div class="al2fb_face_pile">';
				$content .= '<div id="fb-root"></div>';
				$content .= self::Get_fb_script($user_ID);
				$content .= '<fb:facepile';
				$content .= ' size="' . (empty($size) ? 'small' : $size) . '"';
				$content .= ' width="' . (empty($width) ? '200' : $width) . '"';
				$content .= ' max_rows="' . (empty($rows) ? '1' : $rows) . '"';
				$content .= ' href="' . $link . '"></fb:facepile>';
				$content .= '</div>';

				return $content;
			}
			else
				return '';
		}

		// Get HTML profile link
		function Get_profile_link($post) {
			$content = '';
			try {
				$user_ID = self::Get_user_ID($post);
				$me = self::Get_fb_me_cached($user_ID, false);
				if (!empty($me)) {
					$img = 'http://creative.ak.fbcdn.net/ads3/creative/pressroom/jpg/b_1234209334_facebook_logo.jpg';
					$content .= '<div class="al2fb_profile"><a href="' . $me->link . '">';
					$content .= '<img src="' . $img . '" alt="Facebook profile" /></a></div>';
				}
			}
			catch (Exception $e) {
			}
			return $content;
		}

		// Get HTML Facebook registration
		function Get_registration($post) {
			// Check if registration enabled
			if (!get_option('users_can_register'))
				return '';

			// Get data
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {
				// Check if user logged in
				if (is_user_logged_in())
					return do_shortcode(get_user_meta($user_ID, c_al2fb_meta_login_html, true));

				// Get options
				$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
				$width = get_user_meta($user_ID, c_al2fb_meta_reg_width, true);
				$border = get_user_meta($user_ID, c_al2fb_meta_like_box_border, true);
				$fields = "[{'name':'name'}";
				$fields .= ",{'name':'first_name'}";
				$fields .= ",{'name':'last_name'}";
				$fields .= ",{'name':'email'}";
				$fields .= ",{'name':'user_name','description':'" . __('WordPress user name', c_al2fb_text_domain) . "','type':'text'}";
				$fields .= ",{'name':'password'}]";

				// Build content
				if ($appid) {
					$content = '<div class="al2fb_registration">';
					$content .= '<div id="fb-root"></div>';
					$content .= self::Get_fb_script($user_ID);
					$content .= '<fb:registration';
					$content .= ' fields="' . $fields . '"';
					$content .= ' redirect-uri="' . self::Redirect_uri() . '?al2fb_reg=true&user=' . $user_ID . '&uri=' . urlencode($_SERVER['REQUEST_URI']) . '"';
					$content .= ' width="' . (empty($width) ? '530' : $width) . '"';
					$content .= ' border_color="' . (empty($border) ? '' : $border) . '">';
					$content .= '</fb:registration>';
					$content .= '</div>';
					return $content;
				}
			}
			return '';
		}

		// Get HTML Facebook login
		function Get_login($post) {
			// Get data
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {
				// Check if user logged in
				if (is_user_logged_in())
					return do_shortcode(get_user_meta($user_ID, c_al2fb_meta_login_html, true));

				// Get options
				$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
				$regurl = get_user_meta($user_ID, c_al2fb_meta_login_regurl, true);
				$faces = false;
				$width = get_user_meta($user_ID, c_al2fb_meta_login_width, true);
				$rows = get_user_meta($user_ID, c_al2fb_meta_pile_rows, true);
				$permissions = '';

				// Build content
				if ($appid) {
					$content = '<div class="al2fb_login">';
					$content .= '<div id="fb-root"></div>';
					$content .= self::Get_fb_script($user_ID);
					$content .= '<script type="text/javascript">' . PHP_EOL;
					$content .= 'function al2fb_login() {' . PHP_EOL;
					$content .= '	FB.getLoginStatus(function(response) {' . PHP_EOL;
					$content .= '		if (response.status == "unknown")' . PHP_EOL;
					$content .= '			alert("' . __('Please enable third-party cookies', c_al2fb_text_domain) . '");' . PHP_EOL;
					$content .= '		if (response.session)' . PHP_EOL;
					$content .= '			window.location="' .  self::Redirect_uri() . '?al2fb_login=true';
					$content .= '&token=" + response.session.access_token + "&uid=" + response.session.uid + "&uri=" + encodeURI(window.location.pathname + window.location.search) + "&user=' . $user_ID . '";' . PHP_EOL;
					$content .= '	});' . PHP_EOL;
					$content .= '}' . PHP_EOL;
					$content .= '</script>' . PHP_EOL;
					$content .= '<fb:login-button';
					$content .= ' registration-url="' . $regurl . '"';
					$content .= ' show_faces="' . ($faces ? 'true' : 'false') . '"';
					$content .= ' width="' . (empty($width) ? '200' : $width) . '"';
					$content .= ' max_rows="' . (empty($rows) ? '1' : $rows) . '"';
					$content .= ' perms="' . $permissions . '"';
					$content .= ' onlogin="al2fb_login();">';
					$content .= '</fb:login-button>';
					$content .= '</div>';
					return $content;
				}
			}
			return '';
		}

		// Get HTML Facebook activity feed
		function Get_activity_feed($post) {
			// Get data
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded_post_type($post)) {

				// Get options
				$domain = $_SERVER['HTTP_HOST'];
				$width = get_user_meta($user_ID, c_al2fb_meta_act_width, true);
				$height = get_user_meta($user_ID, c_al2fb_meta_act_height, true);
				$header = get_user_meta($user_ID, c_al2fb_meta_act_header, true);
				$colorscheme = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
				$font = get_user_meta($user_ID, c_al2fb_meta_like_font, true);
				$border = get_user_meta($user_ID, c_al2fb_meta_like_box_border, true);
				$recommend = get_user_meta($user_ID, c_al2fb_meta_act_recommend, true);

				// Build content
				$content = '<div class="al2fb_activity_feed">';
				$content .= '<div id="fb-root"></div>';
				$content .= self::Get_fb_script($user_ID);
				$content .= '<fb:activity';
				$content .= ' site="' . $domain . '"';
				$content .= ' width="' . (empty($width) ? '300' : $width) . '"';
				$content .= ' height="' . (empty($height) ? '300' : $height) . '"';
				$content .= ' colorscheme="' . (empty($colorscheme) ? 'light' : $colorscheme) . '"';
				$content .= ' header="' . ($header ? 'true' : 'false') . '"';
				$content .= ' font="' . (empty($font) ? 'arial' : $font) . '"';
				$content .= ' border_color="' . (empty($border) ? '' : $border) . '"';
				$content .= ' recommendations="' . ($recommend ? 'true' : 'false') . '">';
				$content .= '</fb:activity>';
				$content .= '</div>';
				return $content;
			}
			return '';
		}

		// Handle Facebook registration
		function Facebook_registration() {
			// Decode Facebook data
			$reg = self::Parse_signed_request($_REQUEST['user']);

			// Check result
			if ($reg == null) {
				header('Content-type: text/plain');
				_e('Facebook registration failed', c_al2fb_text_domain);
				echo PHP_EOL;
			}
			else {
				if (!get_option('users_can_register')) {
					// Registration not enabled
					header('Content-type: text/plain');
					_e('User registration disabled', c_al2fb_text_domain);
					echo PHP_EOL;
				}
				else if (empty($reg['registration']['email'])) {
					// E-mail missing
					header('Content-type: text/plain');
					_e('Facebook e-mail address missing', c_al2fb_text_domain);
					echo PHP_EOL;
					if ($this->debug)
						print_r($reg);
				}
				else if (email_exists($reg['registration']['email'])) {
					// E-mail in use
					header('Content-type: text/plain');
					_e('E-mail address in use', c_al2fb_text_domain);
					echo PHP_EOL;
					if ($this->debug)
						print_r($reg);
				}
				else if (empty($reg['user_id'])) {
					// User ID missing
					header('Content-type: text/plain');
					_e('Facebook user ID missing', c_al2fb_text_domain);
					echo PHP_EOL;
					if ($this->debug)
						print_r($reg);
				}
				else {
					// Create new WP user
					$user_ID = wp_insert_user(array(
						'first_name' => $reg['registration']['first_name'],
						'last_name' => $reg['registration']['last_name'],
						'user_email' => $reg['registration']['email'],
						'user_login' => $reg['registration']['user_name'],
						'user_pass' => $reg['registration']['password']
					)) ;

					// Check result
					if (is_wp_error($user_ID)) {
						header('Content-type: text/plain');
						_e($user_ID->get_error_message());
						echo PHP_EOL;
						if ($this->debug)
							print_r($reg);
					}
					else {
						// Persist Facebook ID
						update_user_meta($user_ID, c_al2fb_meta_facebook_id, $reg['user_id']);

						// Log user in
						$user = self::Login_by_email($reg['registration']['email'], true);

						// Redirect
						$self = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_REQUEST['uri'];
						$redir = get_user_meta($user_ID, c_al2fb_meta_login_redir, true);
						wp_redirect($redir ? $redir : $self);
					}
				}
			}
		}

		// Handle Facebook login
		function Facebook_login() {
			header('Content-type: text/plain');
			try {
				// Check token
				$url = 'https://graph.facebook.com/' . $_REQUEST['uid'];
				$url = apply_filters('al2fb_url', $url);
				$query = http_build_query(array('access_token' => $_REQUEST['token']), '', '&');
				$response = self::Request($url, $query, 'GET');
				$me = json_decode($response);

				// Workaround if no e-mail present
				if (!empty($me) && empty($me->email)) {
					$users = get_users(array(
						'meta_key' => c_al2fb_meta_facebook_id,
						'meta_value' => $me->id
					));
					if (count($users) == 0) {
						$regurl = get_user_meta($_REQUEST['user'], c_al2fb_meta_login_regurl, true);
						if (!empty($regurl))
							wp_redirect($regurl);
					}
					else if (count($users) == 1)
						$me->email = $users[0]->user_email;
				}

				// Check Facebook user
				if (!empty($me) && !empty($me->id)) {
					// Find user by Facebook ID
					$users = get_users(array(
						'meta_key' => c_al2fb_meta_facebook_id,
						'meta_value' => $me->id
					));

					// Check if found one
					if (count($users) == 1) {
						// Try to login
						$user = self::Login_by_email($users[0]->user_email, true);

						// Check login
						if ($user) {
							// Redirect
							$self = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_REQUEST['uri'];
							$redir = get_user_meta($_REQUEST['user'], c_al2fb_meta_login_redir, true);
							wp_redirect($redir ? $redir : $self);
						}
						else {
							// User not found (anymore)
							header('Content-type: text/plain');
							_e('User not found', c_al2fb_text_domain);
							echo PHP_EOL;
							if ($this->debug)
								print_r($me);
						}
					}
					else {
						$self = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_REQUEST['uri'];
						$regurl = get_user_meta($_REQUEST['user'], c_al2fb_meta_login_regurl, true);
						wp_redirect($regurl ? $regurl : $self);
					}
				}
				else {
					// Something went wrong
					header('Content-type: text/plain');
					_e('Could not verify Facebook login', c_al2fb_text_domain);
					echo PHP_EOL;
					if ($this->debug)
						print_r($me);
				}
			}
			catch (Exception $e) {
				// Communication error?
				header('Content-type: text/plain');
				_e('Could not verify Facebook login', c_al2fb_text_domain);
				echo PHP_EOL;
				echo $e->getMessage();
				echo PHP_EOL;
			}
		}

		// Log WordPress user in using e-mail
		function Login_by_email($email, $rememberme) {
			global $user;
			$user = null;

			$userdata = get_user_by('email', $email);
			if ($userdata) {
				$user = new WP_User($userdata->ID);
				wp_set_current_user($userdata->ID, $userdata->user_login);
				wp_set_auth_cookie($userdata->ID, $rememberme);
				do_action('wp_login', $userdata->user_login);
			}
			return $user;
		}

		// Decode Facebook registration response
		function Parse_signed_request($user_ID) {
			$signed_request = $_REQUEST['signed_request'];
			$secret = get_user_meta($user_ID, c_al2fb_meta_app_secret, true);

			list($encoded_sig, $payload) = explode('.', $signed_request, 2);

			// Decode the data
			$sig = self::base64_url_decode($encoded_sig);
			$data = json_decode(self::base64_url_decode($payload), true);

			if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
				return null;

			// Check sig
			$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
			if ($sig !== $expected_sig)
				return null;

			return $data;
		}

		// Helper: base64 decode url
		function base64_url_decode($input) {
			return base64_decode(strtr($input, '-_', '+/'));
		}

		// Profile personal options
		function Personal_options($user) {
			$fid = get_user_meta($user->ID, c_al2fb_meta_facebook_id, true);
			echo '<th scope="row">' . __('Facebook ID', c_al2fb_text_domain) . '</th><td>';
			echo '<input type="text" name="' . c_al2fb_meta_facebook_id . '" id="' . c_al2fb_meta_facebook_id . '" value="' . $fid . '">';
			if ($fid)
				echo '<a href="' . self::Get_fb_profilelink($fid) . '" target="_blank">' . $fid . '</a></td>';
			else
				echo '<a href="http://apps.facebook.com/whatismyid/" target="_blank">' . __('What is my Facebook ID?', c_al2fb_text_domain) . '</a></td>';
			echo '</tr>';
		}

		// Handle personal options change
		function Personal_options_update($user_id) {
			update_user_meta($user_id, c_al2fb_meta_facebook_id, trim($_REQUEST[c_al2fb_meta_facebook_id]));
		}

		// Modify comment list
		function Comments_array($comments, $post_ID) {
			$post = get_post($post_ID);
			$user_ID = self::Get_user_ID($post);

			// Integration?
			if ($user_ID && !self::Is_excluded_post_type($post) &&
				!get_post_meta($post->ID, c_al2fb_meta_nointegrate, true) &&
				$post->comment_status == 'open') {

				// Get time zone offset
				$tz_off = get_option('gmt_offset');
				if (empty($tz_off))
					$tz_off = 0;
				else
					$tz_off = $tz_off * 3600;

				// Get Facebook comments
				if (self::Is_recent($post) && get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) {
					$fb_comments = self::Get_comments_or_likes($post, false);
					if ($fb_comments) {
						// Get WordPress comments
						$stored_comments = get_comments('post_id=' . $post->ID);
						$stored_comments = array_merge($stored_comments,
							get_comments('status=spam&post_id=' . $post->ID));
						$stored_comments =  array_merge($stored_comments,
							get_comments('status=trash&post_id=' . $post->ID));
						$stored_comments =  array_merge($stored_comments,
							get_comments('status=hold&post_id=' . $post->ID));
						$deleted_fb_comment_ids = get_post_meta($post->ID, c_al2fb_meta_fb_comment_id, false);

						foreach ($fb_comments->data as $fb_comment)
							if (!empty($fb_comment->id)) {
								// Check if stored comment
								$stored = false;
								if ($stored_comments)
									foreach ($stored_comments as $comment) {
										$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
										if ($fb_comment_id == $fb_comment->id) {
											$stored = true;
											break;
										}
									}
								$stored = $stored || in_array($fb_comment->id, $deleted_fb_comment_ids);

								// Create new comment
								if (!$stored) {
									$comment_ID = $fb_comment->id;
									$commentdata = array(
										'comment_post_ID' => $post_ID,
										'comment_author' => $fb_comment->from->name . ' ' . __('on Facebook', c_al2fb_text_domain),
										'comment_author_email' => $fb_comment->from->id . '@facebook.com',
										'comment_author_url' => self::Get_fb_profilelink($fb_comment->from->id),
										'comment_author_IP' => '',
										'comment_date' => date('Y-m-d H:i:s', strtotime($fb_comment->created_time) + $tz_off),
										'comment_date_gmt' => date('Y-m-d H:i:s', strtotime($fb_comment->created_time)),
										'comment_content' => $fb_comment->message,
										'comment_karma' => 0,
										'comment_approved' => 1,
										'comment_agent' => 'AL2FB',
										'comment_type' => '', // pingback|trackback
										'comment_parent' => 0,
										'user_id' => 0
									);

									// Copy Facebook comment to WordPress database
									if (get_user_meta($user_ID, c_al2fb_meta_fb_comments_copy, true)) {
										// Apply filters
										if (get_option(c_al2fb_option_nofilter_comments))
											$commentdata['comment_approved'] = '1';
										else {
											$commentdata = apply_filters('preprocess_comment', $commentdata);
											$commentdata = wp_filter_comment($commentdata);
											$commentdata['comment_approved'] = wp_allow_comment($commentdata);
										}

										// Insert comment in database
										$comment_ID = wp_insert_comment($commentdata);
										add_comment_meta($comment_ID, c_al2fb_meta_fb_comment_id, $fb_comment->id);
										do_action('comment_post', $comment_ID, $commentdata['comment_approved']);

										// Notify
										if ('spam' !== $commentdata['comment_approved']) {
											if ('0' == $commentdata['comment_approved'])
												wp_notify_moderator($comment_ID);
											if (get_option('comments_notify') && $commentdata['comment_approved'])
												wp_notify_postauthor($comment_ID, $commentdata['comment_type']);
										}
									}

									// Add comment to array
									if ($commentdata['comment_approved'] == 1) {
										$new = null;
										$new->comment_ID = $comment_ID;
										$new->comment_post_ID = $commentdata['comment_post_ID'];
										$new->comment_author = $commentdata['comment_author'];
										$new->comment_author_email = $commentdata['comment_author_email'];
										$new->comment_author_url = $commentdata['comment_author_url'];
										$new->comment_author_ip = $commentdata['comment_author_IP'];
										$new->comment_date = $commentdata['comment_date'];
										$new->comment_date_gmt = $commentdata['comment_date_gmt'];
										$new->comment_content = stripslashes($commentdata['comment_content']);
										$new->comment_karma = $commentdata['comment_karma'];
										$new->comment_approved = $commentdata['comment_approved'];
										$new->comment_agent = $commentdata['comment_agent'];
										$new->comment_type = $commentdata['comment_type'];
										$new->comment_parent = $commentdata['comment_parent'];
										$new->user_id = $commentdata['user_id'];
										$comments[] = $new;
									}
								}
							}
							else
								if ($this->debug)
									add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' Missing FB comment id: ' . print_r($fb_comment, true));
					}
				}

				// Get likes
				if (self::Is_recent($post) && get_user_meta($user_ID, c_al2fb_meta_fb_likes, true)) {
					$fb_likes = self::Get_comments_or_likes($post, true);
					if ($fb_likes)
						foreach ($fb_likes->data as $fb_like) {
							// Create new virtual comment
							$link = self::Get_fb_profilelink($fb_like->id);
							$new = null;
							$new->comment_ID = $fb_like->id;
							$new->comment_post_ID = $post_ID;
							$new->comment_author = $fb_like->name . ' ' . __('on Facebook', c_al2fb_text_domain);
							$new->comment_author_email = '';
							$new->comment_author_url = $link;
							$new->comment_author_ip = '';
							$new->comment_date_gmt = date('Y-m-d H:i:s', time());
							$new->comment_date = $new->comment_date_gmt;
							$new->comment_content = '<em>' . __('Liked this post', c_al2fb_text_domain) . '</em>';
							$new->comment_karma = 0;
							$new->comment_approved = 1;
							$new->comment_agent = 'AL2FB';
							$new->comment_type = 'pingback';
							$new->comment_parent = 0;
							$new->user_id = 0;
							$comments[] = $new;
						}
				}

				// Sort comments by time
				if (!empty($fb_comments) || !empty($fb_likes)) {
					usort($comments, array(&$this, 'Comment_compare'));
					if (get_option('comment_order') == 'desc')
						array_reverse($comments);
				}
			}

			// Comment link type
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			$comments_nolink = get_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, true);
			if (empty($comments_nolink))
				$comments_nolink = 'author';
			else if ($comments_nolink == 'on' || empty($link_id))
				$comments_nolink = 'none';

			if ($comments_nolink == 'none' || $comments_nolink == 'link') {
				$link = self::Get_fb_permalink($link_id);
				if ($comments)
					foreach ($comments as $comment)
						if ($comment->comment_agent == 'AL2FB')
							if ($comments_nolink == 'none')
								$comment->comment_author_url = '';
							else if ($comments_nolink == 'link')
								$comment->comment_author_url = $link;
			}

			// Permission to view?
			$min_cap = get_option(c_al2fb_option_min_cap_comment);
			if ($min_cap && !current_user_can($min_cap))
				if ($comments)
					for ($i = 0; $i < count($comments); $i++)
						if ($comments[$i]->comment_agent == 'AL2FB')
							unset($comments[$i]);

			return $comments;
		}

		// Sort helper
		function Comment_compare($a, $b) {
			return strcmp($a->comment_date_gmt, $b->comment_date_gmt);
		}

		// Get comment count with FB comments/likes
		function Get_comments_number($count, $post_ID) {
			$post = get_post($post_ID);
			$user_ID = self::Get_user_ID($post);

			// Permission to view?
			$min_cap = get_option(c_al2fb_option_min_cap_comment);
			if ($min_cap && !current_user_can($min_cap)) {
				$stored_comments = get_comments('post_id=' . $post->ID);
				if ($stored_comments)
					foreach ($stored_comments as $comment)
						if ($comment->comment_agent == 'AL2FB')
							$count--;
			}

			// Integration turned off?
			if (!$user_ID || self::Is_excluded_post_type($post) ||
				get_post_meta($post->ID, c_al2fb_meta_nointegrate, true) ||
				$post->comment_status != 'open')
				return $count;

			if (self::Is_recent($post)) {
				// Comment count
				if (get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) {
					$fb_comments = self::Get_comments_or_likes($post, false);
					if ($fb_comments) {
						$stored_comments = get_comments('post_id=' . $post->ID);
						$stored_comments = array_merge($stored_comments,
							get_comments('status=spam&post_id=' . $post->ID));
						$stored_comments =  array_merge($stored_comments,
							get_comments('status=trash&post_id=' . $post->ID));
						$stored_comments =  array_merge($stored_comments,
							get_comments('status=hold&post_id=' . $post->ID));
						$deleted_fb_comment_ids = get_post_meta($post->ID, c_al2fb_meta_fb_comment_id, false);

						foreach ($fb_comments->data as $fb_comment) {
							// Check if comment in database
							$stored = false;
							if ($stored_comments)
								foreach ($stored_comments as $comment) {
									$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
									if ($fb_comment_id == $fb_comment->id) {
										$stored = true;
										break;
									}
								}

							// Check if comment deleted
							$stored = $stored || in_array($fb_comment->id, $deleted_fb_comment_ids);

							// Only count if not in database or deleted
							if (!$stored)
								$count++;
						}
					}
				}

				// Like count
				if (get_user_meta($user_ID, c_al2fb_meta_fb_likes, true))
					$fb_likes = self::Get_comments_or_likes($post, true);
				if (!empty($fb_likes))
					$count += count($fb_likes->data);
			}

			return $count;
		}

		// Annotate FB comments/likes
		function Comment_class($classes) {
			global $comment;
			if (!empty($comment) && $comment->comment_agent == 'AL2FB')
				$classes[] = 'facebook-comment';
			return $classes;
		}

		// Get FB picture as avatar
		function Get_avatar($avatar, $id_or_email, $size, $default) {
			if (is_object($id_or_email)) {
				$comment = $id_or_email;
				if ($comment->comment_agent == 'AL2FB' &&
					($comment->comment_type == '' || $comment->comment_type == 'comment')) {

					// Get picture url
					$id = explode('id=', $comment->comment_author_url);
					if (count($id) == 2) {
						$fb_picture_url = self::Get_fb_picture_url_cached($id[1], 'normal');

						// Build avatar image
						if ($fb_picture_url) {
							$avatar = '<img alt="' . esc_attr($comment->comment_author) . '"';
							$avatar .= ' src="' . $fb_picture_url . '"';
							$avatar .= ' class="avatar avatar-' . $size . ' photo al2fb"';
							$avatar .= ' height="' . $size . '"';
							$avatar .= ' width="' . $size . '"';
							$avatar .= ' />';
						}
					}
				}
			}
			return $avatar;
		}

		function Get_comments_or_likes($post, $likes, $cached = true) {
			$user_ID = self::Get_user_ID($post);
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			if ($link_id)
				try {
					if ($likes)
						$result = self::Get_fb_likes_cached($user_ID, $link_id, $cached);
					else
						$result = self::Get_fb_comments_cached($user_ID, $link_id, $cached);

					// Remove previous errors
					$error = get_post_meta($post->ID, c_al2fb_meta_error, true);
					if (strpos($error, 'Import comment: ') !== false) {
						delete_post_meta($post->ID, c_al2fb_meta_error, $error);
						delete_post_meta($post->ID, c_al2fb_meta_error_time);
					}

					return $result;
				}
				catch (Exception $e) {
					update_post_meta($post->ID, c_al2fb_meta_error, 'Import comment: ' . $e->getMessage());
					update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
					return null;
				}
			return null;
		}

		function Get_user_ID($post) {
			if (is_multisite())
				$shared_user_ID = get_site_option(c_al2fb_option_app_share);
			else
				$shared_user_ID = get_option(c_al2fb_option_app_share);
			if ($shared_user_ID)
				return $shared_user_ID;
			return $post->post_author;
		}

		// Generic http request
		function Request($url, $query, $type) {
			// Get timeout
			$timeout = get_option(c_al2fb_option_timeout);
			if (!$timeout)
				$timeout = 25;

			// Use cURL if available
			if (function_exists('curl_init') && !get_option(c_al2fb_option_nocurl))
				return self::Request_cURL($url, $query, $type, $timeout);

			if (version_compare(PHP_VERSION, '5.2.1') < 0)
				ini_set('default_socket_timeout', $timeout);

			$this->php_error = '';
			set_error_handler(array(&$this, 'PHP_error_handler'));
			if ($type == 'GET') {
				$context = stream_context_create(array(
				'http' => array(
					'method'  => 'GET',
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'timeout' => $timeout
					)
				));
				$content = file_get_contents($url . ($query ? '?' . $query : ''), false, $context);
			}
			else {
				$context = stream_context_create(array(
					'http' => array(
						'method'  => 'POST',
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'timeout' => $timeout,
						'content' => $query
					)
				));
				$content = file_get_contents($url, false, $context);
			}
			restore_error_handler();

			// Check for errors
			$status = false;
			$auth_error = '';
			if (!empty($http_response_header))
				foreach ($http_response_header as $h)
					if (strpos($h, 'HTTP/') === 0) {
						$status = explode(' ', $h);
						$status = intval($status[1]);
					}
					else if (strpos($h, 'WWW-Authenticate:') === 0)
						$auth_error = $h;

			if ($status == 200)
				return $content;
			else {
				if ($auth_error)
					$msg = 'Error ' . $status . ': ' . $auth_error;
				else
					$msg = 'Error ' . $status . ': ' . $this->php_error . ' ' . print_r($http_response_header, true);
				update_option(c_al2fb_last_error, $msg);
				update_option(c_al2fb_last_error_time, date('c'));
				throw new Exception($msg);
			}
		}

		// Persist PHP errors
		function PHP_error_handler($errno, $errstr) {
			$this->php_error = $errstr;
		}

		// cURL http request
		function Request_cURL($url, $query, $type, $timeout) {
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

			if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
				curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($c, CURLOPT_MAXREDIRS, 10);
			}
			curl_setopt($c, CURLOPT_TIMEOUT, $timeout);

			if ($type == 'GET')
				curl_setopt($c, CURLOPT_URL, $url . ($query ? '?' . $query : ''));
			else {
				curl_setopt($c, CURLOPT_URL, $url);
				curl_setopt($c, CURLOPT_POST, true);
				curl_setopt($c, CURLOPT_POSTFIELDS, $query);
			}

			if (get_option(c_al2fb_option_noverifypeer))
				curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);

			$content = curl_exec($c);
			$errno = curl_errno($c);
			$info = curl_getinfo($c);
			curl_close($c);

			if ($errno === 0 && $info['http_code'] == 200)
				return $content;
			else {
				$error = json_decode($content);
				$error = empty($error->error->message) ? $content : $error->error->message;
				if ($errno || !$error)
					$msg = 'cURL error ' . $errno . ': ' . $error . ' ' . print_r($info, true);
				else
					$msg = $error;
				update_option(c_al2fb_last_error, $msg);
				update_option(c_al2fb_last_error_time, date('c'));
				throw new Exception($msg);
			}
		}

		function user_can($user, $capability) {
			if (!is_object($user))
				$user = new WP_User($user);

			if (!$user || !$user->ID)
				return false;

			$args = array_slice(func_get_args(), 2 );
			$args = array_merge(array($capability), $args);

			return call_user_func_array(array(&$user, 'has_cap'), $args);
		}

		// Add cron schedule
		function Cron_schedules($schedules) {
			if (get_option(c_al2fb_option_cron_enabled)) {
				$duration = self::Get_duration(false);
				$schedules['al2fb_schedule'] = array(
					'interval' => $duration,
					'display' => __('Add Link to Facebook', c_al2fb_text_domain));
			}
			return $schedules;
		}

		function Cron_filter($where = '') {
			$maxage = intval(get_option(c_al2fb_option_msg_maxage));
			if (!$maxage)
				$maxage = 7;

			return $where . " AND post_date > '" . date('Y-m-d', strtotime('-' . $maxage . ' days')) . "'";
		}

		function Cron() {
			$posts = 0;
			$comments = 0;
			$likes = 0;

			// Query recent posts
			add_filter('posts_where', array(&$this, 'Cron_filter'));
			$query = new WP_Query('post_type=any&meta_key=' . c_al2fb_meta_link_id);
			remove_filter('posts_where', array(&$this, 'Cron_filter'));

			while ($query->have_posts()) {
				$posts++;
				$query->the_post();
				$post = $query->post;

				// Integration?
				if (!get_post_meta($post->ID, c_al2fb_meta_nointegrate, true) &&
					$post->comment_status == 'open') {
					$user_ID = self::Get_user_ID($post);

					// Get Facebook comments
					if (get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) {
						$fb_comments = self::Get_comments_or_likes($post, false, false);
						$comments += count($fb_comments->data);
					}

					// Get likes
					if (get_user_meta($user_ID, c_al2fb_meta_fb_likes, true)) {
						$fb_likes = self::Get_comments_or_likes($post, true, false);
						$likes += count($fb_likes->data);
					}
				}
			}

			// Debug info
			update_option(c_al2fb_option_cron_time, date('c'));
			update_option(c_al2fb_option_cron_posts, $posts);
			update_option(c_al2fb_option_cron_comments, $comments);
			update_option(c_al2fb_option_cron_likes, $likes);
		}

		// Check environment
		static function Check_prerequisites() {
			// Check WordPress version
			global $wp_version;
			if (version_compare($wp_version, '3.0') < 0)
				die('Add Link to Facebook requires at least WordPress 3.0');

			// Check basic prerequisities
			self::Check_function('add_action');
			self::Check_function('add_filter');
			self::Check_function('wp_register_style');
			self::Check_function('wp_enqueue_style');
			self::Check_function('file_get_contents');
			self::Check_function('json_decode');
			self::Check_function('md5');
		}

		static function Check_function($name) {
			if (!function_exists($name))
				die('Required WordPress function "' . $name . '" does not exist');
		}

		// Change file extension
		function Change_extension($filename, $new_extension) {
			return preg_replace('/\..+$/', $new_extension, $filename);
		}
	}
}

?>
