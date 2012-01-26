<?php

/*
	Support class Add Link to Facebook admin
	Copyright (c) 2011, 2012 by Marcel Bokhorst
*/

function al2fb_render_admin($al2fb)
{
?>
	<div class="wrap">
	<h2><?php _e('Add Link to Facebook', c_al2fb_text_domain); ?></h2>
<?php
	// Get current user
	global $user_ID;
	get_currentuserinfo();

	// Test
	//print_r($al2fb->Subscribe_fb_page($user_ID));

	// Check for app share
	if (is_multisite())
		$shared_user_ID = get_site_option(c_al2fb_option_app_share);
	else
		$shared_user_ID = get_option(c_al2fb_option_app_share);
	if ($shared_user_ID && $shared_user_ID != $user_ID) {
		$userdata = get_userdata($shared_user_ID);
		echo '<div id="message" class="error fade al2fb_error"><p>';
		echo __('Only this user can access the settings:', c_al2fb_text_domain);
		echo ' ' . $userdata->user_login . ' (id=' . $shared_user_ID . ')</p></div>';
		echo '</div>';
		return;
	}

	// Get settings
	$charset = get_bloginfo('charset');
	$config_url = admin_url('tools.php?page=' . plugin_basename($al2fb->main_file));
	if (isset($_REQUEST['debug']))
		$config_url .= '&debug=1';
	if (isset($_REQUEST['tabs']))
		$config_url .= '&tabs=0';

	// Decode picture type
	$pic_type = get_user_meta($user_ID, c_al2fb_meta_picture_type, true);
	$pic_wordpress = ($pic_type == 'wordpress' ? ' checked' : '');
	$pic_media = ($pic_type == 'media' ? ' checked' : '');
	$pic_featured = ($pic_type == 'featured' ? ' checked' : '');
	$pic_facebook = ($pic_type == 'facebook' ? ' checked' : '');
	$pic_post = ($pic_type == 'post' ? ' checked' : '');
	$pic_avatar = ($pic_type == 'avatar' ? ' checked' : '');
	$pic_userphoto = ($pic_type == 'userphoto' ? ' checked' : '');
	$pic_custom = ($pic_type == 'custom' ? ' checked' : '');

	if (!current_theme_supports('post-thumbnails') ||
		!function_exists('get_post_thumbnail_id') ||
		!function_exists('wp_get_attachment_image_src'))
		$pic_featured .= ' disabled';

	if (!in_array('user-photo/user-photo.php', get_option('active_plugins')))
		$pic_userphoto .= ' disabled';

	// Like button
	$like_layout = get_user_meta($user_ID, c_al2fb_meta_like_layout, true);
	$like_layout_standard = ($like_layout == 'standard' ? ' checked' : '');
	$like_layout_button = ($like_layout == 'button_count' ? ' checked' : '');
	$like_layout_box = ($like_layout == 'box_count' ? ' checked' : '');
	$like_action = get_user_meta($user_ID, c_al2fb_meta_like_action, true);
	$like_action_like = ($like_action == 'like' ? ' checked' : '');
	$like_action_recommend = ($like_action == 'recommend' ? ' checked' : '');
	$like_font = get_user_meta($user_ID, c_al2fb_meta_like_font, true);
	$like_color = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
	$like_color_light = ($like_color == 'light' ? ' checked' : '');
	$like_color_dark = ($like_color == 'dark' ? ' checked' : '');

	// Comment link option
	$comments_nolink = get_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, true);
	if (empty($comments_nolink))
		$comments_nolink = 'author';
	else if ($comments_nolink == 'on')
		$comments_nolink = 'none';
	$comments_nolink_none = ($comments_nolink == 'none' ? ' checked' : '');
	$comments_nolink_author = ($comments_nolink == 'author' ? ' checked' : '');
	$comments_nolink_link = ($comments_nolink == 'link' ? ' checked' : '');

	// Face pile
	$pile_size = get_user_meta($user_ID, c_al2fb_meta_pile_size, true);

	// Check connectivity
	if (!ini_get('allow_url_fopen') && !function_exists('curl_init'))
		echo '<div id="message" class="error fade al2fb_error"><p>' . __('Your server may not allow external connections', c_al2fb_text_domain) . '</p></div>';

	al2fb_render_debug_info($al2fb);
	al2fb_render_resources($al2fb);
?>
	<div class="al2fb_options">

	<div class="al2fb_instructions" style="width: 550px;">
	<strong><?php _e('Please be aware that comment integration or showing Facebook messages in the widget could harm the privacy of other Facebook users!', c_al2fb_text_domain); ?></strong>
	</div>

<?php		if (get_user_meta($user_ID, c_al2fb_meta_client_id, true) &&
		get_user_meta($user_ID, c_al2fb_meta_app_secret, true)) {
?>
		<hr />
		<a name="authorize"></a>
		<h3><?php _e('Authorization', c_al2fb_text_domain); ?></h3>

		<div id="al2fb_auth">
<?php
		if ($al2fb->Is_authorized($user_ID)) {
			echo '<span>' . __('Plugin is authorized', c_al2fb_text_domain) . '</span><br />';
			// Get page name
			try {
				$me = $al2fb->Get_fb_me($user_ID, false);
				if ($me != null) {
					_e('Links will be added to', c_al2fb_text_domain);
					echo ' <a href="' . $me->link . '" target="_blank">' . htmlspecialchars($me->name, ENT_QUOTES, $charset);
					if (!empty($me->category))
						echo ' - ' . htmlspecialchars($me->category, ENT_QUOTES, $charset);
					echo '</a>';
				}
			}
			catch (Exception $e) {
				echo '<div id="message" class="error fade al2fb_error"><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, $charset) . '</p></div>';
			}
		}
?>
		<table><tr>
		<td>
			<form method="get" action="<?php echo admin_url('tools.php?page=' . plugin_basename($al2fb->main_file)); ?>">
			<input type="hidden" name="al2fb_action" value="init">
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Authorize', c_al2fb_text_domain) ?>" />
			</p>
			</form>
		</td>

<?php			if (!get_user_meta($user_ID, c_al2fb_meta_donated, true)) { ?>
			<td>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				</form>
			</td>
			<td>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=marcel%40bokhorst%2ebiz&lc=US&item_name=Add%20Link%20to%20Facebook%20WordPress%20plugin&item_number=Marcel%20Bokhorst&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted"><?php _e('Donate in EUR', c_al2fb_text_domain) ?></a>
			</td>
			<td>
				<a href="http://flattr.com/thing/315162/Add-Link-to-Facebook-WordPress-plugin" target="_blank">
				<img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
			</td>
<?php			} ?>

		</tr></table>

		</div>
		<a href="#" id="al2fb_auth_show"><?php _e('Show', c_al2fb_text_domain) ?></a>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				if (<?php echo $al2fb->Is_authorized($user_ID) && get_user_meta($user_ID, c_al2fb_meta_donated, true) ? 'true' : 'false'; ?>)
					$('#al2fb_auth').hide();
				else
					$('#al2fb_auth_show').hide();

				$('#al2fb_auth_show').click(function() {
					$('#al2fb_auth').show();
					$('#al2fb_auth_show').hide();
					return false;
				});
			});
		</script>
