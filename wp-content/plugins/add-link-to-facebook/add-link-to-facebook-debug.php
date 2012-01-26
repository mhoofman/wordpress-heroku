<?php

/*
	Support class Add Link to Facebook debug info
	Copyright (c) 2011, 2012 by Marcel Bokhorst
*/

// Generate debug info
function al2fb_debug_info($al2fb) {
	// Get current user
	global $user_ID;
	get_currentuserinfo();

	// Get users
	global $wpdb;
	$users = $wpdb->get_var('SELECT COUNT(ID) FROM ' . $wpdb->users);

	// Get versions
	global $wp_version;
	if (!function_exists('get_plugins'))
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	$plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__)));
	$plugin_version = $plugin_folder[basename($al2fb->main_file)]['Version'];

	// Get charset, token
	$charset = get_bloginfo('charset');

	// Get application
	try {
		if ($al2fb->Is_authorized($user_ID)) {
			$a = $al2fb->Get_fb_application($user_ID);
			$app = '<a href="' . $a->link . '" target="_blank">' . $a->name . '</a>';
		}
		else
			$app = 'n/a';
	}
	catch (Exception $e) {
		$app = get_user_meta($user_ID, c_al2fb_meta_client_id, true) . ': ' . $e->getMessage();
	}

	// Sharing
	if (is_multisite())
		$shared_user_ID = get_site_option(c_al2fb_option_app_share);
	else
		$shared_user_ID = get_option(c_al2fb_option_app_share);

	// Get page
	try {
		if ($al2fb->Is_authorized($user_ID)) {
			$me = $al2fb->Get_fb_me($user_ID, false);
			if ($me == null)
				$page = 'n/a';
			else {
				$page = '<a href="' . $me->link . '" target="_blank">' . htmlspecialchars($me->name, ENT_QUOTES, $charset);
				if (!empty($me->category))
					$page .= ' - ' . htmlspecialchars($me->category, ENT_QUOTES, $charset);
				$page .= '</a>';
			}
		}
		else
			$page = 'n/a';
	}
	catch (Exception $e) {
		$page = get_user_meta($user_ID, c_al2fb_meta_page, true) . ': ' . $e->getMessage();
	}

	// Get picture
	$picture = '<a href="' . get_user_meta($user_ID, c_al2fb_meta_picture, true) . '" target="_blank">' . get_user_meta($user_ID, c_al2fb_meta_picture, true) . '</a>';
	$picture_default = '<a href="' . get_user_meta($user_ID, c_al2fb_meta_picture_default, true) . '" target="_blank">' . get_user_meta($user_ID, c_al2fb_meta_picture_default, true) . '</a>';

	// Get theme data
	$theme_data = get_theme_data(STYLESHEETPATH . '/style.css');

	$info = '<div class="al2fb_debug"><table border="1">';
	$info .= '<tr><td>Time:</td><td>' . date('c') . '</td></tr>';
	$info .= '<tr><td>Server software:</td><td>' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'], ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>SAPI:</td><td>' . htmlspecialchars(php_sapi_name(), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>PHP version:</td><td>' . PHP_VERSION . '</td></tr>';
	$info .= '<tr><td>safe_mode:</td><td>' . (ini_get('safe_mode') ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>open_basedir:</td><td>' . ini_get('open_basedir') . '</td></tr>';
	$info .= '<tr><td>User agent:</td><td>' . htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>WordPress version:</td><td>' . $wp_version . '</td></tr>';
	$info .= '<tr><td>Theme name:</td><td>' . '<a href="' . $theme_data['URI'] . '" target="_blank">' . htmlspecialchars($theme_data['Name'], ENT_QUOTES, $charset) . '</a>' . '</td></tr>';
	$info .= '<tr><td>Theme version:</td><td>' . htmlspecialchars($theme_data['Version'], ENT_QUOTES, $charset) . '</td></tr>';

	$active  = get_option('active_plugins', array());
	foreach (get_plugins() as $plugin_tag => $plugin_data)
		if (in_array($plugin_tag, $active))
			$info .= '<tr><td>Active plugin:</td><td><a href="' . $plugin_data['PluginURI'] . '" target="_blank">' . htmlspecialchars($plugin_data['Name'], ENT_QUOTES, $charset) . '</a></td></tr>';

	$info .= '<tr><td>Plugin version:</td><td>' . $plugin_version . '</td></tr>';
	$info .= '<tr><td>Settings version:</td><td>' . get_option(c_al2fb_option_version) . '</td></tr>';
	$info .= '<tr><td>Multi site:</td><td>' . (is_multisite() ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Site id:</td><td>' . $al2fb->site_id . '</td></tr>';
	$info .= '<tr><td>Blog id:</td><td>' . $al2fb->blog_id . '</td></tr>';
	$info .= '<tr><td>Number of users:</td><td>' . $users . '</td></tr>';
	$info .= '<tr><td>Blog address (home):</td><td><a href="' . get_home_url() . '" target="_blank">' . htmlspecialchars(get_home_url(), ENT_QUOTES, $charset) . '</a></td></tr>';
	$info .= '<tr><td>WordPress address (site):</td><td><a href="' . get_site_url() . '" target="_blank">' . htmlspecialchars(get_site_url(), ENT_QUOTES, $charset) . '</a></td></tr>';
	$info .= '<tr><td>Redirect URI:</td><td><a href="' . $al2fb->Redirect_uri() . '" target="_blank">' . htmlspecialchars($al2fb->Redirect_uri(), ENT_QUOTES, $charset) . '</a></td></tr>';
	$info .= '<tr><td>Authorize URL:</td><td><a href="' . $al2fb->Authorize_url($user_ID) . '" target="_blank">' . htmlspecialchars($al2fb->Authorize_url($user_ID), ENT_QUOTES, $charset) . '</a></td></tr>';
	$info .= '<tr><td>Authorization init:</td><td>' . htmlspecialchars(get_option(c_al2fb_log_redir_init), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Authorization check:</td><td>' . htmlspecialchars(get_option(c_al2fb_log_redir_check), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Redirect time:</td><td>' . htmlspecialchars(get_option(c_al2fb_log_redir_time), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Redirect referer:</td><td><a href="' . get_option(c_al2fb_log_redir_ref) . '" target="_blank">' . htmlspecialchars(get_option(c_al2fb_log_redir_ref), ENT_QUOTES, $charset) . '</a></td></tr>';
	$info .= '<tr><td>Redirect from:</td><td>' . htmlspecialchars(get_option(c_al2fb_log_redir_from), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Redirect to:</td><td><a href="' . get_option(c_al2fb_log_redir_to) . '" target="_blank">' . htmlspecialchars(get_option(c_al2fb_log_redir_to), ENT_QUOTES, $charset) . '</a></td></tr>';
	$info .= '<tr><td>Get token:</td><td><a href="' . get_option(c_al2fb_log_get_token) . '" target="_blank">' . htmlspecialchars(get_option(c_al2fb_log_get_token), ENT_QUOTES, $charset) . '</a></td></tr>';
	$info .= '<tr><td>Authorized:</td><td>' . ($al2fb->Is_authorized($user_ID) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Authorized time:</td><td>' . get_option(c_al2fb_log_auth_time) . '</td></tr>';
	$info .= '<tr><td>allow_url_fopen:</td><td>' . (ini_get('allow_url_fopen') ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>cURL:</td><td>' . (function_exists('curl_init') ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>openssl loaded:</td><td>' . (extension_loaded('openssl') ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>Encoding:</td><td>' . htmlspecialchars(get_option('blog_charset'), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Facebook:</td><td>' . htmlspecialchars(get_user_meta($user_ID, c_al2fb_meta_fb_encoding, true), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Locale:</td><td>' . htmlspecialchars(WPLANG, ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Facebook:</td><td>' . htmlspecialchars($al2fb->Get_locale($user_ID), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>mb_convert_encoding:</td><td>' . (function_exists('mb_convert_encoding') ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>Application:</td><td>' . $app . '</td></tr>';
	$info .= '<tr><td>User:</td><td>' . $user_ID . '=' . get_the_author_meta('user_login', $user_ID) . '</td></tr>';
	$info .= '<tr><td>Shared user:</td><td>' . $shared_user_ID . '=' . get_the_author_meta('user_login', $shared_user_ID) . '</td></tr>';

	$info .= '<tr><td>Picture type:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_picture_type, true) . '</td></tr>';
	$info .= '<tr><td>Custom picture URL:</td><td>' . $picture . '</td></tr>';
	$info .= '<tr><td>Default picture URL:</td><td>' . $picture_default . '</td></tr>';

	$info .= '<tr><td>Page:</td><td>' . $page . '</td></tr>';
	$info .= '<tr><td>Page owner:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_page_owner, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Use groups:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_use_groups, true) ? 'Yes' : 'No')  . '</td></tr>';
	$info .= '<tr><td>Group:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_group, true) . '</td></tr>';

	$info .= '<tr><td>Caption:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_caption, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Excerpt:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_msg, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Trailer:</td><td>' . htmlspecialchars(get_user_meta($user_ID, c_al2fb_meta_trailer, true), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Hyperlink:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_hyperlink, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Share link:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_share_link, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Shortlink:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_shortlink, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Page link:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_add_new_page, true) ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>FB comments:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_fb_comments, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>FB comments postback:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_fb_comments_postback, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>FB comments copy:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_fb_comments_copy, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>FB comments no link:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, true) . '</td></tr>';
	$info .= '<tr><td>FB likes:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_fb_likes, true) ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>Post likers:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_post_likers, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Post like button:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_post_like_button, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Not home page:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_nohome, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Not posts:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_noposts, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Not pages:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_nopages, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Not archives:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_noarchives, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Not categories:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_nocategories, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Like layout:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_like_layout, true) . '</td></tr>';
	$info .= '<tr><td>Like faces:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_faces, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Like width:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_like_width, true) . '</td></tr>';
	$info .= '<tr><td>Like action:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_like_action, true) . '</td></tr>';
	$info .= '<tr><td>Like font:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_like_font, true) . '</td></tr>';
	$info .= '<tr><td>Like color scheme:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true) . '</td></tr>';
	$info .= '<tr><td>Like link:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_like_link, true) . '</td></tr>';
	$info .= '<tr><td>Like top:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_top, true) ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>Send button:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_post_send_button, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Combine buttons:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_post_combine_buttons, true) ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>Like box width:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_like_box_width, true) . '</td></tr>';
	$info .= '<tr><td>Like box border:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_like_box_border, true) . '</td></tr>';
	$info .= '<tr><td>Like box no header:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_box_noheader, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Like box no stream:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_like_box_nostream, true) ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>Comments posts:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_comments_posts, true) . '</td></tr>';
	$info .= '<tr><td>Comments width:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_comments_width, true) . '</td></tr>';
	$info .= '<tr><td>Comments auto:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_comments_auto, true) ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>Facepile size:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_pile_size, true) . '</td></tr>';
	$info .= '<tr><td>Facepile width:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_pile_width, true) . '</td></tr>';
	$info .= '<tr><td>Facepile rows:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_pile_rows, true) . '</td></tr>';

	$info .= '<tr><td>Registration width:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_reg_width, true) . '</td></tr>';
	$info .= '<tr><td>Login width:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_login_width, true) . '</td></tr>';
	$info .= '<tr><td>Registration URL:</td><td><a href="' . get_user_meta($user_ID, c_al2fb_meta_login_regurl, true) . '" target="_blank">Link</a></td></tr>';
	$info .= '<tr><td>Redir URL:</td><td><a href="' . get_user_meta($user_ID, c_al2fb_meta_login_redir, true) . '" target="_blank">Link</a></td></tr>';
	$info .= '<tr><td>Login text/HTML:</td><td><a href="' . htmlspecialchars(get_user_meta($user_ID, c_al2fb_meta_login_html, true), ENT_QUOTES, $charset) . '" target="_blank">Link</a></td></tr>';

	$info .= '<tr><td>Activity width:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_act_width, true) . '</td></tr>';
	$info .= '<tr><td>Activity height:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_act_height, true) . '</td></tr>';
	$info .= '<tr><td>Activity header:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_act_header, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Activity recommend:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_act_recommend, true) ? 'Yes' : 'No') . '</td></tr>';

	$fid = get_user_meta($user_ID, c_al2fb_meta_facebook_id, true);
	$info .= '<tr><td>Facebook ID:</td><td><a href="' . $al2fb->Get_fb_profilelink($fid) . '" target="_blank">' . $fid . '</a></td></tr>';

	$info .= '<tr><td>OGP:</td><td>' . (get_user_meta($user_ID, c_al2fb_meta_open_graph, true) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>OGP type:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_open_graph_type, true) . '</td></tr>';
	$info .= '<tr><td>OGP admins:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_open_graph_admins, true) . '</td></tr>';

	$info .= '<tr><td>Timeout:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_timeout), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>No notices:</td><td>' . (get_option(c_al2fb_option_nonotice) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Min. capability:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_min_cap), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Min. capability comments:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_min_cap_comment), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Refresh comments:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_msg_refresh), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Refresh age:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_msg_maxage), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Max. length:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_max_descr), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Max. text length:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_max_text), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Exclude post types:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_exclude_type), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Exclude categories:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_exclude_cat), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Exclude tags:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_exclude_tag), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Exclude authors:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_exclude_author), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Meta box:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_metabox_type), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>No verify peer:</td><td>' . (get_option(c_al2fb_option_noverifypeer) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Shortcode/widget:</td><td>' . (get_option(c_al2fb_option_shortcode_widget) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>No shortcode:</td><td>' . (get_option(c_al2fb_option_noshortcode) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>No filter:</td><td>' . (get_option(c_al2fb_option_nofilter) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>No filter comments:</td><td>' . (get_option(c_al2fb_option_nofilter_comments) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Site URL:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_siteurl), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Do not use cURL:</td><td>' . (get_option(c_al2fb_option_nocurl) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Use publish_post:</td><td>' . (get_option(c_al2fb_option_use_pp) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Debug:</td><td>' . (get_option(c_al2fb_option_debug) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>SSP:</td><td>' . (get_option(c_al2fb_option_use_ssp) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>SSP info:</td><td><a href="' . get_option(c_al2fb_option_ssp_info) . '">link</a></td></tr>';
	$info .= '<tr><td>Filter prio:</td><td>' . intval(get_option(c_al2fb_option_filter_prio)) . '</td></tr>';
	$info .= '<tr><td>No script:</td><td>' . (get_option(c_al2fb_option_noscript) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Clean:</td><td>' . (get_option(c_al2fb_option_clean) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>CSS:</td><td>' . htmlspecialchars(get_option(c_al2fb_option_css), ENT_QUOTES, $charset) . '</td></tr>';

	$info .= '<tr><td>wp_get_attachment_thumb_url:</td><td>' . (function_exists('wp_get_attachment_thumb_url') ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>wp_get_attachment_image_src:</td><td>' . (function_exists('wp_get_attachment_image_src') ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>theme - post-thumbnails:</td><td>' . (current_theme_supports('post-thumbnails') ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>get_post_thumbnail_id:</td><td>' . (function_exists('get_post_thumbnail_id') ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>wp_get_attachment_image_src:</td><td>' . (function_exists('wp_get_attachment_image_src') ? 'Yes' : 'No') . '</td></tr>';

	$info .= '<tr><td>Max exec time:</td><td>' . ini_get('max_execution_time') . '</td></tr>';
	$info .= '<tr><td>Memory usage:</td><td>' . memory_get_usage() . '/' . ini_get('memory_limit') . '</td></tr>';
	$info .= '<tr><td>Links added:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_stat, true) . '</td></tr>';
	$info .= '<tr><td>Current week:</td><td>' . get_user_meta($user_ID, c_al2fb_meta_week, true) . '</td></tr>';

	// Last posts
	$posts = new WP_Query(array('posts_per_page' => 10));
	while ($posts->have_posts()) {
		$posts->next_post();
		$userdata = get_userdata($posts->post->post_author);
		$link_id = get_post_meta($posts->post->ID, c_al2fb_meta_link_id, true);

		// Selected picture
		$selected_picture = null;
		$image_id = get_post_meta($posts->post->ID, c_al2fb_meta_image_id, true);
		if (!empty($image_id) && function_exists('wp_get_attachment_thumb_url'))
			$selected_picture = wp_get_attachment_thumb_url($image_id);

		// Attached picture
		$attached_picture = null;
		$images = array_values(get_children('post_type=attachment&post_mime_type=image&order=ASC&post_parent=' . $posts->post->ID));
		if (!empty($images) && function_exists('wp_get_attachment_image_src')) {
			$picture = wp_get_attachment_image_src($images[0]->ID, 'thumbnail');
			if ($picture && $picture[0])
				$attached_picture = $picture[0];
		}

		// Feature picture
		$featured_picture = null;
		if (current_theme_supports('post-thumbnails') &&
			function_exists('get_post_thumbnail_id') &&
			function_exists('wp_get_attachment_image_src')) {
			$picture_id = get_post_thumbnail_id($posts->post->ID);
			if ($picture_id) {
				$picture = wp_get_attachment_image_src($picture_id, 'thumbnail');
				if ($picture && $picture[0])
					$featured_picture = $picture[0];
			}
		}

		// First picture in post
		$post_picture = null;
		$content = $posts->post->post_content;
		if (!get_option(c_al2fb_option_nofilter))
			$content = apply_filters('the_content', $content);
		if (preg_match('/< *img[^>]*src *= *["\']([^"\']*)["\']/i', $content, $matches))
			$post_picture = $matches[1];

		// Author avatar
		$avatar_picture = null;
		$avatar = get_avatar($userdata->user_email);
		if (!empty($avatar))
			if (preg_match('/< *img[^>]*src *= *["\']([^"\']*)["\']/i', $avatar, $matches))
				$avatar_picture = $matches[1];

		// Actual picture
		$picture = $al2fb->Get_link_picture($posts->post, $al2fb->Get_user_ID($posts->post));

		$info .= '<tr><td>Post #' . $posts->post->ID . ':</td>';
		$info .= '<td><a href="' . get_permalink($posts->post->ID) . '" target="_blank">' . htmlspecialchars(get_the_title($posts->post->ID), ENT_QUOTES, $charset) . '</a>';
		$info .= ' by ' . htmlspecialchars($userdata->user_login, ENT_QUOTES, $charset);
		$info .= ' @ ' . $posts->post->post_date;
		$info .= ' <a href="' . $picture['picture'] . '" target="_blank">result:' . $picture['picture_type'] . '</a>';
		if (!empty($selected_picture))
			$info .= ' <a href="' . $selected_picture . '" target="_blank">selected</a>';
		if (!empty($attached_picture))
			$info .= ' <a href="' . $attached_picture . '" target="_blank">attached</a>';
		if (!empty($featured_picture))
			$info .= ' <a href="' . $featured_picture . '" target="_blank">featured</a>';
		if (!empty($post_picture))
			$info .= ' <a href="' . $post_picture . '" target="_blank">post</a>';
		if (!empty($avatar_picture))
			$info .= ' <a href="' . $avatar_picture . '" target="_blank">avatar</a>';
		if (!empty($link_id))
			$info .= ' <a href="' . $al2fb->Get_fb_permalink($link_id) . '" target="_blank">Facebook</a>';
		$info .= '</td></tr>';
	}

	// Last link pictures
	$posts = new WP_Query(array('meta_key' => c_al2fb_meta_link_picture, 'posts_per_page' => 5));
	while ($posts->have_posts()) {
		$posts->next_post();
		$link_picture = get_post_meta($posts->post->ID, c_al2fb_meta_link_picture, true);
		if (!empty($link_picture)) {
			$info .= '<tr><td>Link picture #' . $posts->post->ID . ':</td>';
			$info .= '<td><a href="' . get_permalink($posts->post->ID) . '" target="_blank">' . htmlspecialchars(get_the_title($posts->post->ID), ENT_QUOTES, $charset) . '</a>';
			$info .= ' ' . htmlspecialchars($link_picture, ENT_QUOTES, $charset);
			$info .= ' @ ' . $posts->post->post_date . '</td></tr>';
		}
	}

	// Last logs
	$posts = new WP_Query(array('meta_key' => c_al2fb_meta_log, 'posts_per_page' => 10));
	while ($posts->have_posts()) {
		$posts->next_post();
		$info .= '<tr><td>Log post:</td>';
		$info .= '<td><a href="' . get_permalink($posts->post->ID) . '" target="_blank">' . htmlspecialchars(get_the_title($posts->post->ID), ENT_QUOTES, $charset) . '</a></td></tr>';
		$logs = get_post_meta($posts->post->ID, c_al2fb_meta_log, false);
		if (!empty($logs))
			foreach ($logs as $log) {
				$info .= '<tr><td>Log:</td>';
				$info .= '<td>' . htmlspecialchars($log, ENT_QUOTES, $charset) . '</td></tr>';
			}
	}

	// Last errors
	$posts = new WP_Query(array('meta_key' => c_al2fb_meta_error, 'posts_per_page' => 10));
	while ($posts->have_posts()) {
		$posts->next_post();
		$error = get_post_meta($posts->post->ID, c_al2fb_meta_error, true);
		if (!empty($error)) {
			$info .= '<tr><td>Error:</td>';
			$info .= '<td>' . htmlspecialchars($error, ENT_QUOTES, $charset) . '</td></tr>';
			$info .= '<tr><td>Error time:</td>';
			$info .= '<td>' . htmlspecialchars(get_post_meta($posts->post->ID, c_al2fb_meta_error_time, true), ENT_QUOTES, $charset) . '</td></tr>';
			$info .= '<tr><td>Error post:</td>';
			$info .= '<td><a href="' . get_permalink($posts->post->ID) . '" target="_blank">' . htmlspecialchars(get_the_title($posts->post->ID), ENT_QUOTES, $charset) . '</a></td></tr>';
		}
	}

	$info .= '<tr><td>Last error:</td><td>' . htmlspecialchars(get_option(c_al2fb_last_error), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Last error time:</td><td>' . htmlspecialchars(get_option(c_al2fb_last_error_time), ENT_QUOTES, $charset) . '</td></tr>';
	$info .= '<tr><td>Last request:</td><td><pre>' . htmlspecialchars(get_option(c_al2fb_last_request), ENT_QUOTES, $charset) . '</pre></td></tr>';
	$info .= '<tr><td>Last request time:</td><td>' . get_option(c_al2fb_last_request_time) . '</td></tr>';
	$info .= '<tr><td>Last response:</td><td><pre>' . htmlspecialchars(get_option(c_al2fb_last_response), ENT_QUOTES, $charset) . '</pre></td></tr>';
	$info .= '<tr><td>Last response time:</td><td>' . get_option(c_al2fb_last_response_time) . '</td></tr>';
	$info .= '<tr><td>Last texts:</td><td><pre>' . htmlspecialchars(get_option(c_al2fb_last_texts), ENT_QUOTES, $charset) . '</pre></td></tr>';

	$info .= '<tr><td>Cron enabled:</td><td>' . (get_option(c_al2fb_option_cron_enabled) ? 'Yes' : 'No') . '</td></tr>';
	$info .= '<tr><td>Cron time:</td><td>' . get_option(c_al2fb_option_cron_time) . '</td></tr>';
	$info .= '<tr><td>Cron posts:</td><td>' . get_option(c_al2fb_option_cron_posts) . '</td></tr>';
	$info .= '<tr><td>Cron comments:</td><td>' . get_option(c_al2fb_option_cron_comments) . '</td></tr>';
	$info .= '<tr><td>Cron likes:</td><td>' . get_option(c_al2fb_option_cron_likes) . '</td></tr>';

	$info .= '</table></div>';

	$info .= '<pre>' . print_r($_SERVER, true) . '</pre>';

	$comments = get_comments('number=10');
	foreach ($comments as $comment) {
		$fb_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
		$comment->fb_comment_id = $fb_id;
	}
	$info .= '<pre>' . print_r($comments, true) . '</pre>';

	return $info;
}

?>
