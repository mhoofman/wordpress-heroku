<?php

/*
* Admin User Interface
*/

require_once plugin_dir_path( __FILE__ ) . 'yst_plugin_tools.php';
require_once plugin_dir_path( __FILE__ ) . '/wp-gdata/wp-gdata.php';

$options = get_option( 'Yoast_Google_Analytics' );

global $wp_version;
if ( version_compare( $wp_version, '3.3', '>=' ) && !isset( $options['tracking_popup'] ) )
	require_once plugin_dir_path( __FILE__ ) . 'class-pointer.php';

class GA_Admin extends Yoast_GA_Plugin_Admin {

	var $hook = 'google-analytics-for-wordpress';
	var $longname = '';
	var $shortname = '';
	var $ozhicon = 'images/chart_curve.png';
	var $optionname = 'Yoast_Google_Analytics';
	var $homepage = 'http://yoast.com/wordpress/google-analytics/';
	var $toc = '';

	/**
	 * Constructur, load all required stuff.
	 */
	function __construct() {
		$this->longname  = __( 'Google Analytics Configuration', 'gawp' );
		$this->shortname = __( 'Google Analytics', 'gawp' );

		$this->upgrade();

		$this->plugin_url = plugins_url( '', __FILE__ ) . '/';

		// Register the settings page
		add_action( 'admin_menu', array( &$this, 'register_settings_page' ) );

		// Register the contextual help for the settings page
		//	add_action( 'contextual_help', 		array(&$this, 'plugin_help'), 10, 3 );

		// Give the plugin a settings link in the plugin overview
		add_filter( 'plugin_action_links', array( &$this, 'add_action_link' ), 10, 2 );

		// Print Scripts and Styles
		add_action( 'admin_print_scripts', array( &$this, 'config_page_scripts' ) );
		add_action( 'admin_print_styles', array( &$this, 'config_page_styles' ) );

		// Print stuff in the settings page's head
		add_action( 'admin_head', array( &$this, 'config_page_head' ) );

		// Drop a warning on each page of the admin when Google Analytics hasn't been configured
		add_action( 'admin_footer', array( &$this, 'warning' ) );

		// Save settings
		// TODO: replace with Options API
		add_action( 'admin_init', array( &$this, 'save_settings' ) );

		// Authenticate
		add_action( 'admin_init', array( &$this, 'authenticate' ) );
	}

