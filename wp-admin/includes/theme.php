<?php
/**
 * WordPress Theme Administration API
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * Remove a theme
 *
 * @since 2.8.0
 *
 * @param string $stylesheet Stylesheet of the theme to delete
 * @param string $redirect Redirect to page when complete.
 * @return mixed
 */
function delete_theme($stylesheet, $redirect = '') {
	global $wp_filesystem;

	if ( empty($stylesheet) )
		return false;

	ob_start();
	if ( empty( $redirect ) )
		$redirect = wp_nonce_url('themes.php?action=delete&stylesheet=' . urlencode( $stylesheet ), 'delete-theme_' . $stylesheet);
	if ( false === ($credentials = request_filesystem_credentials($redirect)) ) {
		$data = ob_get_contents();
		ob_end_clean();
		if ( ! empty($data) ){
			include_once( ABSPATH . 'wp-admin/admin-header.php');
			echo $data;
			include( ABSPATH . 'wp-admin/admin-footer.php');
			exit;
		}
		return;
	}

	if ( ! WP_Filesystem($credentials) ) {
		request_filesystem_credentials($redirect, '', true); // Failed to connect, Error and request again
		$data = ob_get_contents();
		ob_end_clean();
		if ( ! empty($data) ) {
			include_once( ABSPATH . 'wp-admin/admin-header.php');
			echo $data;
			include( ABSPATH . 'wp-admin/admin-footer.php');
			exit;
		}
		return;
	}

	if ( ! is_object($wp_filesystem) )
		return new WP_Error('fs_unavailable', __('Could not access filesystem.'));

	if ( is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code() )
		return new WP_Error('fs_error', __('Filesystem error.'), $wp_filesystem->errors);

	//Get the base plugin folder
	$themes_dir = $wp_filesystem->wp_themes_dir();
	if ( empty($themes_dir) )
		return new WP_Error('fs_no_themes_dir', __('Unable to locate WordPress theme directory.'));

	$themes_dir = trailingslashit( $themes_dir );
	$theme_dir = trailingslashit($themes_dir . $stylesheet);
	$deleted = $wp_filesystem->delete($theme_dir, true);

	if ( ! $deleted )
		return new WP_Error('could_not_remove_theme', sprintf(__('Could not fully remove the theme %s.'), $stylesheet) );

	// Force refresh of theme update information
	delete_site_transient('update_themes');

	return true;
}

/**
 * Get the Page Templates available in this theme
 *
 * @since 1.5.0
 *
 * @param WP_Post|null $post Optional. The post being edited, provided for context.
 * @return array Key is the template name, value is the filename of the template
 */
function get_page_templates( $post = null ) {
	return array_flip( wp_get_theme()->get_page_templates( $post ) );
}

/**
 * Tidies a filename for url display by the theme editor.
 *
 * @since 2.9.0
 * @access private
 *
 * @param string $fullpath Full path to the theme file
 * @param string $containingfolder Path of the theme parent folder
 * @return string
 */
function _get_template_edit_filename($fullpath, $containingfolder) {
	return str_replace(dirname(dirname( $containingfolder )) , '', $fullpath);
}

/**
 * Check if there is an update for a theme available.
 *
 * Will display link, if there is an update available.
 *
 * @since 2.7.0
 * @see get_theme_update_available()
 *
 * @param object $theme Theme data object.
 */
function theme_update_available( $theme ) {
	echo get_theme_update_available( $theme );
}

/**
 * Retrieve the update link if there is a theme update available.
 *
 * Will return a link if there is an update available.
 *
 * @since 3.8.0
 *
 * @param WP_Theme $theme WP_Theme object.
 * @return string|bool HTML for the update link, or false if invalid info was passed.
 */