<?php		} ?>

	<hr />
	<a name="configure"></a>
	<h3><?php _e('Easy setup', c_al2fb_text_domain); ?></h3>

	<form method="post" action="<?php echo $config_url; ?>">
	<input type="hidden" name="al2fb_action" value="config">
	<?php wp_nonce_field(c_al2fb_nonce_form); ?>

	<div id="al2fb_config">

	<div class="al2fb_instructions">
	<h4><?php _e('To get an App ID and App Secret you have to create a Facebook application', c_al2fb_text_domain); ?></h4>
	<span><strong><?php _e('You have to fill in the following:', c_al2fb_text_domain); ?></strong></span>
	<table>
		<tr>
			<td><span class="al2fb_label"><strong><?php _e('App Name:', c_al2fb_text_domain); ?></strong></span></td>
			<td><span class="al2fb_data"><?php _e('Anything you like, will appear as "via ..." below the message', c_al2fb_text_domain); ?></span></td>
		</tr>
		<tr>
			<td><span class="al2fb_label"><strong><?php _e('Website > Site URL:', c_al2fb_text_domain); ?></strong></span></td>
			<td><span class="al2fb_data" style="color: red;"><strong><?php echo htmlspecialchars($al2fb->Redirect_uri(), ENT_QUOTES, $charset); ?></strong></span></td>
		</tr>
	</table>
	<a href="http://developers.facebook.com/" target="_blank"><?php _e('Click here to create', c_al2fb_text_domain); ?></a>
	<span><?php _e('and navigate to \'<em>Apps</em>\' and then to \'<em>Create New App</em>\'', c_al2fb_text_domain); ?></span>
	</div>

	<table class="form-table al2fb_border">
	<tr valign="top"><th scope="row">
		<label for="al2fb_client_id"><strong><?php _e('App ID:', c_al2fb_text_domain); ?></strong></label>
	</th><td>
		<input id="al2fb_client_id" class="al2fb_text" name="<?php echo c_al2fb_meta_client_id; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_client_id, true); ?>" />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_app_secret"><strong><?php _e('App Secret:', c_al2fb_text_domain); ?></strong></label>
	</th><td>
		<input id="al2fb_app_secret" class="al2fb_text" name="<?php echo c_al2fb_meta_app_secret; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_app_secret, true); ?>" />
	</td></tr>