	function config_page_head() {

		global $current_screen;
		if ( 'settings_page_' . $this->hook == $current_screen->id ) {
			?>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery(".chzn-select").chosen({ allow_single_deselect:true });
                jQuery('#position').change(function () {
                    if (jQuery('#position').val() == 'header') {
                        jQuery('#position_header').css("display", "block");
                        jQuery('#position_manual').css("display", "none");
                    } else {
                        jQuery('#position_header').css("display", "none");
                        jQuery('#position_manual').css("display", "block");
                    }
                }).change();
                jQuery('#switchtomanual').change(function () {
                    if (jQuery('#switchtomanual').is(':checked')) {
                        jQuery('#uastring_manual').css('display', 'block');
                        jQuery('#uastring_automatic').css('display', 'none');
                    } else {
                        jQuery('#uastring_manual').css('display', 'none');
                        jQuery('#uastring_automatic').css('display', 'block');
                    }
                }).change();
                jQuery('#trackoutbound').change(function () {
                    if (jQuery('#trackoutbound').is(':checked')) {
                        jQuery('#internallinktracking').css("display", "block");
                        jQuery('.internallinktracking').css("display", "list-item");
                    } else {
                        jQuery('#internallinktracking').css("display", "none");
                        jQuery('.internallinktracking').css("display", "none");
                    }
                }).change();
                jQuery('#advancedsettings').change(function () {
                    if (jQuery('#advancedsettings').is(':checked')) {
                        jQuery('#advancedgasettings').css("display", "block");
                        jQuery('#customvarsettings').css("display", "block");
                        jQuery('.advancedgasettings').css("display", "list-item");
                        jQuery('.customvarsettings').css("display", "list-item");
                    } else {
                        jQuery('#advancedgasettings').css("display", "none");
                        jQuery('#customvarsettings').css("display", "none");
                        jQuery('.advancedgasettings').css("display", "none");
                        jQuery('.customvarsettings').css("display", "none");
                    }
                }).change();
                jQuery('#extrase').change(function () {
                    if (jQuery('#extrase').is(':checked')) {
                        jQuery('#extrasebox').css("display", "block");
                    } else {
                        jQuery('#extrasebox').css("display", "none");
                    }
                }).change();
                jQuery('#gajslocalhosting').change(function () {
                    if (jQuery('#gajslocalhosting').is(':checked')) {
                        jQuery('#localhostingbox').css("display", "block");
                    } else {
                        jQuery('#localhostingbox').css("display", "none");
                    }
                }).change();
                jQuery('#customvarsettings :input').change(function () {
                    if (jQuery("#customvarsettings :input:checked").size() > 5) {
                        alert("<?php _e( 'The maximum number of allowed custom variables in Google Analytics is 5, please unselect one of the other custom variables before selecting this one.', 'gawp' ); ?>");
                        jQuery(this).attr('checked', false);
                    }
                });
                jQuery('#uastring').change(function () {
                    if (jQuery('#switchtomanual').is(':checked')) {
                        if (!jQuery(this).val().match(/^UA-[\d-]+$/)) {
                            alert("<?php _e( 'That\'s not a valid UA ID, please make sure it matches the expected pattern of: UA-XXXXXX-X, and that there are no spaces or other characters in the input field.', 'gawp' ); ?>");
                            jQuery(this).focus();
                        }
                    }
                });
            });
        </script>
        <link rel="shortcut icon" href="<?php echo GAWP_URL; ?>images/favicon.ico"/>
		<?php
		}
	}

	function plugin_help( $contextual_help, $screen_id, $screen ) {
		if ( $screen_id == 'settings_page_' . $this->hook ) {

			$contextual_help = '<h2>' . __( 'Having problems?', 'gawp' ) . '</h2>' .
				'<p>' . sprintf( __( "If you're having problems with this plugin, please refer to its <a href='%s'>FAQ page</a>.", 'gawp' ), 'http://yoast.com/wordpress/google-analytics/ga-wp-faq/' ) . '</p>';
		}
		return $contextual_help;
	}

	function save_settings() {
		$options = get_option( $this->optionname );

		if ( isset( $_REQUEST['reset'] ) && $_REQUEST['reset'] == "true" && isset( $_REQUEST['plugin'] ) && $_REQUEST['plugin'] == 'google-analytics-for-wordpress' ) {
			$options        = $this->set_defaults();
			$options['msg'] = "<div class=\"updated\"><p>" . __( 'Google Analytics settings reset.', 'gawp' ) . "</p></div>\n";
		} elseif ( isset( $_POST['submit'] ) && isset( $_POST['plugin'] ) && $_POST['plugin'] == 'google-analytics-for-wordpress' ) {

			if ( !current_user_can( 'manage_options' ) ) wp_die( __( 'You cannot edit the Google Analytics for WordPress options.', 'gawp' ) );
			check_admin_referer( 'analyticspp-config' );

			foreach ( array( 'uastring', 'dlextensions', 'domainorurl', 'position', 'domain', 'customcode', 'ga_token', 'extraseurl', 'gajsurl', 'gfsubmiteventpv', 'trackprefix', 'ignore_userlevel', 'internallink', 'internallinklabel', 'primarycrossdomain', 'othercrossdomains' ) as $option_name ) {
				if ( isset( $_POST[$option_name] ) )
					$options[$option_name] = $_POST[$option_name];
				else
					$options[$option_name] = '';
			}

			foreach ( array( 'extrase', 'trackoutbound', 'admintracking', 'trackadsense', 'allowanchor', 'allowlinker', 'allowhash', 'rsslinktagging', 'advancedsettings', 'trackregistration', 'theme_updated', 'cv_loggedin', 'cv_authorname', 'cv_category', 'cv_all_categories', 'cv_tags', 'cv_year', 'cv_post_type', 'outboundpageview', 'downloadspageview', 'trackcrossdomain', 'gajslocalhosting', 'manual_uastring', 'taggfsubmit', 'wpec_tracking', 'shopp_tracking', 'anonymizeip', 'trackcommentform', 'debug', 'firebuglite', 'yoast_tracking' ) as $option_name ) {
				if ( isset( $_POST[$option_name] ) && $_POST[$option_name] == 'on' )
					$options[$option_name] = true;
				else
					$options[$option_name] = false;
			}

			if ( isset( $_POST['manual_uastring'] ) && isset( $_POST['uastring_man'] ) ) {
				$options['uastring'] = $_POST['uastring_man'];
			}

			if ( $options['trackcrossdomain'] ) {
				if ( !$options['allowlinker'] )
					$options['allowlinker'] = true;

				if ( empty( $options['primarycrossdomain'] ) ) {
					$origin                        = yoast_ga_get_domain( $_SERVER["HTTP_HOST"] );
					$options['primarycrossdomain'] = $origin["domain"];
				}
			}

			if ( function_exists( 'w3tc_pgcache_flush' ) )
				w3tc_pgcache_flush();

			if ( function_exists( 'w3tc_dbcache_flush' ) )
				w3tc_dbcache_flush();

			if ( function_exists( 'w3tc_minify_flush' ) )
				w3tc_minify_flush();

			if ( function_exists( 'w3tc_objectcache_flush' ) )
				w3tc_objectcache_flush();

			if ( function_exists( 'wp_cache_clear_cache' ) )
				wp_cache_clear_cache();

			$options['msg'] = "<div id=\"updatemessage\" class=\"updated fade\"><p>" . __( "Google Analytics settings updated.", "gawp" ) . "</p></div>\n";
			$options['msg'] .= "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
		}
		update_option( $this->optionname, $options );
	}

	function save_button() {
		return '<div class="alignright"><input type="submit" class="button-primary" name="submit" value="' . __( 'Update Google Analytics Settings &raquo;', 'gawp' ) . '" /></div><br class="clear"/>';
	}

	function upgrade() {
		$options = get_option( $this->optionname );
		if ( isset( $options['version'] ) && $options['version'] < '4.04' ) {
			if ( !isset( $options['ignore_userlevel'] ) || $options['ignore_userlevel'] == '' )
				$options['ignore_userlevel'] = 11;
		}
		if ( !isset( $options['version'] ) || $options['version'] != GAWP_VERSION ) {
			$options['version'] = GAWP_VERSION;
		}
		update_option( $this->optionname, $options );
	}

	function config_page() {
		$options = get_option( $this->optionname );
		if ( isset( $options['msg'] ) )
			echo $options['msg'];
		$options['msg'] = '';
		update_option( $this->optionname, $options );

		if ( !isset( $options['uastring'] ) )
			$options = $this->set_defaults();
		$modules = array();

		if ( !isset( $options['manual_uastring'] ) )
			$options['manual_uastring'] = '';
		?>
    <div class="wrap">
    <a href="http://yoast.com/">
        <div id="yoast-icon"
             style="background: url('<?php echo GAWP_URL; ?>images/ga-icon-32x32.png') no-repeat;"
             class="icon32"><br/></div>
    </a>

    <h2><?php _e( "Google Analytics for WordPress Configuration", 'gawp' ) ?></h2>

    <div class="postbox-container" style="width:60%;">
    <div class="metabox-holder">
    <div class="meta-box-sortables">
    <form action="<?php echo $this->plugin_options_url(); ?>" method="post" id="analytics-conf">
    <input type="hidden" name="plugin" value="google-analytics-for-wordpress"/>
		<?php
		wp_nonce_field( 'analyticspp-config' );

		if ( empty( $options['uastring'] ) && empty( $options['ga_token'] ) ) {
			$query = $this->plugin_options_url() . '&reauth=true';
			$line  = __( 'Please authenticate with Google Analytics to retrieve your tracking code:', 'gawp' ) . '<br/><br/> <a class="button-primary" href="' . $query . '">' . __( 'Click here to authenticate with Google', 'gawp' ) . '</a>';
		} else if ( isset( $options['ga_token'] ) && !empty( $options['ga_token'] ) ) {
			$token = $options['ga_token'];

			require_once plugin_dir_path( __FILE__ ) . 'xmlparser.php';
			if ( file_exists( ABSPATH . 'wp-includes/class-http.php' ) )
				require_once( ABSPATH . 'wp-includes/class-http.php' );

			if ( !isset( $options['ga_api_responses'][$token] ) ) {
				$options['ga_api_responses'] = array();

				if ( $oauth = $options['gawp_oauth'] ) {
					if ( isset( $oauth['params']['oauth_token'], $oauth['params']['oauth_token_secret'] ) ) {
						$options['gawp_oauth']['access_token'] = array(
							'oauth_token'        => base64_decode( $oauth['params']['oauth_token'] ),
							'oauth_token_secret' => base64_decode( $oauth['params']['oauth_token_secret'] )
						);
						unset( $options['gawp_oauth']['params'] );
						update_option( $this->optionname, $options );
					}
				}

				$args         = array(
					'scope'              => 'https://www.googleapis.com/auth/analytics.readonly',
					'xoauth_displayname' => 'Google Analytics for WordPress by Yoast'
				);
				$access_token = $options['gawp_oauth']['access_token'];
				$gdata        = new WP_Gdata( $args, $access_token['oauth_token'], $access_token['oauth_token_secret'] );

				$response  = $gdata->get( 'https://www.googleapis.com/analytics/v2.4/management/accounts/~all/webproperties/~all/profiles' );
				$http_code = wp_remote_retrieve_response_code( $response );
				$response  = wp_remote_retrieve_body( $response );

				if ( $http_code == 200 ) {
					$options['ga_api_responses'][$token] = array(
						'response' => array( 'code' => $http_code ),
						'body'     => $response
					);
					$options['ga_token']                 = $token;
					update_option( 'Yoast_Google_Analytics', $options );
				}
			}

			if ( isset( $options['ga_api_responses'][$token] ) && is_array( $options['ga_api_responses'][$token] ) && $options['ga_api_responses'][$token]['response']['code'] == 200 ) {
				$arr = yoast_xml2array( $options['ga_api_responses'][$token]['body'] );

				$ga_accounts = array();

				$currentua = '';
				if ( !empty( $options['uastring'] ) )
					$currentua = $options['uastring'];

				if ( isset( $arr['feed']['entry'] ) && is_array( $arr['feed']['entry'] ) ) {
					// Check whether the feed output is the new one, first set, or the old one, second set.
					if ( $arr['feed']['link_attr']['href'] == 'https://www.googleapis.com/analytics/v2.4/management/accounts/~all/webproperties/~all/profiles' ) {
						if ( isset( $arr['feed']['entry']['id'] ) ) {
							// Single account in the feed
							if ( isset( $arr['feed']['entry']['dxp:property']['1_attr']['value'] ) )
								$ua = trim( $arr['feed']['entry']['dxp:property']['1_attr']['value'] );
							if ( isset( $arr['feed']['entry']['dxp:property']['2_attr']['value'] ) )
								$title = trim( $arr['feed']['entry']['dxp:property']['2_attr']['value'] );
							if ( !empty( $ua ) && !empty( $title ) )
								$ga_accounts[$ua] = $title;
						} else {
							// Multiple accounts in the feed
							foreach ( $arr['feed']['entry'] as $site ) {
								if ( isset( $site['dxp:property']['1_attr']['value'] ) )
									$ua = trim( $site['dxp:property']['1_attr']['value'] );
								if ( isset( $site['dxp:property']['2_attr']['value'] ) )
									$title = trim( $site['dxp:property']['2_attr']['value'] );
								if ( !empty( $ua ) && !empty( $title ) )
									$ga_accounts[$ua] = $title;
							}
						}
					} else if ( $arr['feed']['link_attr']['href'] == 'https://www.google.com/analytics/feeds/accounts/default' ) {
						foreach ( $arr['feed']['entry'] as $site ) {
							if ( isset( $site['dxp:property']['3_attr']['value'] ) )
								$ua = trim( $site['dxp:property']['3_attr']['value'] );
							if ( isset( $site['dxp:property']['1_attr']['value'] ) )
								$title = trim( $site['dxp:property']['1_attr']['value'] );
							if ( !empty( $ua ) && !empty( $title ) )
								$ga_accounts[$ua] = $title;
						}
					}
					asort( $ga_accounts );

					$select = '<select class="chzn-select" name="uastring" data-placeholder="' . __( 'Please select the correct Analytics Account', 'gawp' ) . '"  id="ga_account">';
					$select .= "\t<option></option>\n";
					foreach ( $ga_accounts as $ua => $title ) {
						$sel = selected( $ua, $currentua, false );
						$select .= "\t" . '<option ' . $sel . ' value="' . $ua . '">' . $title . ' - ' . $ua . '</option>' . "\n";
					}
					$select .= '</select>';

					$line = '<input type="hidden" name="ga_token" value="' . $token . '"/>';
					$line .= __( 'Please select the correct Analytics account to track:', 'gawp' ) . '<br/>';
					$line .= '<table class="form_table">';
					$line .= '<tr><th>' . __( 'Profile', 'gawp' ) . ':</th><td>' . $select . '</td></tr>';
					$line .= '</table>';

					$try = 1;
					if ( isset( $_GET['try'] ) )
						$try = $_GET['try'] + 1;

					if ( count( $ga_accounts ) == 0 && $try < 4 && isset( $_GET['token'] ) ) {
						$line .= '<script type="text/javascript">
													window.location="' . $this->plugin_options_url() . '&switchua=1&token=' . $token . '&try=' . $try . '";
												</script>';
					}
					$line .= __( 'Please note that if you have several profiles of the same website, it doesn\'t matter which profile you select, and in fact another profile might show as selected later. You can check whether they\'re profiles for the same site by checking if they have the same UA code. If that\'s true, tracking will be correct.', 'gawp' );
					$line .= '<br/><br/>';
					$line .= __( 'Refresh this listing or switch to another account: ', 'gawp' );
				} else {
					$line = __( 'Unfortunately, an error occurred while connecting to Google, please try again:', 'gawp' );
				}
			} else {
				$line = __( 'Unfortunately, an error occurred while connecting to Google, please try again:', 'gawp' );
			}

			$query = $this->plugin_options_url() . '&reauth=true';
			$line .= '<a class="button" href="' . $query . '">' . __( 'Re-authenticate with Google', 'gawp' ) . '</a>';
		} else {
			$line = '<input id="uastring" name="uastring" type="text" size="20" maxlength="40" value="' . $options['uastring'] . '"/><br/><a href="' . $this->plugin_options_url() . '&amp;switchua=1">' . __( 'Select another Analytics Profile &raquo;', 'gawp' ) . '</a>';
		}
		$line         = '<div id="uastring_automatic">' . $line . '</div><div style="display:none;" id="uastring_manual">' . __( 'Manually enter your UA code: ', 'gawp' ) . '<input id="uastring" name="uastring_man" type="text" size="20" maxlength="40" value="' . $options['uastring'] . '"/></div>';
		$rows         = array();
		$content      = '';
		$rows[]       = array(
			'id'      => 'uastring',
			'label'   => __( 'Analytics Profile', 'gawp' ),
			'desc'    => '<input type="checkbox" name="manual_uastring" ' . checked( $options['manual_uastring'], true, false ) . ' id="switchtomanual"/> <label for="switchtomanual">' . __( 'Manually enter your UA code', 'gawp' ) . '</label>',
			'content' => $line
		);
		$temp_content = $this->select( 'position', array( 'header' => __( 'In the header (default)', 'gawp' ), 'manual' => __( 'Insert manually', 'gawp' ) ) );
		if ( $options['theme_updated'] && $options['position'] == 'manual' ) {
			$temp_content .= '<input type="hidden" name="theme_updated" value="off"/>';
			echo '<div id="message" class="updated" style="background-color:lightgreen;border-color:green;"><p><strong>' . __( 'Notice', 'gawp' ), ':</strong> ' . __( 'You switched your theme, please make sure your Google Analytics tracking is still ok. Save your settings to make sure Google Analytics gets loaded properly.', 'gawp' ) . '</p></div>';
			remove_action( 'admin_footer', array( &$this, 'theme_switch_warning' ) );
		}
		$desc = '<div id="position_header">' . sprintf( __( 'The header is by far the best spot to place the tracking code. If you\'d rather place the code manually, switch to manual placement. For more info %sread this page%s.' ), '<a href="http://yoast.com/wordpress/google-analytics/manual-placement/">', '</a>' ) . '</div>';
		$desc .= '<div id="position_manual">' . sprintf( __( '%sFollow the instructions here%s to choose the location for your tracking code manually.', 'gawp' ), '<a href="http://yoast.com/wordpress/google-analytics/manual-placement/">', '</a>' ) . '</div>';

		$rows[] = array(
			'id'      => 'position',
			'label'   => __( 'Where should the tracking code be placed', 'gawp' ),
			'desc'    => $desc,
			'content' => $temp_content,
		);
		$rows[] = array(
			'id'      => 'trackoutbound',
			'label'   => __( 'Track outbound clicks &amp; downloads', 'gawp' ),
			'desc'    => __( 'Clicks &amp; downloads will be tracked as events, you can find these under Content &raquo; Event Tracking in your Google Analytics reports.', 'gawp' ),
			'content' => $this->checkbox( 'trackoutbound' ),
		);
		$rows[] = array(
			'id'      => 'advancedsettings',
			'label'   => __( 'Show advanced settings', 'gawp' ),
			'desc'    => __( 'Only adviced for advanced users who know their way around Google Analytics', 'gawp' ),
			'content' => $this->checkbox( 'advancedsettings' ),
		);
		$rows[] = array(
			'id'      => 'yoast_tracking',
			'label'   => __( 'Allow tracking of anonymous data', 'gawp' ),
			'desc'    => __( 'By allowing us to track anonymous data we can better help you, because we know with which WordPress configurations, themes and plugins we should test. No personal data will be submitted.', 'gawp' ),
			'content' => $this->checkbox( 'yoast_tracking' ),
		);
		$this->postbox( 'gasettings', __( 'Google Analytics Settings', 'gawp' ), $this->form_table( $rows ) . $this->save_button() );

		$rows        = array();
		$pre_content = '<p>' . __( 'Google Analytics allows you to save up to 5 custom variables on each page, and this plugin helps you make the most use of these! Check which custom variables you\'d like the plugin to save for you below. Please note that these will only be saved when they are actually available.', 'gawp' ) . '</p>';
		$pre_content .= '<p>' . __( 'If you want to start using these custom variables, go to Visitors &raquo; Custom Variables in your Analytics reports.', 'gawp' ) . '</p>';
		$rows[] = array(
			'id'      => 'cv_loggedin',
			'label'   => __( 'Logged in Users', 'gawp' ),
			'desc'    => __( 'Allows you to easily remove logged in users from your reports, or to segment by different user roles. The users primary role will be logged.', 'gawp' ),
			'content' => $this->checkbox( 'cv_loggedin' ),
		);
		$rows[] = array(
			'id'      => 'cv_post_type',
			'label'   => __( 'Post type', 'gawp' ),
			'desc'    => __( 'Allows you to see pageviews per post type, especially useful if you use multiple custom post types.', 'gawp' ),
			'content' => $this->checkbox( 'cv_post_type' ),
		);
		$rows[] = array(
			'id'      => 'cv_authorname',
			'label'   => __( 'Author Name', 'gawp' ),
			'desc'    => __( 'Allows you to see pageviews per author.', 'gawp' ),
			'content' => $this->checkbox( 'cv_authorname' ),
		);
		$rows[] = array(
			'id'      => 'cv_tags',
			'label'   => __( 'Tags', 'gawp' ),
			'desc'    => __( 'Allows you to see pageviews per tags using advanced segments.', 'gawp' ),
			'content' => $this->checkbox( 'cv_tags' ),
		);
		$rows[] = array(
			'id'      => 'cv_year',
			'label'   => __( 'Publication year', 'gawp' ),
			'desc'    => __( 'Allows you to see pageviews per year of publication, showing you if your old posts still get traffic.', 'gawp' ),
			'content' => $this->checkbox( 'cv_year' ),
		);
		$rows[] = array(
			'id'      => 'cv_category',
			'label'   => __( 'Single Category', 'gawp' ),
			'desc'    => __( 'Allows you to see pageviews per category, works best when each post is in only one category.', 'gawp' ),
			'content' => $this->checkbox( 'cv_category' ),
		);
		$rows[] = array(
			'id'      => 'cv_all_categories',
			'label'   => __( 'All Categories', 'gawp' ),
			'desc'    => __( 'Allows you to see pageviews per category using advanced segments, should be used when you use multiple categories per post.', 'gawp' ),
			'content' => $this->checkbox( 'cv_all_categories' ),
		);

		$modules['Custom Variables'] = 'customvarsettings';
		$this->postbox( 'customvarsettings', __( 'Custom Variables Settings', 'gawp' ), $pre_content . $this->form_table( $rows ) . $this->save_button() );

		$rows   = array();
		$rows[] = array(
			'id'      => 'ignore_userlevel',
			'label'   => __( 'Ignore users', 'gawp' ),
			'desc'    => __( 'Users of the role you select and higher will be ignored, so if you select Editor, all Editors and Administrators will be ignored.', 'gawp' ),
			'content' => $this->select( 'ignore_userlevel', array(
				'11' => __( 'Ignore no-one', 'gawp' ),
				'8'  => __( 'Administrator', 'gawp' ),
				'5'  => __( 'Editor', 'gawp' ),
				'2'  => __( 'Author', 'gawp' ),
				'1'  => __( 'Contributor', 'gawp' ),
				'0'  => __( 'Subscriber (ignores all logged in users)', 'gawp' ),
			) ),
		);
		$rows[] = array(
			'id'      => 'outboundpageview',
			'label'   => __( 'Track outbound clicks as pageviews', 'gawp' ),
			'desc'    => __( 'You do not need to enable this to enable outbound click tracking, this changes the default behavior of tracking clicks as events to tracking them as pageviews. This is therefore not recommended, as this would skew your statistics, but <em>is</em> sometimes necessary when you need to set outbound clicks as goals.', 'gawp' ),
			'content' => $this->checkbox( 'outboundpageview' ),
		);
		$rows[] = array(
			'id'      => 'downloadspageview',
			'label'   => __( 'Track downloads as pageviews', 'gawp' ),
			'desc'    => __( 'Not recommended, as this would skew your statistics, but it does make it possible to track downloads as goals.', 'gawp' ),
			'content' => $this->checkbox( 'downloadspageview' ),
		);
		$rows[] = array(
			'id'      => 'dlextensions',
			'label'   => __( 'Extensions of files to track as downloads', 'gawp' ),
			'content' => $this->textinput( 'dlextensions' ),
		);
		if ( $options['outboundpageview'] ) {
			$rows[] = array(
				'id'      => 'trackprefix',
				'label'   => __( 'Prefix to use in Analytics before the tracked pageviews', 'gawp' ),
				'desc'    => __( 'This prefix is used before all pageviews, they are then segmented automatically after that. If nothing is entered here, <code>/yoast-ga/</code> is used.', 'gawp' ),
				'content' => $this->textinput( 'trackprefix' ),
			);
		}
		$rows[]                       = array(
			'id'      => 'domainorurl',
			'label'   => __( 'Track full URL of outbound clicks or just the domain', 'gawp' ),
			'content' => $this->select( 'domainorurl', array(
					'domain' => __( 'Just the domain', 'gawp' ),
					'url'    => __( 'Track the complete URL', 'gawp' ),
				)
			),
		);
		$rows[]                       = array(
			'id'      => 'domain',
			'label'   => __( 'Subdomain Tracking', 'gawp' ),
			'desc'    => sprintf( __( 'This allows you to set the domain that\'s set by %s<code>setDomainName</code>%s for tracking subdomains, if empty this will not be set.', 'gawp' ), '<a href="http://code.google.com/apis/analytics/docs/gaJS/gaJSApiDomainDirectory.html#_gat.GA_Tracker_._setDomainName">', '</a>' ),
			'content' => $this->textinput( 'domain' ),
		);
		$rows[]                       = array(
			'id'      => 'trackcrossdomain',
			'label'   => __( 'Enable Cross Domain Tracking', 'gawp' ),
			'desc'    => sprintf( __( 'This allows you to enable %sCross-Domain Tracking%s for this site.  When endabled <code>_setAllowLinker:</code> will be enabled if it is not already.', 'gawp' ), '<a href="http://code.google.com/apis/analytics/docs/tracking/gaTrackingSite.html">', '</a>' ),
			'content' => $this->checkbox( 'trackcrossdomain' ),
		);
		$rows[]                       = array(
			'id'      => 'primarycrossdomain',
			'label'   => __( 'Cross-Domain Tracking, Primary Domain', 'gawp' ),
			'desc'    => sprintf( __( 'Set the primary domain used in %s<code>setDomainName</code>%s for cross domain tracking (eg. <code>example-petstore.com</code> ), if empty this will default to your configured Home URL.', 'gawp' ), '<a href="http://code.google.com/apis/analytics/docs/gaJS/gaJSApiDomainDirectory.html#_gat.GA_Tracker_._setDomainName">', '</a>' ),
			'content' => $this->textinput( 'primarycrossdomain' ),
		);
		$rows[]                       = array(
			'id'      => 'othercrossdomains',
			'label'   => __( 'Cross-Domain Tracking, Other Domains', 'gawp' ),
			'desc'    => __( 'All links to these domains will have the <a href="http://code.google.com/apis/analytics/docs/tracking/gaTrackingSite.html#multipleDomains"><code>_link</code></a> code automatically attached.  Separate domains/sub-domains with commas (eg. <code>dogs.example-petstore.com, cats.example-petstore.com</code>)', 'gawp' ),
			'content' => $this->textinput( 'othercrossdomains' ),
		);
		$rows[]                       = array(
			'id'      => 'customcode',
			'label'   => __( 'Custom Code', 'gawp' ),
			'desc'    => __( 'Not for the average user: this allows you to add a line of code, to be added before the <code>trackPageview</code> call.', 'gawp' ),
			'content' => $this->textinput( 'customcode' ),
		);
		$rows[]                       = array(
			'id'      => 'trackadsense',
			'label'   => __( 'Track AdSense', 'gawp' ),
			'desc'    => __( 'This requires integration of your Analytics and AdSense account, for help, <a href="http://google.com/support/analytics/bin/answer.py?answer=92625">look here</a>.', 'gawp' ),
			'content' => $this->checkbox( 'trackadsense' ),
		);
		$rows[]                       = array(
			'id'      => 'gajslocalhosting',
			'label'   => __( 'Host ga.js locally', 'gawp' ),
			'content' => $this->checkbox( 'gajslocalhosting' ) . '<div id="localhostingbox">
											' . __( 'You have to provide a URL to your ga.js file:', 'gawp' ) . '
											<input type="text" name="gajsurl" size="30" value="' . $options['gajsurl'] . '"/>
										</div>',
			'desc'    => __( 'For some reasons you might want to use a locally hosted ga.js file, or another ga.js file, check the box and then please enter the full URL including http here.', 'gawp' )
		);
		$rows[]                       = array(
			'id'      => 'extrase',
			'label'   => __( 'Track extra Search Engines', 'gawp' ),
			'content' => $this->checkbox( 'extrase' ) . '<div id="extrasebox">
											' . __( 'You can provide a custom URL to the extra search engines file if you want:', 'gawp' ) . '
											<input type="text" name="extraseurl" size="30" value="' . $options['extraseurl'] . '"/>
										</div>',
		);
		$rows[]                       = array(
			'id'      => 'rsslinktagging',
			'label'   => __( 'Tag links in RSS feed with campaign variables', 'gawp' ),
			'desc'    => __( 'Do not use this feature if you use FeedBurner, as FeedBurner can do this automatically, and better than this plugin can. Check <a href="http://www.google.com/support/feedburner/bin/answer.py?hl=en&amp;answer=165769">this help page</a> for info on how to enable this feature in FeedBurner.', 'gawp' ),
			'content' => $this->checkbox( 'rsslinktagging' ),
		);
		$rows[]                       = array(
			'id'      => 'trackregistration',
			'label'   => __( 'Add tracking to the login and registration forms', 'gawp' ),
			'content' => $this->checkbox( 'trackregistration' ),
		);
		$rows[]                       = array(
			'id'      => 'trackcommentform',
			'label'   => __( 'Add tracking to the comment forms', 'gawp' ),
			'content' => $this->checkbox( 'trackcommentform' ),
		);
		$rows[]                       = array(
			'id'      => 'allowanchor',
			'label'   => __( 'Use # instead of ? for Campaign tracking', 'gawp' ),
			'desc'    => __( 'This adds a <code><a href="http://code.google.com/apis/analytics/docs/gaJSApiCampaignTracking.html#_gat.GA_Tracker_._setAllowAnchor">_setAllowAnchor</a></code> call to your tracking code, and makes RSS link tagging use a # as well.', 'gawp' ),
			'content' => $this->checkbox( 'allowanchor' ),
		);
		$rows[]                       = array(
			'id'      => 'allowlinker',
			'label'   => __( 'Add <code>_setAllowLinker</code>', 'gawp' ),
			'desc'    => __( 'This adds a <code><a href="http://code.google.com/apis/analytics/docs/gaJS/gaJSApiDomainDirectory.html#_gat.GA_Tracker_._setAllowLinker">_setAllowLinker</a></code> call to your tracking code,  allowing you to use <code>_link</code> and related functions.', 'gawp' ),
			'content' => $this->checkbox( 'allowlinker' ),
		);
		$rows[]                       = array(
			'id'      => 'allowhash',
			'label'   => __( 'Set <code>_setAllowHash</code> to false', 'gawp' ),
			'desc'    => __( 'This sets <code><a href="http://code.google.com/apis/analytics/docs/gaJS/gaJSApiDomainDirectory.html#_gat.GA_Tracker_._setAllowHash">_setAllowHash</a></code> to false, allowing you to track subdomains etc.', 'gawp' ),
			'content' => $this->checkbox( 'allowhash' ),
		);
		$rows[]                       = array(
			'id'      => 'anonymizeip',
			'label'   => __( 'Anonymize IP\'s', 'gawp' ),
			'desc'    => __( 'This adds <code><a href="http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gat.html#_gat._anonymizeIp">_anonymizeIp</a></code>, telling Google Analytics to anonymize the information sent by the tracker objects by removing the last octet of the IP address prior to its storage.', 'gawp' ),
			'content' => $this->checkbox( 'anonymizeip' ),
		);
		$modules['Advanced Settings'] = 'advancedgasettings';
		$this->postbox( 'advancedgasettings', __( 'Advanced Settings', 'gawp' ), $this->form_table( $rows ) . $this->save_button() );

		$rows                              = array();
		$rows[]                            = array(
			'id'      => 'internallink',
			'label'   => __( 'Internal links to track as outbound', 'gawp' ),
			'desc'    => __( 'If you want to track all internal links that begin with <code>/out/</code>, enter <code>/out/</code> in the box above. If you have multiple prefixes you can separate them with comma\'s: <code>/out/,/recommends/</code>', 'gawp' ),
			'content' => $this->textinput( 'internallink' ),
		);
		$rows[]                            = array(
			'id'      => 'internallinklabel',
			'label'   => __( 'Label to use', 'gawp' ),
			'desc'    => __( 'The label to use for these links, this will be added to where the click came from, so if the label is "aff", the label for a click from the content of an article becomes "outbound-article-aff".', 'gawp' ),
			'content' => $this->textinput( 'internallinklabel' ),
		);
		$modules['Internal Link Tracking'] = 'internallinktracking';
		$this->postbox( 'internallinktracking', __( 'Internal Links to Track as Outbound', 'gawp' ), $this->form_table( $rows ) . $this->save_button() );

		if ( defined( 'WPSC_VERSION' ) ) {
			$pre_content = __( 'The WordPress e-Commerce plugin has been detected. This plugin can automatically add transaction tracking for you. To do that, <a href="http://yoast.com/wordpress/google-analytics/enable-ecommerce/">enable e-commerce for your reports in Google Analytics</a> and then check the box below.', 'gawp' );
			$rows        = array();
			$rows[]      = array(
				'id'      => 'wpec_tracking',
				'label'   => __( 'Enable transaction tracking', 'gawp' ),
				'content' => $this->checkbox( 'wpec_tracking' ),
			);
			$this->postbox( 'wpecommerce', __( 'WordPress e-Commerce Settings', 'gawp' ), $pre_content . $this->form_table( $rows ) . $this->save_button() );
			$modules['WordPress e-Commerce'] = 'wpecommerce';
		}

		global $Shopp;
		if ( isset( $Shopp ) ) {
			$pre_content = __( 'The Shopp e-Commerce plugin has been detected. This plugin can automatically add transaction tracking for you. To do that, <a href="http://www.google.com/support/googleanalytics/bin/answer.py?hl=en&amp;answer=55528">enable e-commerce for your reports in Google Analytics</a> and then check the box below.', 'gawp' );
			$rows        = array();
			$rows[]      = array(
				'id'      => 'shopp_tracking',
				'label'   => __( 'Enable transaction tracking', 'gawp' ),
				'content' => $this->checkbox( 'shopp_tracking' ),
			);
			$this->postbox( 'shoppecommerce', __( 'Shopp e-Commerce Settings', 'gawp' ), $pre_content . $this->form_table( $rows ) . $this->save_button() );
			$modules['Shopp'] = 'shoppecommerce';
		}
		$pre_content = '<p>' . sprintf( __( 'If you want to confirm that tracking on your blog is working as it should, enable this option and check the console in %sFirebug%s (for Firefox), %sFirebug Lite%s (for other browsers) or Chrome &amp; Safari\'s Web Inspector. Be absolutely sure to disable debugging afterwards, as it is slower than normal tracking.', 'gawp' ), '<a href="http://getfirebug.com/">', '</a>', '<a href="http://getfirebug.com/firebuglite">', '</a>' ) . '</p>';
		$pre_content .= '<p><strong>' . __( 'Note', 'gawp' ) . '</strong>: ' . __( 'the debugging and firebug scripts are only loaded for admins.', 'gawp' ) . '</p>';
		$rows   = array();
		$rows[] = array(
			'id'      => 'debug',
			'label'   => __( 'Enable debug mode', 'gawp' ),
			'content' => $this->checkbox( 'debug' ),
		);
		$rows[] = array(
			'id'      => 'firebuglite',
			'label'   => __( 'Enable Firebug Lite', 'gawp' ),
			'content' => $this->checkbox( 'firebuglite' ),
		);
		$this->postbox( 'debugmode', __( 'Debug Mode', 'gawp' ), $pre_content . $this->form_table( $rows ) . $this->save_button() );
		$modules['Debug Mode'] = 'debugmode';
		?>
    </form>
    <form action="<?php echo $this->plugin_options_url(); ?>" method="post"
          onsubmit="javascript:return(confirm('<?php _e( 'Do you really want to reset all settings?', 'gawp' ); ?>'));">
        <input type="hidden" name="reset" value="true"/>
        <input type="hidden" name="plugin" value="google-analytics-for-wordpress"/>

        <div class="submit"><input type="submit" value="<?php _e( 'Reset All Settings &raquo;', 'gawp' ); ?>'"/></div>
    </form>
    </div>
    </div>
    </div>
    <div class="postbox-container side" style="width:261px;">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
				<?php
				$this->postbox( 'spread', '<strong>' . __( 'Help Spread the Word!', 'gawp' ) . '</strong>',
					'<ul>'
						. '<li><a href="http://wordpress.org/extend/plugins/google-analytics-for-wordpress/">' . __( 'Rate the plugin 5★ on WordPress.org', 'gawp' ) . '</a></li>'
						. '<li><a href="http://wordpress.org/tags/google-analytics-for-wordpress">' . __( 'Help out other users in the forums', 'gawp' ) . '</a></li>'
						. '<li>' . sprintf( __( 'Blog about it & link to the %1$splugin page%2$s' ), '<a href="http://yoast.com/wordpress/google-analytics/#utm_source=wpadmin&utm_medium=sidebanner&utm_term=link&utm_campaign=wpgaplugin">', '</a>' ) . '</li></ul>' );
				?>
                <a target="_blank"
                   href="https://yoast.com/hire-us/website-review/#utm_source=gawp-config&utm_medium=banner&utm_campaign=website-review-banner"><img
                        src="<?php echo GAWP_URL; ?>images/banner-website-review.png" alt="Website Review banner"/></a>
            </div>
            <br/><br/><br/>
        </div>
    </div>
    </div>
	<?php
	}

	function set_defaults() {
		$options = array(
			'advancedsettings'   => false,
			'allowanchor'        => false,
			'allowhash'          => false,
			'allowlinker'        => false,
			'anonymizeip'        => false,
			'customcode'         => '',
			'cv_loggedin'        => false,
			'cv_authorname'      => false,
			'cv_category'        => false,
			'cv_all_categories'  => false,
			'cv_tags'            => false,
			'cv_year'            => false,
			'cv_post_type'       => false,
			'debug'              => false,
			'dlextensions'       => 'doc,exe,js,pdf,ppt,tgz,zip,xls',
			'domain'             => '',
			'domainorurl'        => 'domain',
			'extrase'            => false,
			'extraseurl'         => '',
			'firebuglite'        => false,
			'ga_token'           => '',
			'ga_api_responses'   => array(),
			'gajslocalhosting'   => false,
			'gajsurl'            => '',
			'ignore_userlevel'   => '11',
			'internallink'       => false,
			'internallinklabel'  => '',
			'outboundpageview'   => false,
			'downloadspageview'  => false,
			'othercrossdomains'  => '',
			'position'           => 'footer',
			'primarycrossdomain' => '',
			'theme_updated'      => false,
			'trackcommentform'   => true,
			'trackcrossdomain'   => false,
			'trackadsense'       => false,
			'trackoutbound'      => true,
			'trackregistration'  => false,
			'rsslinktagging'     => true,
			'uastring'           => '',
			'version'            => GAWP_VERSION,
		);
		update_option( $this->optionname, $options );
		return $options;
	}

	function warning() {
		$options = get_option( $this->optionname );
		if ( !isset( $options['uastring'] ) || empty( $options['uastring'] ) ) {
			echo "<div id='message' class='error'><p><strong>" . __( "Google Analytics is not active.", 'gawp' ) . "</strong> " . sprintf( __( "You must %sselect which Analytics Profile to track%s before it can work.", 'gawp' ), "<a href='" . $this->plugin_options_url() . "'>", "</a>" ) . "</p></div>";
		}
	} // end warning()


	function authenticate() {
		if ( isset( $_REQUEST['ga_oauth_callback'] ) ) {
			$o = get_option( $this->optionname );
			if ( isset( $o['gawp_oauth']['oauth_token'] ) && $o['gawp_oauth']['oauth_token'] == $_REQUEST['oauth_token'] ) {
				$gdata = new WP_GData(
					array(
						'scope'              => 'https://www.google.com/analytics/feeds/',
						'xoauth_displayname' => 'Google Analytics for WordPress by Yoast'
					),
					$o['gawp_oauth']['oauth_token'],
					$o['gawp_oauth']['oauth_token_secret']
				);

				$o['gawp_oauth']['access_token'] = $gdata->get_access_token( $_REQUEST['oauth_verifier'] );
				unset( $o['gawp_oauth']['oauth_token'] );
				unset( $o['gawp_oauth']['oauth_token_secret'] );
				$o['ga_token'] = $o['gawp_oauth']['access_token']['oauth_token'];
			}

			update_option( $this->optionname, $o );

			wp_redirect( menu_page_url( $this->hook, false ) );
			exit;
		}

		if ( !empty( $_GET['reauth'] ) ) {
			$gdata = new WP_GData(
				array(
					'scope'              => 'https://www.google.com/analytics/feeds/',
					'xoauth_displayname' => 'Google Analytics for WordPress by Yoast'
				)
			);

			$oauth_callback = add_query_arg( array( 'ga_oauth_callback' => 1 ), menu_page_url( 'google-analytics-for-wordpress', false ) );
			$request_token  = $gdata->get_request_token( $oauth_callback );

			$options = get_option( $this->optionname );
			unset( $options['ga_token'] );
			unset( $options['gawp_oauth']['access_token'] );
			$options['gawp_oauth']['oauth_token']        = $request_token['oauth_token'];
			$options['gawp_oauth']['oauth_token_secret'] = $request_token['oauth_token_secret'];
			update_option( $this->optionname, $options );

			wp_redirect( $gdata->get_authorize_url( $request_token ) );
			exit;
		}

	} //end reauthenticate()
} // end class GA_Admin

$ga_admin = new GA_Admin();
