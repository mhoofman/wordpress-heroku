<?php

/*
	Support class Add Link to Facebook widget
	Copyright (c) 2011, 2012 by Marcel Bokhorst
*/

class AL2FB_Widget extends WP_Widget {
	function AL2FB_Widget() {
		$widget_ops = array('classname' => 'widget_al2fb', 'description' => '');
		$this->WP_Widget('AL2FB_Widget', 'Add Link to Facebook', $widget_ops);
	}

	function widget($args, $instance) {
		global $wp_al2fb;

		if (!is_single() && !is_page())
			return;

		// Get current post
		if (!empty($GLOBALS['post']))
			$post = $GLOBALS['post'];
		if (empty($post->ID) && !empty($post['post_id']))
			$post = get_post($post['post_id']);
		if (empty($post) || empty($post->ID))
			return;

		// Excluded post types
		$ex_custom_types = explode(',', get_option(c_al2fb_option_exclude_type));
		if (in_array($post->post_type, $ex_custom_types))
			return;

		// Get user
		$user_ID = $wp_al2fb->Get_user_ID($post);

		// Check if widget should be displayed
		if ((get_user_meta($user_ID, c_al2fb_meta_like_nohome, true) && is_home()) ||
			(get_user_meta($user_ID, c_al2fb_meta_like_noposts, true) && is_single()) ||
			(get_user_meta($user_ID, c_al2fb_meta_like_nopages, true) && is_page()) ||
			(get_user_meta($user_ID, c_al2fb_meta_like_noarchives, true) && is_archive()) ||
			(get_user_meta($user_ID, c_al2fb_meta_like_nocategories, true) && is_category()) ||
			get_post_meta($post->ID, c_al2fb_meta_nolike, true))
			return;

		// Get settings
		$comments = isset($instance['al2fb_comments']) ? $instance['al2fb_comments'] : false;
		$comments_count = isset($instance['al2fb_comments_count']) ? $instance['al2fb_comments_count'] : false;
		$messages = isset($instance['al2fb_messages']) ? $instance['al2fb_messages'] : false;
		$messages_count = isset($instance['al2fb_messages_count']) ? $instance['al2fb_messages_count'] : false;
		$messages_comments = isset($instance['al2fb_messages_comments']) ? $instance['al2fb_messages_comments'] : false;
		$like_button = isset($instance['al2fb_like_button']) ? $instance['al2fb_like_button'] : false;
		$like_box = isset($instance['al2fb_like_box']) ? $instance['al2fb_like_box'] : false;
		$send_button = isset($instance['al2fb_send_button']) ? $instance['al2fb_send_button'] : false;
		$comments_plugin = isset($instance['al2fb_comments_plugin']) ? $instance['al2fb_comments_plugin'] : false;
		$face_pile = isset($instance['al2fb_face_pile']) ? $instance['al2fb_face_pile'] : false;
		$profile = isset($instance['al2fb_profile']) ? $instance['al2fb_profile'] : false;
		$registration = isset($instance['al2fb_registration']) ? $instance['al2fb_registration'] : false;
		$login = isset($instance['al2fb_login']) ? $instance['al2fb_login'] : false;
		$activity = isset($instance['al2fb_activity']) ? $instance['al2fb_activity'] : false;

		// Logged in?
		$registration = ($registration && !is_user_logged_in() && get_option('users_can_register'));
		$login = ($login && !is_user_logged_in());

		// More settings
		$charset = get_bloginfo('charset');
		$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);