function get_theme_update_available( $theme ) {
	static $themes_update;

	if ( !current_user_can('update_themes' ) )
		return false;

	if ( !isset($themes_update) )
		$themes_update = get_site_transient('update_themes');

	if ( ! is_a( $theme, 'WP_Theme' ) )
		return false;

	$stylesheet = $theme->get_stylesheet();

	$html = '';

	if ( isset($themes_update->response[ $stylesheet ]) ) {
		$update = $themes_update->response[ $stylesheet ];
		$theme_name = $theme->display('Name');
		$details_url = add_query_arg(array('TB_iframe' => 'true', 'width' => 1024, 'height' => 800), $update['url']); //Theme browser inside WP? replace this, Also, theme preview JS will override this on the available list.
		$update_url = wp_nonce_url( admin_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $stylesheet ) ), 'upgrade-theme_' . $stylesheet );
		$update_onclick = 'onclick="if ( confirm(\'' . esc_js( __("Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.") ) . '\') ) {return true;}return false;"';

		if ( !is_multisite() ) {
			if ( ! current_user_can('update_themes') ) {
				$html = sprintf( '<p><strong>' . __( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>.' ) . '</strong></p>',
					$theme_name, esc_url( $details_url ), esc_attr( $theme['Name'] ), $update['new_version'] );
			} else if ( empty( $update['package'] ) ) {
				$html = sprintf( '<p><strong>' . __( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>. <em>Automatic update is unavailable for this theme.</em>' ) . '</strong></p>',
					$theme_name, esc_url( $details_url ), esc_attr( $theme['Name'] ), $update['new_version'] );
			} else {
				$html = sprintf( '<p><strong>' . __( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update now</a>.' ) . '</strong></p>',
					$theme_name, esc_url( $details_url ), esc_attr( $theme['Name'] ), $update['new_version'], $update_url, $update_onclick );
			}
		}
	}

	return $html;
}

/**
 * Retrieve list of WordPress theme features (aka theme tags)
 *
 * @since 3.1.0
 *
 * @param bool $api Optional. Whether try to fetch tags from the WP.org API. Defaults to true.
 * @return array Array of features keyed by category with translations keyed by slug.
 */
function get_theme_feature_list( $api = true ) {
	// Hard-coded list is used if api not accessible.
	$features = array(
			__( 'Colors' ) => array(
				'black'   => __( 'Black' ),
				'blue'    => __( 'Blue' ),
				'brown'   => __( 'Brown' ),
				'gray'    => __( 'Gray' ),
				'green'   => __( 'Green' ),
				'orange'  => __( 'Orange' ),
				'pink'    => __( 'Pink' ),
				'purple'  => __( 'Purple' ),
				'red'     => __( 'Red' ),
				'silver'  => __( 'Silver' ),
				'tan'     => __( 'Tan' ),
				'white'   => __( 'White' ),
				'yellow'  => __( 'Yellow' ),
				'dark'    => __( 'Dark' ),
				'light'   => __( 'Light' ),
			),

		__( 'Layout' ) => array(
			'fixed-layout'      => __( 'Fixed Layout' ),
			'fluid-layout'      => __( 'Fluid Layout' ),
			'responsive-layout' => __( 'Responsive Layout' ),
			'one-column'    => __( 'One Column' ),
			'two-columns'   => __( 'Two Columns' ),
			'three-columns' => __( 'Three Columns' ),
			'four-columns'  => __( 'Four Columns' ),
			'left-sidebar'  => __( 'Left Sidebar' ),
			'right-sidebar' => __( 'Right Sidebar' ),
		),

		__( 'Features' ) => array(
			'accessibility-ready'   => __( 'Accessibility Ready' ),
			'blavatar'              => __( 'Blavatar' ),
			'buddypress'            => __( 'BuddyPress' ),
			'custom-background'     => __( 'Custom Background' ),
			'custom-colors'         => __( 'Custom Colors' ),
			'custom-header'         => __( 'Custom Header' ),
			'custom-menu'           => __( 'Custom Menu' ),
			'editor-style'          => __( 'Editor Style' ),
			'featured-image-header' => __( 'Featured Image Header' ),
			'featured-images'       => __( 'Featured Images' ),
			'flexible-header'       => __( 'Flexible Header' ),
			'front-page-post-form'  => __( 'Front Page Posting' ),
			'full-width-template'   => __( 'Full Width Template' ),
			'microformats'          => __( 'Microformats' ),
			'post-formats'          => __( 'Post Formats' ),
			'rtl-language-support'  => __( 'RTL Language Support' ),
			'sticky-post'           => __( 'Sticky Post' ),
			'theme-options'         => __( 'Theme Options' ),
			'threaded-comments'     => __( 'Threaded Comments' ),
			'translation-ready'     => __( 'Translation Ready' ),
		),

		__( 'Subject' )  => array(
			'holiday'       => __( 'Holiday' ),
			'photoblogging' => __( 'Photoblogging' ),
			'seasonal'      => __( 'Seasonal' ),
		)
	);

	if ( ! $api || ! current_user_can( 'install_themes' ) )
		return $features;

	if ( !$feature_list = get_site_transient( 'wporg_theme_feature_list' ) )
		set_site_transient( 'wporg_theme_feature_list', array(), 10800);

	if ( !$feature_list ) {
		$feature_list = themes_api( 'feature_list', array() );
		if ( is_wp_error( $feature_list ) )
			return $features;
	}

	if ( !$feature_list )
		return $features;

	set_site_transient( 'wporg_theme_feature_list', $feature_list, 10800 );

	$category_translations = array(
		'Colors'   => __( 'Colors' ),
		'Layout'   => __( 'Layout' ),
		'Features' => __( 'Features' ),
		'Subject'  => __( 'Subject' )
	);

	// Loop over the wporg canonical list and apply translations
	$wporg_features = array();
	foreach ( (array) $feature_list as $feature_category => $feature_items ) {
		if ( isset($category_translations[$feature_category]) )
			$feature_category = $category_translations[$feature_category];
		$wporg_features[$feature_category] = array();

		foreach ( $feature_items as $feature ) {
			if ( isset($features[$feature_category][$feature]) )
				$wporg_features[$feature_category][$feature] = $features[$feature_category][$feature];
			else
				$wporg_features[$feature_category][$feature] = $feature;
		}
	}

	return $wporg_features;
}

/**
 * Retrieve theme installer pages from WordPress Themes API.
 *
 * It is possible for a theme to override the Themes API result with three
 * filters. Assume this is for themes, which can extend on the Theme Info to
 * offer more choices. This is very powerful and must be used with care, when
 * overridding the filters.
 *
 * The first filter, 'themes_api_args', is for the args and gives the action as
 * the second parameter. The hook for 'themes_api_args' must ensure that an
 * object is returned.
 *
 * The second filter, 'themes_api', is the result that would be returned.
 *
 * @since 2.8.0
 *
 * @param string       $action The requested action. Likely values are 'theme_information',
 *                             'feature_list', or 'query_themes'.
 * @param array|object $args   Optional. Arguments to serialize for the Theme Info API.
 * @return mixed
 */
	function themes_api( $action, $args = null ) {

	if ( is_array($args) )
		$args = (object)$args;

	if ( !isset($args->per_page) )
		$args->per_page = 24;
	/**
	 * Filter arguments used to query for installer pages from the WordPress.org Themes API.
	 *
	 * Important: An object MUST be returned to this filter.
	 *
	 * @since 2.8.0
	 *
	 * @param object $args   Arguments used to query for installer pages from the WordPress.org Themes API.
	 * @param string $action Requested action. Likely values are 'theme_information',
	 *                       'feature_list', or 'query_themes'.
 	*/
	$args = apply_filters( 'themes_api_args', $args, $action );

	/**
	 * Filter whether to override the WordPress.org Themes API.
	 *
	 * Returning a value of true to this filter allows a theme to completely
	 * override the built-in WordPress.org API.
	 *
	 * @since 2.8.0
	 *
	 * @param bool   $bool   Whether to override the WordPress.org Themes API. Default false.
	 * @param string $action Requested action. Likely values are 'theme_information',
	 *                       'feature_list', or 'query_themes'.
	 * @param object $args   Arguments used to query for installer pages from the Themes API.
	 */
	$res = apply_filters( 'themes_api', false, $action, $args );

	if ( ! $res ) {
		$url = $http_url = 'http://api.wordpress.org/themes/info/1.0/';
		if ( $ssl = wp_http_supports( array( 'ssl' ) ) )
			$url = set_url_scheme( $url, 'https' );

		$args = array(
			'body' => array(
				'action' => $action,
				'request' => serialize( $args )
			)
		);
		$request = wp_remote_post( $url, $args );

		if ( $ssl && is_wp_error( $request ) ) {
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				trigger_error( __( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.' ) . ' ' . __( '(WordPress could not establish a secure connection to WordPress.org. Please contact your server administrator.)' ), headers_sent() || WP_DEBUG ? E_USER_WARNING : E_USER_NOTICE );
			}
			$request = wp_remote_post( $http_url, $args );
		}

		if ( is_wp_error($request) ) {
			$res = new WP_Error('themes_api_failed', __( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.' ), $request->get_error_message() );
		} else {
			$res = maybe_unserialize( wp_remote_retrieve_body( $request ) );
			if ( ! is_object( $res ) && ! is_array( $res ) )
				$res = new WP_Error('themes_api_failed', __( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.' ), wp_remote_retrieve_body( $request ) );
		}
	}

	/**
	 * Filter the returned WordPress.org Themes API response.
	 *
	 * @since 2.8.0
	 *
	 * @param array|object $res    WordPress.org Themes API response.
	 * @param string       $action Requested action. Likely values are 'theme_information',
	 *                             'feature_list', or 'query_themes'.
	 * @param object       $args   Arguments used to query for installer pages from the WordPress.org Themes API.
	 */
	return apply_filters( 'themes_api_result', $res, $action, $args );
}

/**
 * Prepare themes for JavaScript.
 *
 * @since 3.8.0
 *
 * @param array $themes Optional. Array of WP_Theme objects to prepare.
 *                      Defaults to all allowed themes.
 *
 * @return array An associative array of theme data, sorted by name.
 */
function wp_prepare_themes_for_js( $themes = null ) {
	$current_theme = get_stylesheet();

	// Make sure the current theme is listed first.
	$prepared_themes = array( $current_theme => array() );

	if ( null === $themes ) {
		$themes = wp_get_themes( array( 'allowed' => true ) );
		if ( ! isset( $themes[ $current_theme ] ) ) {
			$themes[ $current_theme ] = wp_get_theme();
		}
	}

	$updates = array();
	if ( current_user_can( 'update_themes' ) ) {
		$updates_transient = get_site_transient( 'update_themes' );
		if ( isset( $updates_transient->response ) ) {
			$updates = $updates_transient->response;
		}
	}

	WP_Theme::sort_by_name( $themes );
	foreach ( $themes as $theme ) {
		$parent = false;
		if ( $theme->parent() ) {
			$parent = $theme->parent()->display( 'Name' );
		}

		$slug = $theme->get_stylesheet();
		$encoded_slug = urlencode( $slug );

		$prepared_themes[ $slug ] = array(
			'id'           => $slug,
			'name'         => $theme->display( 'Name' ),
			'screenshot'   => array( $theme->get_screenshot() ), // @todo multiple
			'description'  => $theme->display( 'Description' ),
			'author'       => $theme->display( 'Author', false, true ),
			'authorAndUri' => $theme->display( 'Author' ),
			'version'      => $theme->display( 'Version' ),
			'tags'         => $theme->display( 'Tags' ),
			'parent'       => $parent,
			'active'       => $slug === $current_theme,
			'hasUpdate'    => isset( $updates[ $slug ] ),
			'update'       => get_theme_update_available( $theme ),
			'actions'      => array(
				'activate' => current_user_can( 'switch_themes' ) ? wp_nonce_url( admin_url( 'themes.php?action=activate&amp;stylesheet=' . $encoded_slug ), 'switch-theme_' . $slug ) : null,
				'customize'=> current_user_can( 'edit_theme_options' ) ? wp_customize_url( $slug ) : null,
				'preview'   => add_query_arg( array(
					'preview'        => 1,
					'template'       => urlencode( $theme->get_template() ),
					'stylesheet'     => urlencode( $slug ),
					'preview_iframe' => true,
					'TB_iframe'      => true,
				), home_url( '/' ) ),
				'delete'   => current_user_can( 'delete_themes' ) ? wp_nonce_url( admin_url( 'themes.php?action=delete&amp;stylesheet=' . $encoded_slug ), 'delete-theme_' . $slug ) : null,
			),
		);
	}

	/**
	 * Filter the themes prepared for JavaScript, for themes.php.
	 *
	 * Could be useful for changing the order, which is by name by default.
	 *
	 * @since 3.8.0
	 *
	 * @param array $prepared_themes Array of themes.
	 */
	$prepared_themes = apply_filters( 'wp_prepare_themes_for_js', $prepared_themes );
	return array_values( $prepared_themes );
}