<?php		if (isset($_REQUEST['debug'])) { ?>
		<tr valign="top"><th scope="row">
			<label for="al2fb_access_token"><strong><?php _e('Access token:', c_al2fb_text_domain); ?></strong></label>
		</th><td>
			<input id="al2fb_access_token" class="al2fb_text" name="<?php echo c_al2fb_meta_access_token; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_access_token, true); ?>" />
		</td></tr>
<?php
	}

	if ($al2fb->Is_authorized($user_ID))
		try {
			$app = $al2fb->Get_fb_application($user_ID);
?>
			<tr valign="top"><th scope="row">
				<label for="al2fb_app_name"><?php _e('App Name:', c_al2fb_text_domain); ?></label>
			</th><td>
				<a id="al2fb_app_name" href="<?php echo $app->link; ?>" target="_blank"><?php echo htmlspecialchars($app->name, ENT_QUOTES, $charset); ?></a>
			</td></tr>
<?php
		}
		catch (Exception $e) {
			echo '<div id="message" class="error fade al2fb_error"><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, $charset) . '</p></div>';
		}

	if (current_user_can('manage_options')) {
?>
		<tr valign="top"><th scope="row">
			<label for="al2fb_app_share"><?php _e('Share with all users on this site:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_app_share" name="<?php echo c_al2fb_option_app_share; ?>" type="checkbox"<?php if (get_site_option(c_al2fb_option_app_share)) echo ' checked="checked"'; ?> />
		</td></tr>
<?php
	}
?>
	</table>

	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>

	</div>
	<a href="#" id="al2fb_config_show"><?php _e('Show', c_al2fb_text_domain) ?></a>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			if (<?php echo $al2fb->Is_authorized($user_ID) ? 'true' : 'false'; ?>)
				$('#al2fb_config').hide();
			else
				$('#al2fb_config_show').hide();

			$('#al2fb_config_show').click(function() {
				$('#al2fb_config').show();
				$('#al2fb_config_show').hide();
				return false;
			});
		});
	</script>

	<hr />
	<h3><?php _e('Additional settings', c_al2fb_text_domain); ?></h3>

	<ul class="al2fb_tabs" id="al2fb_tab_settings">
		<li><a href="#al2fb_tab_picture"><?php _e('Picture', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_page_group"><?php _e('Page/group', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_appearance"><?php _e('Appearance', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_comments"><?php _e('Comments', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_like_button"><?php _e('Like button', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_like_box"><?php _e('Like box', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_comments_plugin"><?php _e('Comments plugin', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_face_pile"><?php _e('Face pile', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_login"><?php _e('Login', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_activity_feed"><?php _e('Activity feed', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_common"><?php _e('Common', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_misc"><?php _e('Misc.', c_al2fb_text_domain); ?></a></li>
		<li><a href="#al2fb_tab_admin"><?php _e('Admin', c_al2fb_text_domain); ?></a></li>
	</ul>

	<div class="al2fb_tab_container">
	<div id="al2fb_tab_picture" class="al2fb_tab_content">
	<h4><?php _e('Link picture', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">
	<tr valign="top"><th scope="row">
		<label for="al2fb_picture_type"><?php _e('Link picture:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input type="radio" name="<?php echo c_al2fb_meta_picture_type; ?>" value="wordpress"<?php echo $pic_wordpress; ?>><?php _e('WordPress logo', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_picture_type; ?>" value="media"<?php echo $pic_media; ?>><?php _e('First attached image', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_picture_type; ?>" value="featured"<?php echo $pic_featured; ?>><?php _e('Featured post image', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_picture_type; ?>" value="facebook"<?php echo $pic_facebook; ?>><?php _e('Let Facebook select', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_picture_type; ?>" value="post"<?php echo $pic_post; ?>><?php _e('First image in the post', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_picture_type; ?>" value="avatar"<?php echo $pic_avatar; ?>><?php _e('Avatar of author', c_al2fb_text_domain); ?><br />
<?php 			if ($pic_type == 'userphoto' || $al2fb->debug) { ?>
			<input type="radio" name="<?php echo c_al2fb_meta_picture_type; ?>" value="userphoto"<?php echo $pic_userphoto; ?>><?php _e('Image from User Photo plugin', c_al2fb_text_domain); ?><br />
<?php			} ?>
		<input type="radio" name="<?php echo c_al2fb_meta_picture_type; ?>" value="custom"<?php echo $pic_custom; ?>><?php _e('Custom picture below', c_al2fb_text_domain); ?><br />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_picture"><?php _e('Custom picture URL:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_picture" class="al2fb_text" name="<?php echo c_al2fb_meta_picture; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_picture, true); ?>" />
		<br /><span class="al2fb_explanation"><?php _e('At least 50 x 50 pixels', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_picture_default"><?php _e('Default picture URL:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_picture_default" class="al2fb_text" name="<?php echo c_al2fb_meta_picture_default; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_picture_default, true); ?>" />
		<br /><span class="al2fb_explanation"><?php _e('Default WordPress logo', c_al2fb_text_domain); ?></span>
	</td></tr>
	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_page_group" class="al2fb_tab_content">
<?php
	if ($al2fb->Is_authorized($user_ID)) {
		try {
			if (!get_user_meta($user_ID, c_al2fb_meta_use_groups, true) ||
				!get_user_meta($user_ID, c_al2fb_meta_group, true)) {
				try {
					$me = $al2fb->Get_fb_me($user_ID, true);
				}
				catch (Exception $e) {
					$me = null;
				}
				$pages = $al2fb->Get_fb_pages($user_ID);
				$selected_page = get_user_meta($user_ID, c_al2fb_meta_page, true);
?>
				<div id="al2fb_pages">
				<h4><?php _e('Facebook page', c_al2fb_text_domain); ?></h4>
				<table class="form-table al2fb_border">
				<tr valign="top"><th scope="row">
					<label for="al2fb_page_owner"><?php _e('See all pages:', c_al2fb_text_domain); ?></label>
				</th><td>
					<input id="al2fb_page_owner" name="<?php echo c_al2fb_meta_page_owner; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_page_owner, true)) echo ' checked="checked"'; ?> />
					<br /><span class="al2fb_explanation"><?php _e('Requires manage pages permission', c_al2fb_text_domain); ?></span>
					<br /><span class="al2fb_explanation"><?php _e('Check this too if you want to add as page owner', c_al2fb_text_domain); ?></span>
				</td></tr>

				<tr valign="top"><th scope="row">
					<label for="al2fb_page"><?php _e('Add to page:', c_al2fb_text_domain); ?></label>
				</th><td>
					<select class="al2db_select" id="al2fb_page" name="<?php echo c_al2fb_meta_page; ?>">
<?php
					if ($me != null)
						echo '<option value=""' . ($selected_page ? '' : ' selected') . '>' . htmlspecialchars($me->name, ENT_QUOTES, $charset) . '</option>';
					if ($pages->data)
						foreach ($pages->data as $page) {
							echo '<option value="' . $page->id . '"';
							if ($page->id == $selected_page)
								echo ' selected';
							if (empty($page->name))
								$page->name = '?';
							echo '>' . htmlspecialchars($page->name, ENT_QUOTES, $charset) . ' - ' . htmlspecialchars($page->category, ENT_QUOTES, $charset) . '</option>';
						}
?>
					</select>
				</td></tr>
				</table>
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
				</p>
				</div>
<?php
				if (get_user_meta($user_ID, c_al2fb_meta_use_groups, true)) {
?>
					<script type="text/javascript">
						jQuery(document).ready(function($) {
							$('#al2fb_pages').hide();
						});
					</script>
<?php
				}
			}
?>

			<h4><?php _e('Facebook group', c_al2fb_text_domain); ?></h4>
			<table class="form-table al2fb_border">
			<tr valign="top"><th scope="row">
				<label for="al2fb_use_groups"><?php _e('Use groups:', c_al2fb_text_domain); ?></label>
			</th><td>
				<input id="al2fb_use_groups" name="<?php echo c_al2fb_meta_use_groups; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_use_groups, true)) echo ' checked="checked"'; ?> />
				<br /><span class="al2fb_explanation"><?php _e('Requires user groups permission', c_al2fb_text_domain); ?></span>
			</td></tr>

<?php		if (get_user_meta($user_ID, c_al2fb_meta_use_groups, true)) {
				$groups = $al2fb->Get_fb_groups($user_ID);
				$selected_group = get_user_meta($user_ID, c_al2fb_meta_group, true);
?>
				<tr valign="top"><th scope="row">
					<label for="al2fb_group"><?php _e('Add to group:', c_al2fb_text_domain); ?></label>
				</th><td>
					<select class="al2db_select" id="al2fb_group" name="<?php echo c_al2fb_meta_group; ?>">
<?php
					echo '<option value=""' . ($selected_group ? '' : ' selected') . '>' . __('None', c_al2fb_text_domain) . '</option>';
					if ($groups->data)
						foreach ($groups->data as $group) {
							echo '<option value="' . $group->id . '"';
							if ($group->id == $selected_group)
								echo ' selected';
							echo '>' . htmlspecialchars($group->name, ENT_QUOTES, $charset) . '</option>';
						}
?>
					</select>
				</td></tr>
<?php
			}
?>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
			</p>
<?php
		}
		catch (Exception $e) {
			echo '<div id="message" class="error fade al2fb_error"><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, $charset) . '</p></div>';
		}
	}
	else { ?>
		<h4><?php _e('First authorize the plugin', c_al2fb_text_domain); ?></h4>
<?php		} ?>
	</div>

	<div id="al2fb_tab_appearance" class="al2fb_tab_content">
	<h4><?php _e('Link appearance', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">
	<tr valign="top"><th scope="row">
		<label for="al2fb_caption"><?php _e('Use site title as caption:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_caption" name="<?php echo c_al2fb_meta_caption; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_caption, true)) echo ' checked="checked"'; ?> />
		<br /><span class="al2fb_explanation">&quot;<?php echo html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset')); ?>&quot;</span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_msg"><?php _e('Use excerpt as message:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_msg" name="<?php echo c_al2fb_meta_msg; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_msg, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_trailer"><?php _e('Text trailer:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_trailer" class="al2fb_text" name="<?php echo c_al2fb_meta_trailer; ?>" type="text" value="<?php  echo htmlentities(get_user_meta($user_ID, c_al2fb_meta_trailer, true), ENT_QUOTES, get_bloginfo('charset')); ?>" />
		<br /><span class="al2fb_explanation"><?php _e('For example "Read more ..."', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_hyperlink"><?php _e('Keep hyperlinks:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_hyperlink" name="<?php echo c_al2fb_meta_hyperlink; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_hyperlink, true)) echo ' checked="checked"'; ?> />
		<br /><span class="al2fb_explanation"><?php _e('The hyperlink title will be removed', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_share_link"><?php _e('Add \'Share\' link:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_share_link" name="<?php echo c_al2fb_meta_share_link; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_share_link, true)) echo ' checked="checked"'; ?> />
		<strong>Experimental!</strong>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_shortlink"><?php _e('Use short URL:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_shortlink" name="<?php echo c_al2fb_meta_shortlink; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_shortlink, true)) echo ' checked="checked"'; ?> />
		<br /><span class="al2fb_explanation"><?php _e('If available', c_al2fb_text_domain); ?></span>
	</td></tr>
	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_comments" class="al2fb_tab_content">
	<h4><?php _e('Facebook comments', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">
	<tr valign="top"><th scope="row">
		<label for="al2fb_fb_comments"><?php _e('Integrate comments from Facebook:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_fb_comments" name="<?php echo c_al2fb_meta_fb_comments; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_fb_comments_postback"><?php _e('Post WordPress comments back to Facebook:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_fb_comments_postback" name="<?php echo c_al2fb_meta_fb_comments_postback; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_fb_comments_postback, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_fb_comments_copy"><?php _e('Copy comments from Facebook to WordPress:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_fb_comments_copy" name="<?php echo c_al2fb_meta_fb_comments_copy; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_fb_comments_copy, true)) echo ' checked="checked"'; ?> />
		<br /><span class="al2fb_explanation"><?php _e('Enables for example editing of Facebook comments', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_fb_comments_nolink"><?php _e('Link Facebook comment to:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input type="radio" name="<?php echo c_al2fb_meta_fb_comments_nolink; ?>" value="none"<?php echo $comments_nolink_none; ?>><?php _e('None', c_al2fb_text_domain); ?><br />
		<span class="al2fb_explanation"><?php _e('Disables displaying of Facebook avatars too', c_al2fb_text_domain); ?></span><br />
		<input type="radio" name="<?php echo c_al2fb_meta_fb_comments_nolink; ?>" value="author"<?php echo $comments_nolink_author; ?>><?php _e('Profile author', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_fb_comments_nolink; ?>" value="link"<?php echo $comments_nolink_link; ?>><?php _e('Added link', c_al2fb_text_domain); ?><br />
		<span class="al2fb_explanation"><?php _e('Disables displaying of Facebook avatars too', c_al2fb_text_domain); ?></span><br />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_fb_likes"><?php _e('Integrate likes from Facebook:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_fb_likes" name="<?php echo c_al2fb_meta_fb_likes; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_fb_likes, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_post_likers"><?php _e('Show likers below the post text:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_post_likers" name="<?php echo c_al2fb_meta_post_likers; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_post_likers, true)) echo ' checked="checked"'; ?> />
	</td></tr>
	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_like_button" class="al2fb_tab_content">
	<h4><?php _e('Facebook like button', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">
	<tr valign="top"><th scope="row">
		<label for="al2fb_post_like_button"><?php _e('Show Facebook like button:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_post_like_button" name="<?php echo c_al2fb_meta_post_like_button; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_post_like_button, true)) echo ' checked="checked"'; ?> />
		<br /><a class="al2fb_explanation" href="http://developers.facebook.com/docs/reference/plugins/like/" target="_blank"><?php _e('Documentation', c_al2fb_text_domain); ?></a>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_layout"><?php _e('Layout:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input type="radio" name="<?php echo c_al2fb_meta_like_layout; ?>" value="standard"<?php echo $like_layout_standard; ?>><?php _e('Standard', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_like_layout; ?>" value="button_count"<?php echo $like_layout_button; ?>><?php _e('Button with count', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_like_layout; ?>" value="box_count"<?php echo $like_layout_box; ?>><?php _e('Box with count', c_al2fb_text_domain); ?><br />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_width"><?php _e('Width:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_like_width" name="<?php echo c_al2fb_meta_like_width; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_like_width, true); ?>" />
		<span><?php _e('Pixels', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_action"><?php _e('Action:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input type="radio" name="<?php echo c_al2fb_meta_like_action; ?>" value="like"<?php echo $like_action_like; ?>><?php _e('Like', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_like_action; ?>" value="recommend"<?php echo $like_action_recommend; ?>><?php _e('Recommend', c_al2fb_text_domain); ?><br />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_top"><?php _e('Show at the top of the post:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_like_top" name="<?php echo c_al2fb_meta_like_top; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_top, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_send"><?php _e('Show Facebook send button:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_like_send" name="<?php echo c_al2fb_meta_post_send_button; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_post_send_button, true)) echo ' checked="checked"'; ?> />
		<br /><a class="al2fb_explanation" href="http://developers.facebook.com/docs/reference/plugins/send/" target="_blank"><?php _e('Documentation', c_al2fb_text_domain); ?></a>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_combine"><?php _e('Combine Facebook like and send buttons:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_combine" name="<?php echo c_al2fb_meta_post_combine_buttons; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_post_combine_buttons, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_like_box" class="al2fb_tab_content">
	<h4><?php _e('Facebook like box', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_box_width"><?php _e('Width:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_like_box_width" name="<?php echo c_al2fb_meta_like_box_width; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_like_box_width, true); ?>" />
		<span><?php _e('Pixels', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_box_noheader"><?php _e('Disable like box header:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_box_noheader" name="<?php echo c_al2fb_meta_like_box_noheader; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_box_noheader, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_box_nostream"><?php _e('Disable like box stream:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_box_nostream" name="<?php echo c_al2fb_meta_like_box_nostream; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_box_nostream, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_comments_plugin" class="al2fb_tab_content">
	<h4><?php _e('Facebook comments plugin', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">

	<tr valign="top"><th scope="row">
		<label for="al2fb_comments_posts"><?php _e('Number of posts:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_comments_posts" name="<?php echo c_al2fb_meta_comments_posts; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_comments_posts, true); ?>" />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_comments_width"><?php _e('Width:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_comments_width" name="<?php echo c_al2fb_meta_comments_width; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_comments_width, true); ?>" />
		<span><?php _e('Pixels', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_comments_auto"><?php _e('Display automatically after post:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_comments_auto" name="<?php echo c_al2fb_meta_comments_auto; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_comments_auto, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_face_pile" class="al2fb_tab_content">
	<h4><?php _e('Facebook face pile', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">

	<tr valign="top"><th scope="row">
		<label for="al2fb_pile_size"><?php _e('Size:', c_al2fb_text_domain); ?></label>
	</th><td>
		<select class="al2db_select" id="al2fb_pile_size" name="<?php echo c_al2fb_meta_pile_size; ?>">
		<option value="small" <?php echo $pile_size == 'small' ? 'selected' : ''; ?>><?php _e('Small', c_al2fb_text_domain); ?></option>
		<option value="large" <?php echo $pile_size == 'large' ? 'selected' : ''; ?>><?php _e('Large', c_al2fb_text_domain); ?></option>
		</select>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_pile_width"><?php _e('Width:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_pile_width" name="<?php echo c_al2fb_meta_pile_width; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_pile_width, true); ?>" />
		<span><?php _e('Pixels', c_al2fb_text_domain); ?></span>
	</td></tr>

	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_login" class="al2fb_tab_content">
	<h4><?php _e('Facebook login', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">

	<tr valign="top"><th scope="row">
		<label for="al2fb_reg_width"><?php _e('Registration width:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_reg_width" name="<?php echo c_al2fb_meta_reg_width; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_reg_width, true); ?>" />
		<span><?php _e('Pixels', c_al2fb_text_domain); ?></span>
		<br /><a class="al2fb_explanation" href="http://developers.facebook.com/docs/plugins/registration/" target="_blank"><?php _e('Documentation', c_al2fb_text_domain); ?></a>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_login_width"><?php _e('Login width:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_login_width" name="<?php echo c_al2fb_meta_login_width; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_login_width, true); ?>" />
		<span><?php _e('Pixels', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_login_regurl"><?php _e('Login registration URL:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_text" id="al2fb_login_regurl" name="<?php echo c_al2fb_meta_login_regurl; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_login_regurl, true); ?>" />
		<br /><a class="al2fb_explanation" href="http://developers.facebook.com/docs/reference/plugins/login/" target="_blank"><?php _e('Documentation', c_al2fb_text_domain); ?></a>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_login_redir"><?php _e('Login redirect URL:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_text" id="al2fb_login_redir" name="<?php echo c_al2fb_meta_login_redir; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_login_redir, true); ?>" />
	</td></tr>

	<tr valign="top"><th scope="row" colspan="2">
		<label for="al2fb_login_html"><?php _e('Text or HTML when logged in:', c_al2fb_text_domain); ?></label>
		<br />
		<textarea id="al2fb_login_html" name="<?php echo c_al2fb_meta_login_html; ?>" cols="75" rows="10"><?php echo get_user_meta($user_ID, c_al2fb_meta_login_html, true); ?></textarea>
	</th><td>
	</td></tr>

	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_activity_feed" class="al2fb_tab_content">
	<h4><?php _e('Facebook activity feed', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">

	<tr valign="top"><th scope="row">
		<label for="al2fb_act_width"><?php _e('Width:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_act_width" name="<?php echo c_al2fb_meta_act_width; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_act_width, true); ?>" />
		<span><?php _e('Pixels', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_act_height"><?php _e('Height:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_act_height" name="<?php echo c_al2fb_meta_act_height; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_act_height, true); ?>" />
		<span><?php _e('Pixels', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_act_header"><?php _e('Show header:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_act_header" name="<?php echo c_al2fb_meta_act_header; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_act_header, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_act_recommend"><?php _e('Show recommendations:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_act_recommend" name="<?php echo c_al2fb_meta_act_recommend; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_act_recommend, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_common" class="al2fb_tab_content">
	<h4><?php _e('Facebook common', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">

	<tr valign="top"><th scope="row">
		<label for="al2fb_post_nohome"><?php _e('Do not show on the home page:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_post_nohome" name="<?php echo c_al2fb_meta_like_nohome; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_nohome, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_post_noposts"><?php _e('Do not show on posts:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_post_noposts" name="<?php echo c_al2fb_meta_like_noposts; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_noposts, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_post_nopages"><?php _e('Do not show on pages:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_post_nopages" name="<?php echo c_al2fb_meta_like_nopages; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_nopages, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_post_noarchives"><?php _e('Do not show in archives:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_post_noarchives" name="<?php echo c_al2fb_meta_like_noarchives; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_noarchives, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_post_nocategories"><?php _e('Do not show in categories:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_post_nocategories" name="<?php echo c_al2fb_meta_like_nocategories; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_nocategories, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_faces"><?php _e('Faces:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_like_faces" name="<?php echo c_al2fb_meta_like_faces; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_like_faces, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_pile_rows"><?php _e('Maximum count of rows:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input class="al2fb_numeric" id="al2fb_pile_rows" name="<?php echo c_al2fb_meta_pile_rows; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_pile_rows, true); ?>" />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_font"><?php _e('Font:', c_al2fb_text_domain); ?></label>
	</th><td>
		<select class="al2db_select" id="al2fb_like_font" name="<?php echo c_al2fb_meta_like_font; ?>">
		<option value="" <?php echo empty($like_font) ? 'selected' : ''; ?>></option>
		<option value="arial" <?php echo $like_font == 'arial' ? 'selected' : ''; ?>>arial</option>
		<option value="lucida grande" <?php echo $like_font == 'lucida grande' ? 'selected' : ''; ?>>lucida grande</option>
		<option value="segoe ui" <?php echo $like_font == 'segoe ui' ? 'selected' : ''; ?>>segoe ui</option>
		<option value="tahoma" <?php echo $like_font == 'tahoma' ? 'selected' : ''; ?>>tahoma</option>
		<option value="trebuchet ms" <?php echo $like_font == 'trebuchet ms' ? 'selected' : ''; ?>>trebuchet ms</option>
		<option value="verdana" <?php echo $like_font == 'verdana' ? 'selected' : ''; ?>>verdana</option>
		</select>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_color"><?php _e('Color scheme:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input type="radio" name="<?php echo c_al2fb_meta_like_colorscheme; ?>" value="light"<?php echo $like_color_light; ?>><?php _e('Light', c_al2fb_text_domain); ?><br />
		<input type="radio" name="<?php echo c_al2fb_meta_like_colorscheme; ?>" value="dark"<?php echo $like_color_dark; ?>><?php _e('Dark', c_al2fb_text_domain); ?><br />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_box_border"><?php _e('Border color:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_like_box_border" name="<?php echo c_al2fb_meta_like_box_border; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_like_box_border, true); ?>" />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_like_link"><?php _e('Link to:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_like_link" class="al2fb_text" name="<?php echo c_al2fb_meta_like_link; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_like_link, true); ?>" />
		<br /><span class="al2fb_explanation"><?php _e('Default the post or page', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_open_graph"><?php _e('Use Open Graph protocol:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_open_graph" name="<?php echo c_al2fb_meta_open_graph; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_open_graph, true)) echo ' checked="checked"'; ?> />
		<br /><a class="al2fb_explanation" href="http://developers.facebook.com/docs/opengraph/" target="_blank"><?php _e('Documentation', c_al2fb_text_domain); ?></a>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_open_graph_type"><?php _e('Open Graph protocol <em>og:type</em>:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_open_graph_type" class="al2fb_text" name="<?php echo c_al2fb_meta_open_graph_type; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_open_graph_type, true); ?>" />
		<br /><span class="al2fb_explanation"><?php _e('Default \'article\'', c_al2fb_text_domain); ?></span>
		<a class="al2fb_explanation" href="http://developers.facebook.com/docs/opengraph/#types" target="_blank"><?php _e('Documentation', c_al2fb_text_domain); ?></a>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_open_graph_admin"><?php _e('Facebook administrators:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_open_graph_admin" class="al2fb_text" name="<?php echo c_al2fb_meta_open_graph_admins; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_open_graph_admins, true); ?>" />
		<br /><span class="al2fb_explanation"><?php _e('Separate multiple administrators by a comma without spaces', c_al2fb_text_domain); ?></span>
	</td></tr>

	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_misc" class="al2fb_tab_content">
	<a name="misc"></a>
	<h4><?php _e('Miscelaneous settings', c_al2fb_text_domain); ?></h4>
	<table class="form-table al2fb_border">
	<tr valign="top"><th scope="row">
		<label for="al2fb_exclude_default"><?php _e('Do not add link by default:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_exclude_default" name="<?php echo c_al2fb_meta_exclude_default; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_exclude_default, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_not_post_list"><?php _e('Don\'t show a summary in the post list:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_not_post_list" name="<?php echo c_al2fb_meta_not_post_list; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_not_post_list, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_add_new_page"><?php _e('Add links for new pages:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_add_new_page" name="<?php echo c_al2fb_meta_add_new_page; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_add_new_page, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_fb_encoding"><?php _e('Facebook character encoding:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_fb_encoding" class="al2fb_text" name="<?php echo c_al2fb_meta_fb_encoding; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_fb_encoding, true); ?>" />
		<br /><span class="al2fb_explanation"><?php _e('Default UTF-8; do not change if no need', c_al2fb_text_domain); ?></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_fb_locale"><?php _e('Facebook locale:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_fb_locale" class="al2fb_text" name="<?php echo c_al2fb_meta_fb_locale; ?>" type="text" value="<?php echo get_user_meta($user_ID, c_al2fb_meta_fb_locale, true); ?>" />
		<br /><span class="al2fb_explanation"><?php _e('Do not change if no need', c_al2fb_text_domain); ?><span>&nbsp;(<?php echo WPLANG; ?>)</span></span>
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_donated"><?php _e('I have donated to this plugin:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_donated" name="<?php echo c_al2fb_meta_donated; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_donated, true)) echo ' checked="checked"'; ?> />
	</td></tr>

	<tr valign="top"><th scope="row">
		<label for="al2fb_rated"><?php _e('I have rated this plugin:', c_al2fb_text_domain); ?></label>
	</th><td>
		<input id="al2fb_rated" name="<?php echo c_al2fb_meta_rated; ?>" type="checkbox"<?php if (get_user_meta($user_ID, c_al2fb_meta_rated, true)) echo ' checked="checked"'; ?> />
	</td></tr>
	</table>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
	</p>
	</div>

	<div id="al2fb_tab_admin" class="al2fb_tab_content">
<?php		if (current_user_can('manage_options')) { ?>
		<h4><?php _e('Administrator options', c_al2fb_text_domain); ?></h4>
		<table class="form-table al2fb_border">
		<tr valign="top"><th scope="row">
			<label for="al2fb_timeout"><?php _e('Facebook communication timeout:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_numeric" id="al2fb_timeout" name="<?php echo c_al2fb_option_timeout; ?>" type="text" value="<?php echo get_option(c_al2fb_option_timeout); ?>" />
			<span><?php _e('Seconds', c_al2fb_text_domain); ?></span>
			<br /><span class="al2fb_explanation"><?php _e('Default 15 seconds', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_nonotice"><?php _e('Do not display notices:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_nonotice" name="<?php echo c_al2fb_option_nonotice; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_nonotice)) echo ' checked="checked"'; ?> />
			<br /><span class="al2fb_explanation"><?php _e('Except on this page', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_min_cap"><?php _e('Required capability to use plugin:', c_al2fb_text_domain); ?></label>
		</th><td>
			<select class="al2db_select" id="al2fb_min_cap" name="<?php echo c_al2fb_option_min_cap; ?>">
<?php
			// Get list of capabilities
			global $wp_roles;
			$capabilities = array();
			foreach ($wp_roles->role_objects as $key => $role)
				if (is_array($role->capabilities))
					foreach ($role->capabilities as $cap => $grant)
						$capabilities[$cap] = $cap;
			sort($capabilities);

			// List capabilities and select current
			$min_cap = get_option(c_al2fb_option_min_cap);
			foreach ($capabilities as $cap) {
				echo '<option value="' . $cap . '"';
				if ($cap == $min_cap)
					echo ' selected';
				echo '>' . $cap . '</option>';
			}
?>
			</select>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_min_cap_comment"><?php _e('Required capability to view Facebook comments:', c_al2fb_text_domain); ?></label>
		</th><td>
			<select class="al2db_select" id="al2fb_min_cap_comment" name="<?php echo c_al2fb_option_min_cap_comment; ?>">
<?php
			// List capabilities and select current
			$min_cap = get_option(c_al2fb_option_min_cap_comment);
			echo '<option value=""';
			if (empty($min_cap))
				echo ' selected';
			echo '>' . __('None', c_al2fb_text_domain) . '</option>';
			foreach ($capabilities as $cap) {
				echo '<option value="' . $cap . '"';
				if ($cap == $min_cap)
					echo ' selected';
				echo '>' . $cap . '</option>';
			}
?>
			</select>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_cache"><?php _e('Refresh Facebook comments every:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_numeric" id="al2fb_cache" name="<?php echo c_al2fb_option_msg_refresh; ?>" type="text" value="<?php echo get_option(c_al2fb_option_msg_refresh); ?>" />
			<span><?php _e('Minutes', c_al2fb_text_domain); ?></span>
			<br /><span class="al2fb_explanation"><?php _e('Default every 10 minutes', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_maxage"><?php _e('Refresh Facebook comments for:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_numeric" id="al2fb_maxage" name="<?php echo c_al2fb_option_msg_maxage; ?>" type="text" value="<?php echo get_option(c_al2fb_option_msg_maxage); ?>" />
			<span><?php _e('Days', c_al2fb_text_domain); ?></span>
			<br /><span class="al2fb_explanation"><?php _e('Default 7 days', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_cron"><?php _e('Refresh Facebook comments in the background:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_cron" name="<?php echo c_al2fb_option_cron_enabled; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_cron_enabled)) echo ' checked="checked"'; ?> />
			<br /><span class="al2fb_explanation"><?php _e('Using Wordress cron', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_max_descr"><?php _e('Maximum text length with trailer:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_numeric" id="al2fb_max_descr" name="<?php echo c_al2fb_option_max_descr; ?>" type="text" value="<?php echo get_option(c_al2fb_option_max_descr); ?>" />
			<span><?php _e('Characters', c_al2fb_text_domain); ?></span>
			<br /><span class="al2fb_explanation"><?php _e('Default 256 characters', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_max_text"><?php _e('Maximum Facebook text length:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_numeric" id="al2fb_max_text" name="<?php echo c_al2fb_option_max_text; ?>" type="text" value="<?php echo get_option(c_al2fb_option_max_text); ?>" />
			<span><?php _e('Characters', c_al2fb_text_domain); ?></span>
			<br /><span class="al2fb_explanation"><?php _e('Default 10,000 characters', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_exclude_type"><?php _e('Exclude these custom post types:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_text" id="al2fb_exclude_type" name="<?php echo c_al2fb_option_exclude_type; ?>" type="text" value="<?php echo get_option(c_al2fb_option_exclude_type); ?>" />
			<br /><span class="al2fb_explanation"><?php _e('Separate by commas', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_exclude_cat"><?php _e('Exclude these categories:', c_al2fb_text_domain); ?></label>
			<br /><span class="al2fb_explanation"><?php _e('Use category ID\'s', c_al2fb_text_domain); ?></span>
		</th><td>
			<input class="al2fb_text" id="al2fb_exclude_cat" name="<?php echo c_al2fb_option_exclude_cat; ?>" type="text" value="<?php echo get_option(c_al2fb_option_exclude_cat); ?>" />
			<br /><span class="al2fb_explanation"><?php _e('Separate by commas', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_exclude_tag"><?php _e('Exclude these tags:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_text" id="al2fb_exclude_tag" name="<?php echo c_al2fb_option_exclude_tag; ?>" type="text" value="<?php echo get_option(c_al2fb_option_exclude_tag); ?>" />
			<br /><span class="al2fb_explanation"><?php _e('Separate by commas', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_exclude_author"><?php _e('Exclude these authors:', c_al2fb_text_domain); ?></label>
			<br /><span class="al2fb_explanation"><?php _e('Use login names', c_al2fb_text_domain); ?></span>
		</th><td>
			<input class="al2fb_text" id="al2fb_exclude_author" name="<?php echo c_al2fb_option_exclude_author; ?>" type="text" value="<?php echo get_option(c_al2fb_option_exclude_author); ?>" />
			<br /><span class="al2fb_explanation"><?php _e('Separate by commas', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_metabox_type"><?php _e('Add meta box for these custom post types:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_text" id="al2fb_metabox_type" name="<?php echo c_al2fb_option_metabox_type; ?>" type="text" value="<?php echo get_option(c_al2fb_option_metabox_type); ?>" />
			<br /><span class="al2fb_explanation"><?php _e('Separate by commas', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_noverifypeer"><?php _e('Do not verify the peer\'s certificate:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_noverifypeer" name="<?php echo c_al2fb_option_noverifypeer; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_noverifypeer)) echo ' checked="checked"'; ?> />
			<br /><span class="al2fb_explanation"><?php _e('Try this in case of cURL error 60', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_shortcode"><?php _e('Execute shortcodes in widgets:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_shortcode" name="<?php echo c_al2fb_option_shortcode_widget; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_shortcode_widget)) echo ' checked="checked"'; ?> />
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_noshortcode"><?php _e('Do not execute shortcodes for texts:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_noshortcode" name="<?php echo c_al2fb_option_noshortcode; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_noshortcode)) echo ' checked="checked"'; ?> />
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_nofilter"><?php _e('Do not execute filters for texts:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_nofilter" name="<?php echo c_al2fb_option_nofilter; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_nofilter)) echo ' checked="checked"'; ?> />
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_nofilter"><?php _e('Do not execute filters for comments:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_nofilter" name="<?php echo c_al2fb_option_nofilter_comments; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_nofilter_comments)) echo ' checked="checked"'; ?> />
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_use_ssp"><a href="http://yro.slashdot.org/story/11/09/03/0115241/Heises-Two-Clicks-For-More-Privacy-vs-Facebook"><?php _e('Use Heise social share privacy:', c_al2fb_text_domain); ?></a></label>
		</th><td>
			<input id="al2fb_use ssp" name="<?php echo c_al2fb_option_use_ssp; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_use_ssp)) echo ' checked="checked"'; ?> />
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_ssp_info"><?php _e('Heise privacy policy URL:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_text" id="al2fb_ssp_info" name="<?php echo c_al2fb_option_ssp_info; ?>" type="text" value="<?php echo get_option(c_al2fb_option_ssp_info); ?>" />
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_filter_prio"><?php _e('Priority filter \'the_content\':', c_al2fb_text_domain); ?></label>
		</th><td>
			<input class="al2fb_text" id="al2fb_filter_prio" name="<?php echo c_al2fb_option_filter_prio; ?>" type="text" value="<?php echo get_option(c_al2fb_option_filter_prio); ?>" />
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_noscript"><?php _e('Do not include Facebook script:', c_al2fb_text_domain); ?></label>
		</th><td>
			<input id="al2fb_noscript" name="<?php echo c_al2fb_option_noscript; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_noscript)) echo ' checked="checked"'; ?> />
			<br /><span class="al2fb_explanation"><?php _e('In case of conflicts with other Facebook plugins', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_clean"><?php _e('Clean on deactivate:', c_al2fb_text_domain); ?></label>
			<br />
			<span class="al2fb_explanation"><strong><?php _e('Upgrade deactivates the plugin!', c_al2fb_text_domain); ?></strong></span>
		</th><td>
			<input id="al2fb_clean" name="<?php echo c_al2fb_option_clean; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_clean)) echo ' checked="checked"'; ?> />
			<br /><span class="al2fb_explanation"><?php _e('All data, except link id\'s', c_al2fb_text_domain); ?></span>
		</td></tr>

		<tr valign="top"><th scope="row" colspan="2">
			<label for="al2fb_css"><?php _e('Additional styling rules (CSS):', c_al2fb_text_domain); ?></label>
			<br />
			<textarea id="al2fb_css" name="<?php echo c_al2fb_option_css; ?>" cols="75" rows="10"><?php echo get_option(c_al2fb_option_css); ?></textarea>
		</th><td>
		</td></tr>
		</table>

<?php			if (isset($_REQUEST['debug'])) { ?>
			<h4><?php _e('Debug options', c_al2fb_text_domain); ?></h4>
			<table class="form-table al2fb_border">
			<tr valign="top"><th scope="row">
				<label for="al2fb_siteurl"><?php _e('Use site URL as request URI:', c_al2fb_text_domain); ?></label>
			</th><td>
				<input id="al2fb_siteurl" name="<?php echo c_al2fb_option_siteurl; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_siteurl)) echo ' checked="checked"'; ?> />
			</td></tr>

			<tr valign="top"><th scope="row">
				<label for="al2fb_nocurl"><?php _e('Do not use cURL:', c_al2fb_text_domain); ?></label>
			</th><td>
				<input id="al2fb_nocurl" name="<?php echo c_al2fb_option_nocurl; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_nocurl)) echo ' checked="checked"'; ?> />
			</td></tr>

			<tr valign="top"><th scope="row">
				<label for="al2fb_use_pp"><?php _e('Use publish_post action:', c_al2fb_text_domain); ?></label>
			</th><td>
				<input id="al2fb_use_pp" name="<?php echo c_al2fb_option_use_pp; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_use_pp)) echo ' checked="checked"'; ?> />
			</td></tr>

			<tr valign="top"><th scope="row">
				<label for="al2fb_debug"><?php _e('Debug:', c_al2fb_text_domain); ?></label>
			</th><td>
				<input id="al2fb_debug" name="<?php echo c_al2fb_option_debug; ?>" type="checkbox"<?php if (get_option(c_al2fb_option_debug)) echo ' checked="checked"'; ?> />
			</td></tr>
			</table>
<?php			} ?>
		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save', c_al2fb_text_domain) ?>" />
		</p>
<?php
	}
	else { ?>
		<h4><?php _e('Only for administrators', c_al2fb_text_domain); ?></h4>
<?php		} ?>
	</div>

	</div>
	<a href="<?php echo admin_url('tools.php?page=' . plugin_basename($al2fb->main_file)) . '&tabs=0'; ?>"><?php _e('No tab pages', c_al2fb_text_domain); ?></a>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			if (window.location.search.substr(window.location.search.length - 6) == 'tabs=0') {
				$('#al2fb_tab_settings').hide();
				$('.al2fb_tab_container').removeClass('al2fb_tab_container');
				$('.al2fb_tab_content').removeClass('al2fb_tab_content');
			}
			else {
				$('.al2fb_tab_content').hide();
				if (window.location.search.substr(window.location.search.length - 4) == 'rate') {
					$('ul.al2fb_tabs li:has(a[href=#al2fb_tab_misc])').addClass('active').show();
					$('#al2fb_tab_misc').show();
					$('html, body').animate({scrollTop: $('#al2fb_tab_settings').offset().top}, 2000);
				}
				else {
					$('ul.al2fb_tabs li:first').addClass('active').show();
					$('.al2fb_tab_content:first').show();
				}

				$('ul.al2fb_tabs li').click(function() {
					$('ul.al2fb_tabs li').removeClass('active');
					$(this).addClass('active');
					$('.al2fb_tab_content').hide();
					var activeTab = $(this).find('a').attr('href');
					$(activeTab).show();
					return false;
				});
			}
		});
	</script>
	</form>
	</div>
	</div>
<?php
}

function al2fb_render_resources($al2fb) {
	global $user_ID;
	get_currentuserinfo();
?>
	<div class="al2fb_resources">
	<h3><?php _e('Resources', c_al2fb_text_domain); ?></h3>
	<ul>
	<li><a href="http://wordpress.org/extend/plugins/add-link-to-facebook/other_notes/" target="_blank"><?php _e('Setup guide & user manual', c_al2fb_text_domain); ?></a></li>
	<li><a href="http://wordpress.org/extend/plugins/add-link-to-facebook/faq/" target="_blank"><?php _e('Frequently asked questions', c_al2fb_text_domain); ?></a></li>
	<li><a href="http://forum.bokhorst.biz/add-link-to-facebook/" target="_blank"><?php _e('Support page', c_al2fb_text_domain); ?></a></li>
	<li><a href="<?php echo 'tools.php?page=' . plugin_basename($al2fb->main_file) . '&debug=1'; ?>"><?php _e('Debug information', c_al2fb_text_domain); ?></a></li>
	<li><a href="http://blog.bokhorst.biz/about/" target="_blank"><?php _e('About the author', c_al2fb_text_domain); ?></a></li>
	<li><a href="http://wordpress.org/extend/plugins/profile/m66b" target="_blank"><?php _e('Other plugins', c_al2fb_text_domain); ?></a></li>
	</ul>
<?php		if (!get_user_meta($user_ID, c_al2fb_meta_donated, true)) { ?>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHZwYJKoZIhvcNAQcEoIIHWDCCB1QCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYApWh+oUn2CtY+7zwU5zu5XKj096Mj0sxBhri5/lYV7i7B+JwhAC1ta7kkj2tXAbR3kcjVyNA9n5kKBUND+5Lu7HiNlnn53eFpl3wtPBBvPZjPricLI144ZRNdaaAVtY32pWX7tzyWJaHgClKWp5uHaerSZ70MqUK8yqzt0V2KKDjELMAkGBSsOAwIaBQAwgeQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIn3eeOKy6QZGAgcDKPGjy/6+i9RXscvkaHQqjbFI1bE36XYcrttae+aXmkeicJpsm+Se3NCBtY9yt6nxwwmxhqNTDNRwL98t8EXNkLg6XxvuOql0UnWlfEvRo+/66fqImq2jsro31xtNKyqJ1Qhx+vsf552j3xmdqdbg1C9IHNYQ7yfc6Bhx914ur8UPKYjy66KIuZBCXWge8PeYjuiswpOToRN8BU6tV4OW1ndrUO9EKZd5UHW/AOX0mjXc2HFwRoD22nrapVFIsjt2gggOHMIIDgzCCAuygAwIBAgIBADANBgkqhkiG9w0BAQUFADCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wHhcNMDQwMjEzMTAxMzE1WhcNMzUwMjEzMTAxMzE1WjCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAMFHTt38RMxLXJyO2SmS+Ndl72T7oKJ4u4uw+6awntALWh03PewmIJuzbALScsTS4sZoS1fKciBGoh11gIfHzylvkdNe/hJl66/RGqrj5rFb08sAABNTzDTiqqNpJeBsYs/c2aiGozptX2RlnBktH+SUNpAajW724Nv2Wvhif6sFAgMBAAGjge4wgeswHQYDVR0OBBYEFJaffLvGbxe9WT9S1wob7BDWZJRrMIG7BgNVHSMEgbMwgbCAFJaffLvGbxe9WT9S1wob7BDWZJRroYGUpIGRMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbYIBADAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAIFfOlaagFrl71+jq6OKidbWFSE+Q4FqROvdgIONth+8kSK//Y/4ihuE4Ymvzn5ceE3S/iBSQQMjyvb+s2TWbQYDwcp129OPIbD9epdr4tJOUNiSojw7BHwYRiPh58S1xGlFgHFXwrEBb3dgNbMUa+u4qectsMAXpVHnD9wIyfmHMYIBmjCCAZYCAQEwgZQwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tAgEAMAkGBSsOAwIaBQCgXTAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJBTEPFw0xMTAyMDcwOTQ4MTlaMCMGCSqGSIb3DQEJBDEWBBQOOy+JroeRlZL7jGU/azSibWz1fjANBgkqhkiG9w0BAQEFAASBgCUXDO9KLIuy/XJwBa6kMWi0U1KFarbN9568i14mmZCFDvBmexRKhnSfqx+QLzdpNENBHKON8vNKanmL9jxgtyc88WAtrP/LqN4tmSrr0VB5wrds/viLxWZfu4Spb+YOTpo+z2hjXCJzVSV3EDvoxzHEN1Haxrvr1gWNhWzvVN3q-----END PKCS7-----">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		</form>
		<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=marcel%40bokhorst%2ebiz&lc=US&item_name=Add%20Link%20to%20Facebook%20WordPress%20plugin&item_number=Marcel%20Bokhorst&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted"><?php _e('Donate in EUR', c_al2fb_text_domain) ?></a>
		<br />
		<br />
		<a href="http://flattr.com/thing/315162/Add-Link-to-Facebook-WordPress-plugin" target="_blank">
		<img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
<?php		} ?>
	</div>
<?php
}

function al2fb_render_debug_info($al2fb) {
	// Debug information
	if (isset($_REQUEST['debug'])) {
		global $user_identity, $user_email;
		get_currentuserinfo();
?>
		<hr />
		<h3><?php _e('Debug information', c_al2fb_text_domain) ?></h3>
		<form method="post" action="">
		<input type="hidden" name="al2fb_action" value="mail">
		<?php wp_nonce_field(c_al2fb_nonce_form); ?>

		<table class="form-table">
		<tr valign="top"><th scope="row">
			<label for="al2fb_debug_name"><strong><?php _e('Name:', c_al2fb_text_domain); ?></strong></label>
		</th><td>
			<input id="al2fb_debug_name" class="" name="<?php echo c_al2fb_mail_name; ?>" type="text" value="<?php echo $user_identity; ?>" />
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_debug_email"><strong><?php _e('E-mail:', c_al2fb_text_domain); ?></strong></label>
		</th><td>
			<input id="al2fb_debug_email" class="" name="<?php echo c_al2fb_mail_email; ?>" type="text" value="<?php echo $user_email; ?>" />
			<br><strong><?php _e('Please check if this is a correct, reachable e-mail address', c_al2fb_text_domain); ?></strong>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_debug_topic"><strong><a href="http://forum.bokhorst.biz/"><?php _e('Forum topic link:', c_al2fb_text_domain); ?></a></strong></label>
		</th><td>
			<input id="al2fb_debug_topic" class="" name="<?php echo c_al2fb_mail_topic; ?>" type="text" />
			<br><strong><?php _e('Mandatory', c_al2fb_text_domain); ?></strong>
		</td></tr>

		<tr valign="top"><th scope="row">
			<label for="al2fb_debug_msg"><strong><?php _e('Message:', c_al2fb_text_domain); ?></strong></label>
		</th><td>
			<textarea id="al2fb_debug_msg" name="<?php echo c_al2fb_mail_msg; ?>" rows="10" cols="80"></textarea>
			<br><strong><?php _e('Please describe your problem, even if you did before', c_al2fb_text_domain); ?></strong>
		</td></tr>
		</table>

<?php
		$msg = __('Did you check if your problem is described in <a href="[FAQ]" target="_blank">the FAQ</a> ?', c_al2fb_text_domain);
		$msg = str_replace('[FAQ]', 'http://wordpress.org/extend/plugins/add-link-to-facebook/faq/', $msg);
		echo '<br /><strong><span style="color: red;">' . $msg . '</span></strong>';
?>

		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Send', c_al2fb_text_domain) ?>" />
		</p>

		</form>
<?php
		require_once('add-link-to-facebook-debug.php');
		echo al2fb_debug_info($al2fb);
	}
}

?>