		// Get link type
		$comments_nolink = get_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, true);
		if (empty($comments_nolink))
			$comments_nolink = 'author';
		else if ($comments_nolink == 'on')
			$comments_nolink = 'none';

		// Get time zone offset
		$tz_off = get_option('gmt_offset');
		if (empty($tz_off))
			$tz_off = 0;
		else
			$tz_off = $tz_off * 3600;

		// Get comments
		$fb_comments = false;
		if ($comments)
			$fb_comments = $wp_al2fb->Get_comments_or_likes($post, false);

		// Get messages
		$fb_messages = false;
		if ($messages)
			try {
				$fb_messages = $wp_al2fb->Get_fb_feed_cached($user_ID);
			}
			catch (Exception $e) {
				if ($wp_al2fb->debug)
					print_r($e);
			}

		if ($fb_comments || $fb_messages ||
			$like_button || $like_box || $send_button ||
			$comments_plugin || $face_pile ||
			$profile || $registration || $login || $activity) {
			// Get values
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);

			// Build content
			echo $before_widget;
			if (empty($title))
				$title = 'Add Link to Facebook';
			echo $before_title . $title . $after_title;

			// Comments
			if ($fb_comments) {
				echo '<div class="al2fb_widget_comments">';
				self::Render_fb_comments($fb_comments, $comments_nolink, $link_id, $comments_count);
				echo '</div>';
			}

			// Status messages
			if ($fb_messages) {
				echo '<div class="al2fb_widget_messages">';
				self::Render_fb_messages($fb_messages, $comments_nolink, $link_id, $messages_count, $messages_comments);
				echo '</div>';
			}

			// Facebook like button
			if ($like_button)
				echo $wp_al2fb->Get_like_button($post, false);

			// Facebook like box
			if ($like_box)
				echo $wp_al2fb->Get_like_button($post, true);

			// Facebook send button
			if ($send_button)
				echo $wp_al2fb->Get_send_button($post);

			// Facebook comments plugins
			if ($comments_plugin)
				echo $wp_al2fb->Get_comments_plugin($post);

			// Facebook Face pile
			if ($face_pile)
				echo $wp_al2fb->Get_face_pile($post);

			// Facebook profile
			if ($profile)
				echo $wp_al2fb->Get_profile_link($post);

			// Facebook registration
			if ($registration)
				echo $wp_al2fb->Get_registration($post);

			// Facebook login
			if ($login)
				echo $wp_al2fb->Get_login($post);

			// Facebook activity feed
			if ($activity)
				echo $wp_al2fb->Get_activity_feed($post);

			echo $after_widget;
		}
	}

	// Helper render Facebook comments
	function Render_fb_comments($fb_comments, $comments_nolink, $link_id, $max_count) {
		global $wp_al2fb;
		$charset = get_bloginfo('charset');

		// Get time zone offset
		$tz_off = get_option('gmt_offset');
		if (empty($tz_off))
			$tz_off = 0;
		else
			$tz_off = $tz_off * 3600;

		$fb_comments->data = array_reverse($fb_comments->data);

		$count = 0;
		echo '<ul>';
		foreach ($fb_comments->data as $fb_comment) {
			if ($max_count && ++$count > $max_count)
				break;
			echo '<li>';

			// Picture
			if ($comments_nolink == 'author')
				echo '<img class="al2fb_widget_picture" alt="' . htmlspecialchars($fb_comment->from->name, ENT_QUOTES, $charset) . '" src="' . $wp_al2fb->Get_fb_picture_url_cached($fb_comment->from->id, 'small') . '" />';

			// Author
			echo ' ';
			if ($comments_nolink == 'link')
				echo '<a href="' . $wp_al2fb->Get_fb_permalink($link_id) . '" class="al2fb_widget_name">' .  htmlspecialchars($fb_comment->from->name, ENT_QUOTES, $charset) . '</a>';
			else if ($comments_nolink == 'author')
				echo '<a href="' . $wp_al2fb->Get_fb_profilelink($fb_comment->from->id) . '" class="al2fb_widget_name">' .  htmlspecialchars($fb_comment->from->name, ENT_QUOTES, $charset) . '</a>';
			else
				echo '<span class="al2fb_widget_name">' .  htmlspecialchars($fb_comment->from->name, ENT_QUOTES, $charset) . '</span>';

			// Comment
			echo ' ';
			echo '<span class="al2fb_widget_comment">' .  htmlspecialchars($fb_comment->message, ENT_QUOTES, $charset) . '</span>';

			// Time
			echo ' ';
			$fb_time = strtotime($fb_comment->created_time) + $tz_off;
			echo '<span class="al2fb_widget_date">' . date(get_option('date_format') . ' ' . get_option('time_format'), $fb_time) . '</span>';

			echo '</li>';
		}
		echo '</ul>';
	}

	// Helper render Facebook status messages
	function Render_fb_messages($fb_messages, $comments_nolink, $link_id, $max_count, $messages_comments) {
		global $wp_al2fb;
		$charset = get_bloginfo('charset');

		// Get time zone offset
		$tz_off = get_option('gmt_offset');
		if (empty($tz_off))
			$tz_off = 0;
		else
			$tz_off = $tz_off * 3600;

		$count = 0;
		echo '<ul>';
		foreach ($fb_messages->data as $fb_message)
			if (isset($fb_message->message)) {
				if ($max_count && ++$count > $max_count)
					break;
				echo '<li>';

				// Picture
				if ($comments_nolink == 'author')
					echo '<img class="al2fb_widget_picture" alt="' . htmlspecialchars($fb_message->from->name, ENT_QUOTES, $charset) . '" src="' . $wp_al2fb->Get_fb_picture_url_cached($fb_message->from->id, 'small') . '" />';

				// Author
				if ($comments_nolink == 'link')
					echo '<a href="' . $wp_al2fb->Get_fb_permalink($fb_message->id) . '" class="al2fb_widget_name">' .  htmlspecialchars($fb_message->from->name, ENT_QUOTES, $charset) . '</a>';
				else if ($comments_nolink == 'author')
					echo '<a href="' . $wp_al2fb->Get_fb_profilelink($fb_message->from->id) . '" class="al2fb_widget_name">' .  htmlspecialchars($fb_message->from->name, ENT_QUOTES, $charset) . '</a>';
				else
					echo '<span class="al2fb_widget_name">' .  htmlspecialchars($fb_message->from->name, ENT_QUOTES, $charset) . '</span>';

				// Message
				echo ' ';
				echo '<span class="al2fb_widget_message">' .  htmlspecialchars($fb_message->message, ENT_QUOTES, $charset) . '</span>';

				// Time
				echo ' ';
				$fb_time = strtotime($fb_message->created_time) + $tz_off;
				echo '<span class="al2fb_widget_date">' . date(get_option('date_format') . ' ' . get_option('time_format'), $fb_time) . '</span>';

				// Comments on message
				if ($messages_comments)
					try {
						$fb_message_comments = $wp_al2fb->Get_fb_comments_cached($user_ID, $fb_message->id);
						if ($fb_message_comments)
							self::Render_fb_comments($fb_message_comments, $comments_nolink, $fb_message->id, $messages_comments);
					}
					catch (Exception $e) {
						$error = $e->getMessage();
					}

				echo '</li>';
			}
		echo '</ul>';
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['al2fb_comments'] = $new_instance['al2fb_comments'];
		$instance['al2fb_comments_count'] = $new_instance['al2fb_comments_count'];
		$instance['al2fb_messages'] = $new_instance['al2fb_messages'];
		$instance['al2fb_messages_count'] = $new_instance['al2fb_messages_count'];
		$instance['al2fb_messages_comments'] = $new_instance['al2fb_messages_comments'];
		$instance['al2fb_like_button'] = $new_instance['al2fb_like_button'];
		$instance['al2fb_like_box'] = $new_instance['al2fb_like_box'];
		$instance['al2fb_send_button'] = $new_instance['al2fb_send_button'];
		$instance['al2fb_comments_plugin'] = $new_instance['al2fb_comments_plugin'];
		$instance['al2fb_face_pile'] = $new_instance['al2fb_face_pile'];
		$instance['al2fb_profile'] = $new_instance['al2fb_profile'];
		$instance['al2fb_registration'] = $new_instance['al2fb_registration'];
		$instance['al2fb_login'] = $new_instance['al2fb_login'];
		$instance['al2fb_activity'] = $new_instance['al2fb_activity'];
		return $instance;
	}

	function form($instance) {
		if (empty($instance['title']))
			$instance['title'] = null;
		if (empty($instance['al2fb_comments']))
			$instance['al2fb_comments'] = false;
		if (empty($instance['al2fb_comments_count']))
			$instance['al2fb_comments_count'] = null;
		if (empty($instance['al2fb_messages']))
			$instance['al2fb_messages'] = false;
		if (empty($instance['al2fb_messages_count']))
			$instance['al2fb_messages_count'] = null;
		if (empty($instance['al2fb_messages_comments']))
			$instance['al2fb_messages_comments'] = false;
		if (empty($instance['al2fb_like_button']))
			$instance['al2fb_like_button'] = false;
		if (empty($instance['al2fb_like_box']))
			$instance['al2fb_like_box'] = false;
		if (empty($instance['al2fb_send_button']))
			$instance['al2fb_send_button'] = false;
		if (empty($instance['al2fb_comments_plugin']))
			$instance['al2fb_comments_plugin'] = false;
		if (empty($instance['al2fb_face_pile']))
			$instance['al2fb_face_pile'] = false;
		if (empty($instance['al2fb_profile']))
			$instance['al2fb_profile'] = false;
		if (empty($instance['al2fb_registration']))
			$instance['al2fb_registration'] = false;
		if (empty($instance['al2fb_login']))
			$instance['al2fb_login'] = false;
		if (empty($instance['al2fb_activity']))
			$instance['al2fb_activity'] = false;

		$chk_comments = ($instance['al2fb_comments'] ? ' checked ' : '');
		$chk_messages = ($instance['al2fb_messages'] ? ' checked ' : '');
		$chk_messages_comments = ($instance['al2fb_messages_comments'] ? ' checked ' : '');
		$chk_like = ($instance['al2fb_like_button'] ? ' checked ' : '');
		$chk_box = ($instance['al2fb_like_box'] ? ' checked ' : '');
		$chk_send = ($instance['al2fb_send_button'] ? ' checked ' : '');
		$chk_comments_plugin = ($instance['al2fb_comments_plugin'] ? ' checked ' : '');
		$chk_face_pile = ($instance['al2fb_face_pile'] ? ' checked ' : '');
		$chk_profile = ($instance['al2fb_profile'] ? ' checked ' : '');
		$chk_registration = ($instance['al2fb_registration'] ? ' checked ' : '');
		$chk_login = ($instance['al2fb_login'] ? ' checked ' : '');
		$chk_activity = ($instance['al2fb_activity'] ? ' checked ' : '');
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_comments; ?> id="<?php echo $this->get_field_id('al2fb_comments'); ?>" name="<?php echo $this->get_field_name('al2fb_comments'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_comments'); ?>"><?php _e('Show Facebook comments', c_al2fb_text_domain); ?></label>
			<br />
			<label for="<?php echo $this->get_field_id('al2fb_comments_count'); ?>"><?php _e('Maximum number:', c_al2fb_text_domain); ?></label>
			<input class="al2fb_numeric" id="<?php echo $this->get_field_id('al2fb_comments_count'); ?>" name="<?php echo $this->get_field_name('al2fb_comments_count'); ?>" type="text" value="<?php echo esc_attr($instance['al2fb_comments_count']); ?>" />
			<br />
			<strong><?php _e('Appearance depends on your theme!', c_al2fb_text_domain); ?></strong>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_messages; ?> id="<?php echo $this->get_field_id('al2fb_messages'); ?>" name="<?php echo $this->get_field_name('al2fb_messages'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_messages'); ?>"><?php _e('Show Facebook messages', c_al2fb_text_domain); ?></label>
			<br />
			<label for="<?php echo $this->get_field_id('al2fb_messages_count'); ?>"><?php _e('Maximum number:', c_al2fb_text_domain); ?></label>
			<input class="al2fb_numeric" id="<?php echo $this->get_field_id('al2fb_messages_count'); ?>" name="<?php echo $this->get_field_name('al2fb_messages_count'); ?>" type="text" value="<?php echo esc_attr($instance['al2fb_messages_count']); ?>" />
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_messages_comments; ?> id="<?php echo $this->get_field_id('al2fb_messages_comments'); ?>" name="<?php echo $this->get_field_name('al2fb_messages_comments'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_messages_comments'); ?>"><?php _e('Show comments on messages', c_al2fb_text_domain); ?></label>
			<br />
			<strong><?php _e('Appearance depends on your theme!', c_al2fb_text_domain); ?></strong>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_like; ?> id="<?php echo $this->get_field_id('al2fb_like_button'); ?>" name="<?php echo $this->get_field_name('al2fb_like_button'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_like_button'); ?>"><?php _e('Show Facebook like button', c_al2fb_text_domain); ?></label>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_box; ?> id="<?php echo $this->get_field_id('al2fb_like_box'); ?>" name="<?php echo $this->get_field_name('al2fb_like_box'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_like_box'); ?>"><?php _e('Show Facebook like box', c_al2fb_text_domain); ?></label>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_send; ?> id="<?php echo $this->get_field_id('al2fb_send_button'); ?>" name="<?php echo $this->get_field_name('al2fb_send_button'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_send_button'); ?>"><?php _e('Show Facebook send button', c_al2fb_text_domain); ?></label>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_comments_plugin; ?> id="<?php echo $this->get_field_id('al2fb_comments_plugin'); ?>" name="<?php echo $this->get_field_name('al2fb_comments_plugin'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_comments_plugin'); ?>"><?php _e('Show Facebook comments plugin', c_al2fb_text_domain); ?></label>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_face_pile; ?> id="<?php echo $this->get_field_id('al2fb_face_pile'); ?>" name="<?php echo $this->get_field_name('al2fb_face_pile'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_face_pile'); ?>"><?php _e('Show Facebook face pile', c_al2fb_text_domain); ?></label>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_profile; ?> id="<?php echo $this->get_field_id('al2fb_profile'); ?>" name="<?php echo $this->get_field_name('al2fb_profile'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_profile'); ?>"><?php _e('Show Facebook image/link', c_al2fb_text_domain); ?></label>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_registration; ?> id="<?php echo $this->get_field_id('al2fb_registration'); ?>" name="<?php echo $this->get_field_name('al2fb_registration'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_profile'); ?>"><?php _e('Show Facebook registration', c_al2fb_text_domain); ?></label>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_login; ?> id="<?php echo $this->get_field_id('al2fb_login'); ?>" name="<?php echo $this->get_field_name('al2fb_login'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_login'); ?>"><?php _e('Show Facebook login', c_al2fb_text_domain); ?></label>
			<br />

			<input class="checkbox" type="checkbox" <?php echo $chk_activity; ?> id="<?php echo $this->get_field_id('al2fb_activity'); ?>" name="<?php echo $this->get_field_name('al2fb_activity'); ?>" />
			<label for="<?php echo $this->get_field_id('al2fb_activity'); ?>"><?php _e('Show Facebook activity feed', c_al2fb_text_domain); ?></label>
		</p>
		<?php
	}
}

?>
