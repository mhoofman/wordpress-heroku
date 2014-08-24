<?php
/**
 * Facilitates adding of the WordPress editor as used on the Write and Edit screens.
 *
 * @package WordPress
 * @since 3.3.0
 *
 * Private, not included by default. See wp_editor() in wp-includes/general-template.php.
 */

final class _WP_Editors {
	public static $mce_locale;

	private static $mce_settings = array();
	private static $qt_settings = array();
	private static $plugins = array();
	private static $qt_buttons = array();
	private static $ext_plugins;
	private static $baseurl;
	private static $first_init;
	private static $this_tinymce = false;
	private static $this_quicktags = false;
	private static $has_tinymce = false;
	private static $has_quicktags = false;
	private static $has_medialib = false;
	private static $editor_buttons_css = true;
	private static $drag_drop_upload = false;

	private function __construct() {}

	/**
	 * Parse default arguments for the editor instance.
	 *
	 * @param string $editor_id ID for the current editor instance.
	 * @param array  $settings {
	 *     Array of editor arguments.
	 *
	 *     @type bool       $wpautop           Whether to use wpautop(). Default true.
	 *     @type bool       $media_buttons     Whether to show the Add Media/other media buttons.
	 *     @type string     $default_editor    When both TinyMCE and Quicktags are used, set which
	 *                                         editor is shown on page load. Default empty.
	 *     @type bool       $drag_drop_upload  Whether to enable drag & drop on the editor uploading. Default false.
	 *                                         Requires the media modal.
	 *     @type string     $textarea_name     Give the textarea a unique name here. Square brackets
	 *                                         can be used here. Default $editor_id.
	 *     @type int        $textarea_rows     Number rows in the editor textarea. Default 20.
	 *     @type string|int $tabindex          Tabindex value to use. Default empty.
	 *     @type string     $tabfocus_elements The previous and next element ID to move the focus to
	 *                                         when pressing the Tab key in TinyMCE. Defualt ':prev,:next'.
	 *     @type string     $editor_css        Intended for extra styles for both Visual and Text editors.
	 *                                         Should include <style> tags, and can use "scoped". Default empty.
	 *     @type string     $editor_class      Extra classes to add to the editor textarea elemen. Default empty.
	 *     @type bool       $teeny             Whether to output the minimal editor config. Examples include
	 *                                         Press This and the Comment editor. Default false.
	 *     @type bool       $dfw               Whether to replace the default fullscreen with "Distraction Free
	 *                                         Writing". DFW requires specific DOM elements and css). Default false.
	 *     @type bool|array $tinymce           Whether to load TinyMCE. Can be used to pass settings directly to
	 *                                         TinyMCE using an array. Default true.
	 *     @type bool|array $quicktags         Whether to load Quicktags. Can be used to pass settings directly to
	 *                                         Quicktags using an array. Default true.
	 * }
	 * @return array Parsed arguments array.
	 */
	public static function parse_settings( $editor_id, $settings ) {
		$set = wp_parse_args( $settings,  array(
			'wpautop'           => true,
			'media_buttons'     => true,
			'default_editor'    => '',
			'drag_drop_upload'  => false,
			'textarea_name'     => $editor_id,
			'textarea_rows'     => 20,
			'tabindex'          => '',
			'tabfocus_elements' => ':prev,:next',
			'editor_css'        => '',
			'editor_class'      => '',
			'teeny'             => false,
			'dfw'               => false,
			'tinymce'           => true,
			'quicktags'         => true
		) );

		self::$this_tinymce = ( $set['tinymce'] && user_can_richedit() );

		if ( self::$this_tinymce ) {
			if ( false !== strpos( $editor_id, '[' ) ) {
				self::$this_tinymce = false;
				_deprecated_argument( 'wp_editor()', '3.9', 'TinyMCE editor IDs cannot have brackets.' );
			}
		}

		self::$this_quicktags = (bool) $set['quicktags'];

		if ( self::$this_tinymce )
			self::$has_tinymce = true;

		if ( self::$this_quicktags )
			self::$has_quicktags = true;

		if ( empty( $set['editor_height'] ) )
			return $set;

		if ( 'content' === $editor_id ) {
			// A cookie (set when a user resizes the editor) overrides the height.
			$cookie = (int) get_user_setting( 'ed_size' );

			// Upgrade an old TinyMCE cookie if it is still around, and the new one isn't.
			if ( ! $cookie && isset( $_COOKIE['TinyMCE_content_size'] ) ) {
				parse_str( $_COOKIE['TinyMCE_content_size'], $cookie );
 				$cookie = $cookie['ch'];
			}

			if ( $cookie )
				$set['editor_height'] = $cookie;
		}

		if ( $set['editor_height'] < 50 )
			$set['editor_height'] = 50;
		elseif ( $set['editor_height'] > 5000 )
			$set['editor_height'] = 5000;

		return $set;
	}

	/**
	 * Outputs the HTML for a single instance of the editor.
	 *
	 * @param string $content The initial content of the editor.
	 * @param string $editor_id ID for the textarea and TinyMCE and Quicktags instances (can contain only ASCII letters and numbers).
	 * @param array $settings See the _parse_settings() method for description.
	 */
	public static function editor( $content, $editor_id, $settings = array() ) {

		$set = self::parse_settings( $editor_id, $settings );
		$editor_class = ' class="' . trim( $set['editor_class'] . ' wp-editor-area' ) . '"';
		$tabindex = $set['tabindex'] ? ' tabindex="' . (int) $set['tabindex'] . '"' : '';
		$switch_class = 'html-active';
		$toolbar = $buttons = $autocomplete = '';

		if ( $set['drag_drop_upload'] ) {
			self::$drag_drop_upload = true;
		}

		if ( ! empty( $set['editor_height'] ) )
			$height = ' style="height: ' . $set['editor_height'] . 'px"';
		else
			$height = ' rows="' . $set['textarea_rows'] . '"';

		if ( !current_user_can( 'upload_files' ) )
			$set['media_buttons'] = false;

		if ( ! self::$this_quicktags && self::$this_tinymce ) {
			$switch_class = 'tmce-active';
			$autocomplete = ' autocomplete="off"';
		} elseif ( self::$this_quicktags && self::$this_tinymce ) {
			$default_editor = $set['default_editor'] ? $set['default_editor'] : wp_default_editor();
			$autocomplete = ' autocomplete="off"';

			// 'html' is used for the "Text" editor tab.
			if ( 'html' === $default_editor ) {
				add_filter('the_editor_content', 'wp_htmledit_pre');
				$switch_class = 'html-active';
			} else {
				add_filter('the_editor_content', 'wp_richedit_pre');
				$switch_class = 'tmce-active';
			}

			$buttons .= '<a id="' . $editor_id . '-html" class="wp-switch-editor switch-html" onclick="switchEditors.switchto(this);">' . _x( 'Text', 'Name for the Text editor tab (formerly HTML)' ) . "</a>\n";
			$buttons .= '<a id="' . $editor_id . '-tmce" class="wp-switch-editor switch-tmce" onclick="switchEditors.switchto(this);">' . __('Visual') . "</a>\n";
		}

		$wrap_class = 'wp-core-ui wp-editor-wrap ' . $switch_class;

		if ( $set['dfw'] ) {
			$wrap_class .= ' has-dfw';
		}

		echo '<div id="wp-' . $editor_id . '-wrap" class="' . $wrap_class . '">';

		if ( self::$editor_buttons_css ) {
			wp_print_styles('editor-buttons');
			self::$editor_buttons_css = false;
		}

		if ( !empty($set['editor_css']) )
			echo $set['editor_css'] . "\n";

		if ( !empty($buttons) || $set['media_buttons'] ) {
			echo '<div id="wp-' . $editor_id . '-editor-tools" class="wp-editor-tools hide-if-no-js">';

			if ( $set['media_buttons'] ) {
				self::$has_medialib = true;

				if ( !function_exists('media_buttons') )
					include(ABSPATH . 'wp-admin/includes/media.php');

				echo '<div id="wp-' . $editor_id . '-media-buttons" class="wp-media-buttons">';

				/**
				 * Fires after the default media button(s) are displayed.
				 *
				 * @since 2.5.0
				 *
				 * @param string $editor_id Unique editor identifier, e.g. 'content'.
				 */
				do_action( 'media_buttons', $editor_id );
				echo "</div>\n";
			}

			echo '<div class="wp-editor-tabs">' . $buttons . "</div>\n";
			echo "</div>\n";
		}

		/**
		 * Filter the HTML markup output that displays the editor.
		 *
		 * @since 2.1.0
		 *
		 * @param string $output Editor's HTML markup.
		 */
		$the_editor = apply_filters( 'the_editor', '<div id="wp-' . $editor_id . '-editor-container" class="wp-editor-container">' .
			'<textarea' . $editor_class . $height . $tabindex . $autocomplete . ' cols="40" name="' . $set['textarea_name'] . '" ' .
			'id="' . $editor_id . '">%s</textarea></div>' );

		/**
		 * Filter the default editor content.
		 *
		 * @since 2.1.0
		 *
		 * @param string $content Default editor content.
		 */
		$content = apply_filters( 'the_editor_content', $content );

		printf( $the_editor, $content );
		echo "\n</div>\n\n";

		self::editor_settings($editor_id, $set);
	}

	public static function editor_settings($editor_id, $set) {
		$first_run = false;

		if ( empty(self::$first_init) ) {
			if ( is_admin() ) {
				add_action( 'admin_print_footer_scripts', array( __CLASS__, 'editor_js' ), 50 );
				add_action( 'admin_print_footer_scripts', array( __CLASS__, 'enqueue_scripts' ), 1 );
			} else {
				add_action( 'wp_print_footer_scripts', array( __CLASS__, 'editor_js' ), 50 );
				add_action( 'wp_print_footer_scripts', array( __CLASS__, 'enqueue_scripts' ), 1 );
			}
		}

		if ( self::$this_quicktags ) {

			$qtInit = array(
				'id' => $editor_id,
				'buttons' => ''
			);

			if ( is_array($set['quicktags']) )
				$qtInit = array_merge($qtInit, $set['quicktags']);

			if ( empty($qtInit['buttons']) )
				$qtInit['buttons'] = 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close';

			if ( $set['dfw'] )
				$qtInit['buttons'] .= ',fullscreen';

			/**
			 * Filter the Quicktags settings.
			 *
			 * @since 3.3.0
			 *
			 * @param array  $qtInit    Quicktags settings.
			 * @param string $editor_id The unique editor ID, e.g. 'content'.
			 */
			$qtInit = apply_filters( 'quicktags_settings', $qtInit, $editor_id );

			self::$qt_settings[$editor_id] = $qtInit;

			self::$qt_buttons = array_merge( self::$qt_buttons, explode(',', $qtInit['buttons']) );
		}

		if ( self::$this_tinymce ) {

			if ( empty( self::$first_init ) ) {
				self::$baseurl = includes_url( 'js/tinymce' );

				$mce_locale = get_locale();
				self::$mce_locale = $mce_locale = empty( $mce_locale ) ? 'en' : strtolower( substr( $mce_locale, 0, 2 ) ); // ISO 639-1

				/** This filter is documented in wp-admin/includes/media.php */
				$no_captions = (bool) apply_filters( 'disable_captions', '' );
				$first_run = true;
				$ext_plugins = '';

				if ( $set['teeny'] ) {

					/**
					 * Filter the list of teenyMCE plugins.
					 *
					 * @since 2.7.0
					 *
					 * @param array  $plugins   An array of teenyMCE plugins.
					 * @param string $editor_id Unique editor identifier, e.g. 'content'.
					 */
					self::$plugins = $plugins = apply_filters( 'teeny_mce_plugins', array( 'fullscreen', 'image', 'wordpress', 'wpeditimage', 'wplink' ), $editor_id );
				} else {

					/**
					 * Filter the list of TinyMCE external plugins.
					 *
					 * The filter takes an associative array of external plugins for
					 * TinyMCE in the form 'plugin_name' => 'url'.
					 *
					 * The url should be absolute, and should include the js filename
					 * to be loaded. For example:
					 * 'myplugin' => 'http://mysite.com/wp-content/plugins/myfolder/mce_plugin.js'.
					 *
					 * If the external plugin adds a button, it should be added with
					 * one of the 'mce_buttons' filters.
					 *
					 * @since 2.5.0
					 *
					 * @param array $external_plugins An array of external TinyMCE plugins.
					 */
					$mce_external_plugins = apply_filters( 'mce_external_plugins', array() );

					$plugins = array(
						'charmap',
						'hr',
						'media',
						'paste',
						'tabfocus',
						'textcolor',
						'fullscreen',
						'wordpress',
						'wpeditimage',
						'wpgallery',
						'wplink',
						'wpdialogs',
						'wpview',
					);

					if ( ! self::$has_medialib ) {
						$plugins[] = 'image';
					}

					/**
					 * Filter the list of default TinyMCE plugins.
					 *
					 * The filter specifies which of the default plugins included
					 * in WordPress should be added to the TinyMCE instance.
					 *
					 * @since 3.3.0
					 *
					 * @param array $plugins An array of default TinyMCE plugins.
					 */
					$plugins = array_unique( apply_filters( 'tiny_mce_plugins', $plugins ) );

					if ( ( $key = array_search( 'spellchecker', $plugins ) ) !== false ) {
						// Remove 'spellchecker' from the internal plugins if added with 'tiny_mce_plugins' filter to prevent errors.
						// It can be added with 'mce_external_plugins'.
						unset( $plugins[$key] );
					}

					if ( ! empty( $mce_external_plugins ) ) {

						/**
						 * Filter the translations loaded for external TinyMCE 3.x plugins.
						 *
						 * The filter takes an associative array ('plugin_name' => 'path')
						 * where 'path' is the include path to the file.
						 *
						 * The language file should follow the same format as wp_mce_translation(),
						 * and should define a variable ($strings) that holds all translated strings.
						 *
						 * @since 2.5.0
						 *
						 * @param array $translations Translations for external TinyMCE plugins.
						 */
						$mce_external_languages = apply_filters( 'mce_external_languages', array() );

						$loaded_langs = array();
						$strings = '';

						if ( ! empty( $mce_external_languages ) ) {
							foreach ( $mce_external_languages as $name => $path ) {
								if ( @is_file( $path ) && @is_readable( $path ) ) {
									include_once( $path );
									$ext_plugins .= $strings . "\n";
									$loaded_langs[] = $name;
								}
							}
						}

						foreach ( $mce_external_plugins as $name => $url ) {
							if ( in_array( $name, $plugins, true ) ) {
								unset( $mce_external_plugins[ $name ] );
								continue;
							}

							$url = set_url_scheme( $url );
							$mce_external_plugins[ $name ] = $url;
							$plugurl = dirname( $url );
							$strings = $str1 = $str2 = '';

							// Try to load langs/[locale].js and langs/[locale]_dlg.js
							if ( ! in_array( $name, $loaded_langs, true ) ) {
								$path = str_replace( content_url(), '', $plugurl );
								$path = WP_CONTENT_DIR . $path . '/langs/';

								if ( function_exists('realpath') )
									$path = trailingslashit( realpath($path) );

								if ( @is_file( $path . $mce_locale . '.js' ) )
									$strings .= @file_get_contents( $path . $mce_locale . '.js' ) . "\n";

								if ( @is_file( $path . $mce_locale . '_dlg.js' ) )
									$strings .= @file_get_contents( $path . $mce_locale . '_dlg.js' ) . "\n";

								if ( 'en' != $mce_locale && empty( $strings ) ) {
									if ( @is_file( $path . 'en.js' ) ) {
										$str1 = @file_get_contents( $path . 'en.js' );
										$strings .= preg_replace( '/([\'"])en\./', '$1' . $mce_locale . '.', $str1, 1 ) . "\n";
									}

									if ( @is_file( $path . 'en_dlg.js' ) ) {
										$str2 = @file_get_contents( $path . 'en_dlg.js' );
										$strings .= preg_replace( '/([\'"])en\./', '$1' . $mce_locale . '.', $str2, 1 ) . "\n";
									}
								}

								if ( ! empty( $strings ) )
									$ext_plugins .= "\n" . $strings . "\n";
							}

							$ext_plugins .= 'tinyMCEPreInit.load_ext("' . $plugurl . '", "' . $mce_locale . '");' . "\n";
							$ext_plugins .= 'tinymce.PluginManager.load("' . $name . '", "' . $url . '");' . "\n";
						}
					}
				}

				if ( $set['dfw'] )
					$plugins[] = 'wpfullscreen';

				self::$plugins = $plugins;
				self::$ext_plugins = $ext_plugins;

				self::$first_init = array(
					'theme' => 'modern',
					'skin' => 'lightgray',
					'language' => self::$mce_locale,
					'formats' => "{
						alignleft: [
							{selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'left'}},
							{selector: 'img,table,dl.wp-caption', classes: 'alignleft'}
						],
						aligncenter: [
							{selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'center'}},
							{selector: 'img,table,dl.wp-caption', classes: 'aligncenter'}
						],
						alignright: [
							{selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'right'}},
							{selector: 'img,table,dl.wp-caption', classes: 'alignright'}
						],
						strikethrough: {inline: 'del'}
					}",
					'relative_urls' => false,
					'remove_script_host' => false,
					'convert_urls' => false,
					'browser_spellcheck' => true,
					'fix_list_elements' => true,
					'entities' => '38,amp,60,lt,62,gt',
					'entity_encoding' => 'raw',
					'keep_styles' => false,
					'paste_webkit_styles' => 'font-weight font-style color',

					// Limit the preview styles in the menu/toolbar
					'preview_styles' => 'font-family font-size font-weight font-style text-decoration text-transform',

					'wpeditimage_disable_captions' => $no_captions,
					'wpeditimage_html5_captions' => current_theme_supports( 'html5', 'caption' ),
					'plugins' => implode( ',', $plugins ),
				);

				if ( ! empty( $mce_external_plugins ) ) {
					self::$first_init['external_plugins'] = json_encode( $mce_external_plugins );
				}

				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				$version = 'ver=' . $GLOBALS['wp_version'];
				$dashicons = includes_url( "css/dashicons$suffix.css?$version" );
				$mediaelement = includes_url( "js/mediaelement/mediaelementplayer.min.css?$version" );
				$wpmediaelement = includes_url( "js/mediaelement/wp-mediaelement.css?$version" );

				// WordPress default stylesheet and dashicons
				$mce_css = array(
					$dashicons,
					$mediaelement,
					$wpmediaelement,
					self::$baseurl . '/skins/wordpress/wp-content.css?' . $version
				);

				// load editor_style.css if the current theme supports it
				if ( ! empty( $GLOBALS['editor_styles'] ) && is_array( $GLOBALS['editor_styles'] ) ) {
					$editor_styles = $GLOBALS['editor_styles'];

					$editor_styles = array_unique( array_filter( $editor_styles ) );
					$style_uri = get_stylesheet_directory_uri();
					$style_dir = get_stylesheet_directory();

					// Support externally referenced styles (like, say, fonts).
					foreach ( $editor_styles as $key => $file ) {
						if ( preg_match( '~^(https?:)?//~', $file ) ) {
							$mce_css[] = esc_url_raw( $file );
							unset( $editor_styles[ $key ] );
						}
					}

					// Look in a parent theme first, that way child theme CSS overrides.
					if ( is_child_theme() ) {
						$template_uri = get_template_directory_uri();
						$template_dir = get_template_directory();

						foreach ( $editor_styles as $key => $file ) {
							if ( $file && file_exists( "$template_dir/$file" ) )
								$mce_css[] = "$template_uri/$file";
						}
					}

					foreach ( $editor_styles as $file ) {
						if ( $file && file_exists( "$style_dir/$file" ) )
							$mce_css[] = "$style_uri/$file";
					}
				}

				/**
				 * Filter the comma-delimited list of stylesheets to load in TinyMCE.
				 *
				 * @since 2.1.0
				 *
				 * @param array $stylesheets Comma-delimited list of stylesheets.
				 */
				$mce_css = trim( apply_filters( 'mce_css', implode( ',', $mce_css ) ), ' ,' );

				if ( ! empty($mce_css) )
					self::$first_init['content_css'] = $mce_css;
			}

			if ( $set['teeny'] ) {

				/**
				 * Filter the list of teenyMCE buttons (Text tab).
				 *
				 * @since 2.7.0
				 *
				 * @param array  $buttons   An array of teenyMCE buttons.
				 * @param string $editor_id Unique editor identifier, e.g. 'content'.
				 */
				$mce_buttons = apply_filters( 'teeny_mce_buttons', array('bold', 'italic', 'underline', 'blockquote', 'strikethrough', 'bullist', 'numlist', 'alignleft', 'aligncenter', 'alignright', 'undo', 'redo', 'link', 'unlink', 'fullscreen'), $editor_id );
				$mce_buttons_2 = $mce_buttons_3 = $mce_buttons_4 = array();
			} else {

				/**
				 * Filter the first-row list of TinyMCE buttons (Visual tab).
				 *
				 * @since 2.0.0
				 *
				 * @param array  $buttons   First-row list of buttons.
				 * @param string $editor_id Unique editor identifier, e.g. 'content'.
				 */
				$mce_buttons = apply_filters( 'mce_buttons', array('bold', 'italic', 'strikethrough', 'bullist', 'numlist', 'blockquote', 'hr', 'alignleft', 'aligncenter', 'alignright', 'link', 'unlink', 'wp_more', 'spellchecker', 'fullscreen', 'wp_adv' ), $editor_id );

				/**
				 * Filter the second-row list of TinyMCE buttons (Visual tab).
				 *
				 * @since 2.0.0
				 *
				 * @param array  $buttons   Second-row list of buttons.
				 * @param string $editor_id Unique editor identifier, e.g. 'content'.
				 */
				$mce_buttons_2 = apply_filters( 'mce_buttons_2', array( 'formatselect', 'underline', 'alignjustify', 'forecolor', 'pastetext', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo', 'wp_help' ), $editor_id );

				/**
				 * Filter the third-row list of TinyMCE buttons (Visual tab).
				 *
				 * @since 2.0.0
				 *
				 * @param array  $buttons   Third-row list of buttons.
				 * @param string $editor_id Unique editor identifier, e.g. 'content'.
				 */
				$mce_buttons_3 = apply_filters( 'mce_buttons_3', array(), $editor_id );

				/**
				 * Filter the fourth-row list of TinyMCE buttons (Visual tab).
				 *
				 * @since 2.5.0
				 *
				 * @param array  $buttons   Fourth-row list of buttons.
				 * @param string $editor_id Unique editor identifier, e.g. 'content'.
				 */
				$mce_buttons_4 = apply_filters( 'mce_buttons_4', array(), $editor_id );
			}

			$body_class = $editor_id;

			if ( $post = get_post() ) {
				$body_class .= ' post-type-' . sanitize_html_class( $post->post_type ) . ' post-status-' . sanitize_html_class( $post->post_status );
				if ( post_type_supports( $post->post_type, 'post-formats' ) ) {
					$post_format = get_post_format( $post );
					if ( $post_format && ! is_wp_error( $post_format ) )
						$body_class .= ' post-format-' . sanitize_html_class( $post_format );
					else
						$body_class .= ' post-format-standard';
				}
			}

			if ( !empty($set['tinymce']['body_class']) ) {
				$body_class .= ' ' . $set['tinymce']['body_class'];
				unset($set['tinymce']['body_class']);
			}

			if ( $set['dfw'] ) {
				// replace the first 'fullscreen' with 'wp_fullscreen'
				if ( ($key = array_search('fullscreen', $mce_buttons)) !== false )
					$mce_buttons[$key] = 'wp_fullscreen';
				elseif ( ($key = array_search('fullscreen', $mce_buttons_2)) !== false )
					$mce_buttons_2[$key] = 'wp_fullscreen';
				elseif ( ($key = array_search('fullscreen', $mce_buttons_3)) !== false )
					$mce_buttons_3[$key] = 'wp_fullscreen';
				elseif ( ($key = array_search('fullscreen', $mce_buttons_4)) !== false )
					$mce_buttons_4[$key] = 'wp_fullscreen';
			}

			$mceInit = array (
				'selector' => "#$editor_id",
				'resize' => 'vertical',
				'menubar' => false,
				'wpautop' => (bool) $set['wpautop'],
				'indent' => ! $set['wpautop'],
				'toolbar1' => implode($mce_buttons, ','),
				'toolbar2' => implode($mce_buttons_2, ','),
				'toolbar3' => implode($mce_buttons_3, ','),
				'toolbar4' => implode($mce_buttons_4, ','),
				'tabfocus_elements' => $set['tabfocus_elements'],
				'body_class' => $body_class
			);

			if ( $first_run )
				$mceInit = array_merge( self::$first_init, $mceInit );

			if ( is_array( $set['tinymce'] ) )
				$mceInit = array_merge( $mceInit, $set['tinymce'] );

			/*
			 * For people who really REALLY know what they're doing with TinyMCE
			 * You can modify $mceInit to add, remove, change elements of the config
			 * before tinyMCE.init. Setting "valid_elements", "invalid_elements"
			 * and "extended_valid_elements" can be done through this filter. Best
			 * is to use the default cleanup by not specifying valid_elements,
			 * as TinyMCE contains full set of XHTML 1.0.
			 */
			if ( $set['teeny'] ) {

				/**
				 * Filter the teenyMCE config before init.
				 *
				 * @since 2.7.0
				 *
				 * @param array  $mceInit   An array with teenyMCE config.
				 * @param string $editor_id Unique editor identifier, e.g. 'content'.
				 */
				$mceInit = apply_filters( 'teeny_mce_before_init', $mceInit, $editor_id );
			} else {

				/**
				 * Filter the TinyMCE config before init.
				 *
				 * @since 2.5.0
				 *
				 * @param array  $mceInit   An array with TinyMCE config.
				 * @param string $editor_id Unique editor identifier, e.g. 'content'.
				 */
				$mceInit = apply_filters( 'tiny_mce_before_init', $mceInit, $editor_id );
			}

			if ( empty( $mceInit['toolbar3'] ) && ! empty( $mceInit['toolbar4'] ) ) {
				$mceInit['toolbar3'] = $mceInit['toolbar4'];
				$mceInit['toolbar4'] = '';
			}

			self::$mce_settings[$editor_id] = $mceInit;
		} // end if self::$this_tinymce
	}

	private static function _parse_init($init) {
		$options = '';

		foreach ( $init as $k => $v ) {
			if ( is_bool($v) ) {
				$val = $v ? 'true' : 'false';
				$options .= $k . ':' . $val . ',';
				continue;
			} elseif ( !empty($v) && is_string($v) && ( ('{' == $v{0} && '}' == $v{strlen($v) - 1}) || ('[' == $v{0} && ']' == $v{strlen($v) - 1}) || preg_match('/^\(?function ?\(/', $v) ) ) {
				$options .= $k . ':' . $v . ',';
				continue;
			}
			$options .= $k . ':"' . $v . '",';
		}

		return '{' . trim( $options, ' ,' ) . '}';
	}

	public static function enqueue_scripts() {
		wp_enqueue_script('word-count');

		if ( self::$has_tinymce )
			wp_enqueue_script('editor');

		if ( self::$has_quicktags ) {
			wp_enqueue_script( 'quicktags' );
			wp_enqueue_style( 'buttons' );
		}

		if ( in_array('wplink', self::$plugins, true) || in_array('link', self::$qt_buttons, true) ) {
			wp_enqueue_script('wplink');
		}

		if ( in_array('wpfullscreen', self::$plugins, true) || in_array('fullscreen', self::$qt_buttons, true) )
			wp_enqueue_script('wp-fullscreen');

		if ( self::$has_medialib ) {
			add_thickbox();
			wp_enqueue_script('media-upload');
		}

		/**
		 * Fires when scripts and styles are enqueued for the editor.
		 *
		 * @since 3.9.0
		 *
		 * @param array $to_load An array containing boolean values whether TinyMCE
		 *                       and Quicktags are being loaded.
		 */
		do_action( 'wp_enqueue_editor', array(
			'tinymce'   => self::$has_tinymce,
			'quicktags' => self::$has_quicktags,
		) );
	}

	public static function wp_mce_translation() {

		$mce_translation = array(
			// Default TinyMCE strings
			'New document' => __( 'New document' ),
			'Formats' => _x( 'Formats', 'TinyMCE' ),

			'Headings' => _x( 'Headings', 'TinyMCE' ),
			'Heading 1' => __( 'Heading 1' ),
			'Heading 2' => __( 'Heading 2' ),
			'Heading 3' => __( 'Heading 3' ),
			'Heading 4' => __( 'Heading 4' ),
			'Heading 5' => __( 'Heading 5' ),
			'Heading 6' => __( 'Heading 6' ),

			/* translators: block tags */
			'Blocks' => _x( 'Blocks', 'TinyMCE' ),
			'Paragraph' => __( 'Paragraph' ),
			'Blockquote' => __( 'Blockquote' ),
			'Div' => _x( 'Div', 'HTML tag' ),
			'Pre' => _x( 'Pre', 'HTML tag' ),
			'Address' => _x( 'Address', 'HTML tag' ),

			'Inline' => _x( 'Inline', 'HTML elements' ),
			'Underline' => __( 'Underline' ),
			'Strikethrough' => __( 'Strikethrough' ),
			'Subscript' => __( 'Subscript' ),
			'Superscript' => __( 'Superscript' ),
			'Clear formatting' => __( 'Clear formatting' ),
			'Bold' => __( 'Bold' ),
			'Italic' => __( 'Italic' ),
			'Code' => _x( 'Code', 'editor button' ),
			'Source code' => __( 'Source code' ),
			'Font Family' => __( 'Font Family' ),
			'Font Sizes' => __( 'Font Sizes' ),

			'Align center' => __( 'Align center' ),
			'Align right' => __( 'Align right' ),
			'Align left' => __( 'Align left' ),
			'Justify' => __( 'Justify' ),
			'Increase indent' => __( 'Increase indent' ),
			'Decrease indent' => __( 'Decrease indent' ),

			'Cut' => __( 'Cut' ),
			'Copy' => __( 'Copy' ),
			'Paste' => __( 'Paste' ),
			'Select all' => __( 'Select all' ),
			'Undo' => __( 'Undo' ),
			'Redo' => __( 'Redo' ),

			'Ok' => __( 'OK' ),
			'Cancel' => __( 'Cancel' ),
			'Close' => __( 'Close' ),
			'Visual aids' => __( 'Visual aids' ),

			'Bullet list' => __( 'Bulleted list' ),
			'Numbered list' => __( 'Numbered list' ),
			'Square' => _x( 'Square', 'list style' ),
			'Default' => _x( 'Default', 'list style' ),
			'Circle' => _x( 'Circle', 'list style' ),
			'Disc' => _x('Disc', 'list style' ),
			'Lower Greek' => _x( 'Lower Greek', 'list style' ),
			'Lower Alpha' => _x( 'Lower Alpha', 'list style' ),
			'Upper Alpha' => _x( 'Upper Alpha', 'list style' ),
			'Upper Roman' => _x( 'Upper Roman', 'list style' ),
			'Lower Roman' => _x( 'Lower Roman', 'list style' ),

			// Anchor plugin
			'Name' => _x( 'Name', 'Name of link anchor (TinyMCE)' ),
			'Anchor' => _x( 'Anchor', 'Link anchor (TinyMCE)' ),
			'Anchors' => _x( 'Anchors', 'Link anchors (TinyMCE)' ),

			// Fullpage plugin
			'Document properties' => __( 'Document properties' ),
			'Robots' => __( 'Robots' ),
			'Title' => __( 'Title' ),
			'Keywords' => __( 'Keywords' ),
			'Encoding' => __( 'Encoding' ),
			'Description' => __( 'Description' ),
			'Author' => __( 'Author' ),

			// Media, image plugins
			'Insert/edit image' => __( 'Insert/edit image' ),
			'General' => __( 'General' ),
			'Advanced' => __( 'Advanced' ),
			'Source' => __( 'Source' ),
			'Border' => __( 'Border' ),
			'Constrain proportions' => __( 'Constrain proportions' ),
			'Vertical space' => __( 'Vertical space' ),
			'Image description' => __( 'Image description' ),
			'Style' => __( 'Style' ),
			'Dimensions' => __( 'Dimensions' ),
			'Insert image' => __( 'Insert image' ),
			'Insert date/time' => __( 'Insert date/time' ),
			'Insert/edit video' => __( 'Insert/edit video' ),
			'Poster' => __( 'Poster' ),
			'Alternative source' => __( 'Alternative source' ),
			'Paste your embed code below:' => __( 'Paste your embed code below:' ),
			'Insert video' => __( 'Insert video' ),
			'Embed' => __( 'Embed' ),

			// Each of these have a corresponding plugin
			'Special character' => __( 'Special character' ),
			'Right to left' => _x( 'Right to left', 'editor button' ),
			'Left to right' => _x( 'Left to right', 'editor button' ),
			'Emoticons' => __( 'Emoticons' ),
			'Nonbreaking space' => __( 'Nonbreaking space' ),
			'Page break' => __( 'Page break' ),
			'Paste as text' => __( 'Paste as text' ),
			'Preview' => __( 'Preview' ),
			'Print' => __( 'Print' ),
			'Save' => __( 'Save' ),
			'Fullscreen' => __( 'Fullscreen' ),
			'Horizontal line' => __( 'Horizontal line' ),
			'Horizontal space' => __( 'Horizontal space' ),
			'Restore last draft' => __( 'Restore last draft' ),
			'Insert/edit link' => __( 'Insert/edit link' ),
			'Remove link' => __( 'Remove link' ),

			// Spelling, search/replace plugins
			'Could not find the specified string.' => __( 'Could not find the specified string.' ),
			'Replace' => _x( 'Replace', 'find/replace' ),
			'Next' => _x( 'Next', 'find/replace' ),
			/* translators: previous */
			'Prev' => _x( 'Prev', 'find/replace' ),
			'Whole words' => _x( 'Whole words', 'find/replace' ),
			'Find and replace' => __( 'Find and replace' ),
			'Replace with' => _x('Replace with', 'find/replace' ),
			'Find' => _x( 'Find', 'find/replace' ),
			'Replace all' => _x( 'Replace all', 'find/replace' ),
			'Match case' => __( 'Match case' ),
			'Spellcheck' => __( 'Check Spelling' ),
			'Finish' => _x( 'Finish', 'spellcheck' ),
			'Ignore all' => _x( 'Ignore all', 'spellcheck' ),
			'Ignore' => _x( 'Ignore', 'spellcheck' ),

			// TinyMCE tables
			'Insert table' => __( 'Insert table' ),
			'Delete table' => __( 'Delete table' ),
			'Table properties' => __( 'Table properties' ),
			'Row properties' => __( 'Table row properties' ),
			'Cell properties' => __( 'Table cell properties' ),

			'Row' => __( 'Row' ),
			'Rows' => __( 'Rows' ),
			'Column' => _x( 'Column', 'table column' ),
			'Cols' => _x( 'Cols', 'table columns' ),
			'Cell' => _x( 'Cell', 'table cell' ),
			'Header cell' => __( 'Header cell' ),
			'Header' => _x( 'Header', 'table header' ),
			'Body' => _x( 'Body', 'table body' ),
			'Footer' => _x( 'Footer', 'table footer' ),

			'Insert row before' => __( 'Insert row before' ),
			'Insert row after' => __( 'Insert row after' ),
			'Insert column before' => __( 'Insert column before' ),
			'Insert column after' => __( 'Insert column after' ),
			'Paste row before' => __( 'Paste table row before' ),
			'Paste row after' => __( 'Paste table row after' ),
			'Delete row' => __( 'Delete row' ),
			'Delete column' => __( 'Delete column' ),
			'Cut row' => __( 'Cut table row' ),
			'Copy row' => __( 'Copy table row' ),
			'Merge cells' => __( 'Merge table cells' ),
			'Split cell' => __( 'Split table cell' ),

			'Height' => __( 'Height' ),
			'Width' => __( 'Width' ),
			'Caption' => __( 'Caption' ),
			'Alignment' => __( 'Alignment' ),
			'Left' => __( 'Left' ),
			'Center' => __( 'Center' ),
			'Right' => __( 'Right' ),
			'None' => _x( 'None', 'table cell alignment attribute' ),

			'Row group' => __( 'Row group' ),
			'Column group' => __( 'Column group' ),
			'Row type' => __( 'Row type' ),
			'Cell type' => __( 'Cell type' ),
			'Cell padding' => __( 'Cell padding' ),
			'Cell spacing' => __( 'Cell spacing' ),
			'Scope' => _x( 'Scope', 'table cell scope attribute' ),

			'Insert template' => _x( 'Insert template', 'TinyMCE' ),
			'Templates' => _x( 'Templates', 'TinyMCE' ),

			'Background color' => __( 'Background color' ),
			'Text color' => __( 'Text color' ),
			'Show blocks' => _x( 'Show blocks', 'editor button' ),
			'Show invisible characters' => __( 'Show invisible characters' ),

			/* translators: word count */
			'Words: {0}' => sprintf( __( 'Words: %s' ), '{0}' ),
			'Paste is now in plain text mode. Contents will now be pasted as plain text until you toggle this option off.' => __( 'Paste is now in plain text mode. Contents will now be pasted as plain text until you toggle this option off.' ) . "\n\n" . __( 'If you&#8217;re looking to paste rich content from Microsoft Word, try turning this option off. The editor will clean up text pasted from Word automatically.' ),
			'Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help' => __( 'Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help' ),
			'You have unsaved changes are you sure you want to navigate away?' => __( 'The changes you made will be lost if you navigate away from this page.' ),
			'Your browser doesn\'t support direct access to the clipboard. Please use the Ctrl+X/C/V keyboard shortcuts instead.' => __( 'Your browser does not support direct access to the clipboard. Please use the Ctrl+X/C/V keyboard shortcuts instead.' ),

			// TinyMCE menus
			'Insert' => _x( 'Insert', 'TinyMCE menu' ),
			'File' => _x( 'File', 'TinyMCE menu' ),
			'Edit' => _x( 'Edit', 'TinyMCE menu' ),
			'Tools' => _x( 'Tools', 'TinyMCE menu' ),
			'View' => _x( 'View', 'TinyMCE menu' ),
			'Table' => _x( 'Table', 'TinyMCE menu' ),
			'Format' => _x( 'Format', 'TinyMCE menu' ),

			// WordPress strings
			'Keyboard Shortcuts' => __( 'Keyboard Shortcuts' ),
			'Toolbar Toggle' => __( 'Toolbar Toggle' ),
			'Insert Read More tag' => __( 'Insert Read More tag' ),
			'Distraction Free Writing' => __( 'Distraction Free Writing' ),
		);

		/**
		 * Link plugin (not included):
		 *	Insert link
		 *	Target
		 *	New window
		 *	Text to display
		 *	The URL you entered seems to be an email address. Do you want to add the required mailto: prefix?
		 *	The URL you entered seems to be an external link. Do you want to add the required http:\/\/ prefix?
		 *	Url
		 */

		$baseurl = self::$baseurl;
		$mce_locale = self::$mce_locale;

		/**
		 * Filter translated strings prepared for TinyMCE.
		 *
		 * @since 3.9.0
		 *
		 * @param array  $mce_translation Key/value pairs of strings.
		 * @param string $mce_locale      Locale.
		 */
		$mce_translation = apply_filters( 'wp_mce_translation', $mce_translation, $mce_locale );

		foreach ( $mce_translation as $key => $value ) {
			if ( false !== strpos( $value, '&' ) ) {
				$mce_translation[$key] = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
			}
		}

		// Set direction
		if ( is_rtl() ) {
			$mce_translation['_dir'] = 'rtl';
		}

		return "tinymce.addI18n( '$mce_locale', " . json_encode( $mce_translation ) . ");\n" .
			"tinymce.ScriptLoader.markDone( '$baseurl/langs/$mce_locale.js' );\n";
	}

	public static function editor_js() {
		global $tinymce_version, $concatenate_scripts, $compress_scripts;

		/**
		 * Filter "tiny_mce_version" is deprecated
		 *
		 * The tiny_mce_version filter is not needed since external plugins are loaded directly by TinyMCE.
		 * These plugins can be refreshed by appending query string to the URL passed to "mce_external_plugins" filter.
		 * If the plugin has a popup dialog, a query string can be added to the button action that opens it (in the plugin's code).
		 */
		$version = 'ver=' . $tinymce_version;
		$tmce_on = !empty(self::$mce_settings);

		if ( ! isset($concatenate_scripts) )
			script_concat_settings();

		$compressed = $compress_scripts && $concatenate_scripts && isset($_SERVER['HTTP_ACCEPT_ENCODING'])
			&& false !== stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');

		$mceInit = $qtInit = '';
		if ( $tmce_on ) {
			foreach ( self::$mce_settings as $editor_id => $init ) {
				$options = self::_parse_init( $init );
				$mceInit .= "'$editor_id':{$options},";
			}
			$mceInit = '{' . trim($mceInit, ',') . '}';
		} else {
			$mceInit = '{}';
		}

		if ( !empty(self::$qt_settings) ) {
			foreach ( self::$qt_settings as $editor_id => $init ) {
				$options = self::_parse_init( $init );
				$qtInit .= "'$editor_id':{$options},";
			}
			$qtInit = '{' . trim($qtInit, ',') . '}';
		} else {
			$qtInit = '{}';
		}

		$ref = array(
			'plugins' => implode( ',', self::$plugins ),
			'theme' => 'modern',
			'language' => self::$mce_locale
		);

		$suffix = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';

		/**
		 * Fires immediately before the TinyMCE settings are printed.
		 *
		 * @since 3.2.0
		 *
		 * @param array $mce_settings TinyMCE settings array.
		 */
		do_action( 'before_wp_tiny_mce', self::$mce_settings );
		?>

		<script type="text/javascript">
		tinyMCEPreInit = {
			baseURL: "<?php echo self::$baseurl; ?>",
			suffix: "<?php echo $suffix; ?>",
			<?php

			if ( self::$drag_drop_upload ) {
				echo 'dragDropUpload: true,';
			}

			?>
			mceInit: <?php echo $mceInit; ?>,
			qtInit: <?php echo $qtInit; ?>,
			ref: <?php echo self::_parse_init( $ref ); ?>,
			load_ext: function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
		};
		</script>
		<?php

		$baseurl = self::$baseurl;
		// Load tinymce.js when running from /src, else load wp-tinymce.js.gz (production) or tinymce.min.js (SCRIPT_DEBUG)
		$mce_suffix = false !== strpos( $GLOBALS['wp_version'], '-src' ) ? '' : '.min';

		if ( $tmce_on ) {
			if ( $compressed ) {
				echo "<script type='text/javascript' src='{$baseurl}/wp-tinymce.php?c=1&amp;$version'></script>\n";
			} else {
				echo "<script type='text/javascript' src='{$baseurl}/tinymce{$mce_suffix}.js?$version'></script>\n";
				echo "<script type='text/javascript' src='{$baseurl}/plugins/compat3x/plugin{$suffix}.js?$version'></script>\n";
			}

			echo "<script type='text/javascript'>\n" . self::wp_mce_translation() . "</script>\n";

			if ( self::$ext_plugins ) {
				// Load the old-format English strings to prevent unsightly labels in old style popups
				echo "<script type='text/javascript' src='{$baseurl}/langs/wp-langs-en.js?$version'></script>\n";
			}
		}

		/**
		 * Fires after tinymce.js is loaded, but before any TinyMCE editor
		 * instances are created.
		 *
		 * @since 3.9.0
		 *
		 * @param array $mce_settings TinyMCE settings array.
		 */
		do_action( 'wp_tiny_mce_init', self::$mce_settings );

		?>
		<script type="text/javascript">
		<?php

		if ( self::$ext_plugins )
			echo self::$ext_plugins . "\n";

		if ( ! is_admin() )
			echo 'var ajaxurl = "' . admin_url( 'admin-ajax.php', 'relative' ) . '";';

		?>

		( function() {
			var init, edId, qtId, firstInit, wrapper;

			if ( typeof tinymce !== 'undefined' ) {
				for ( edId in tinyMCEPreInit.mceInit ) {
					if ( firstInit ) {
						init = tinyMCEPreInit.mceInit[edId] = tinymce.extend( {}, firstInit, tinyMCEPreInit.mceInit[edId] );
					} else {
						init = firstInit = tinyMCEPreInit.mceInit[edId];
					}

					wrapper = tinymce.DOM.select( '#wp-' + edId + '-wrap' )[0];

					if ( ( tinymce.DOM.hasClass( wrapper, 'tmce-active' ) || ! tinyMCEPreInit.qtInit.hasOwnProperty( edId ) ) &&
						! init.wp_skip_init ) {

						try {
							tinymce.init( init );

							if ( ! window.wpActiveEditor ) {
								window.wpActiveEditor = edId;
							}
						} catch(e){}
					}
				}
			}

			if ( typeof quicktags !== 'undefined' ) {
				for ( qtId in tinyMCEPreInit.qtInit ) {
					try {
						quicktags( tinyMCEPreInit.qtInit[qtId] );

						if ( ! window.wpActiveEditor ) {
							window.wpActiveEditor = qtId;
						}
					} catch(e){};
				}
			}

			if ( typeof jQuery !== 'undefined' ) {
				jQuery('.wp-editor-wrap').on( 'click.wp-editor', function() {
					if ( this.id ) {
						window.wpActiveEditor = this.id.slice( 3, -5 );
					}
				});
			} else {
				for ( qtId in tinyMCEPreInit.qtInit ) {
					document.getElementById( 'wp-' + qtId + '-wrap' ).onclick = function() {
						window.wpActiveEditor = this.id.slice( 3, -5 );
					}
				}
			}
		}());
		</script>
		<?php

		if ( in_array( 'wplink', self::$plugins, true ) || in_array( 'link', self::$qt_buttons, true ) )
			self::wp_link_dialog();

		if ( in_array( 'wpfullscreen', self::$plugins, true ) || in_array( 'fullscreen', self::$qt_buttons, true ) )
			self::wp_fullscreen_html();

		/**
		 * Fires after any core TinyMCE editor instances are created.
		 *
		 * @since 3.2.0
		 *
		 * @param array $mce_settings TinyMCE settings array.
		 */
		do_action( 'after_wp_tiny_mce', self::$mce_settings );
	}

	public static function wp_fullscreen_html() {
		global $content_width;
		$post = get_post();

		$width = isset( $content_width ) && 800 > $content_width ? $content_width : 800;
		$width = $width + 22; // compensate for the padding and border
		$dfw_width = get_user_setting( 'dfw_width', $width );
		$save = isset( $post->post_status ) && $post->post_status == 'publish' ? __('Update') : __('Save');

		?>
		<div id="wp-fullscreen-body" class="wp-core-ui<?php if ( is_rtl() ) echo ' rtl'; ?>" data-theme-width="<?php echo (int) $width; ?>" data-dfw-width="<?php echo (int) $dfw_width; ?>">
		<div id="fullscreen-topbar">
			<div id="wp-fullscreen-toolbar">
			<div id="wp-fullscreen-close"><a href="#" onclick="wp.editor.fullscreen.off();return false;"><?php _e('Exit fullscreen'); ?></a></div>
			<div id="wp-fullscreen-central-toolbar" style="width:<?php echo $width; ?>px;">

			<div id="wp-fullscreen-mode-bar">
				<div id="wp-fullscreen-modes" class="button-group">
					<a class="button wp-fullscreen-mode-tinymce" href="#" onclick="wp.editor.fullscreen.switchmode( 'tinymce' ); return false;"><?php _e( 'Visual' ); ?></a>
					<a class="button wp-fullscreen-mode-html" href="#" onclick="wp.editor.fullscreen.switchmode( 'html' ); return false;"><?php _ex( 'Text', 'Name for the Text editor tab (formerly HTML)' ); ?></a>
				</div>
			</div>

			<div id="wp-fullscreen-button-bar"><div id="wp-fullscreen-buttons" class="mce-toolbar">
		<?php

		$buttons = array(
			// format: title, onclick, show in both editors
			'bold' => array( 'title' => __('Bold (Ctrl + B)'), 'both' => false ),
			'italic' => array( 'title' => __('Italic (Ctrl + I)'), 'both' => false ),
			'bullist' => array( 'title' => __('Unordered list (Alt + Shift + U)'), 'both' => false ),
			'numlist' => array( 'title' => __('Ordered list (Alt + Shift + O)'), 'both' => false ),
			'blockquote' => array( 'title' => __('Blockquote (Alt + Shift + Q)'), 'both' => false ),
			'wp-media-library' => array( 'title' => __('Media library (Alt + Shift + M)'), 'both' => true ),
			'link' => array( 'title' => __('Insert/edit link (Alt + Shift + A)'), 'both' => true ),
			'unlink' => array( 'title' => __('Unlink (Alt + Shift + S)'), 'both' => false ),
			'help' => array( 'title' => __('Help (Alt + Shift + H)'), 'both' => false ),
		);

		/**
		 * Filter the list of TinyMCE buttons for the fullscreen
		 * 'Distraction Free Writing' editor.
		 *
		 * @since 3.2.0
		 *
		 * @param array $buttons An array of TinyMCE buttons for the DFW editor.
		 */
		$buttons = apply_filters( 'wp_fullscreen_buttons', $buttons );

		foreach ( $buttons as $button => $args ) {
			if ( 'separator' == $args ) {
				continue;
			}

			$onclick = ! empty( $args['onclick'] ) ? ' onclick="' . $args['onclick'] . '"' : '';
			$title = esc_attr( $args['title'] );
			?>

			<div class="mce-widget mce-btn<?php if ( $args['both'] ) { ?> wp-fullscreen-both<?php } ?>">
			<button type="button" aria-label="<?php echo $title; ?>" title="<?php echo $title; ?>"<?php echo $onclick; ?> id="wp_fs_<?php echo $button; ?>">
				<i class="mce-ico mce-i-<?php echo $button; ?>"></i>
			</button>
			</div>
			<?php
		}

		?>

		</div></div>

		<div id="wp-fullscreen-save">
			<input type="button" class="button button-primary right" value="<?php echo $save; ?>" onclick="wp.editor.fullscreen.save();" />
			<span class="wp-fullscreen-saved-message"><?php if ( $post->post_status == 'publish' ) _e('Updated.'); else _e('Saved.'); ?></span>
			<span class="wp-fullscreen-error-message"><?php _e('Save failed.'); ?></span>
			<span class="spinner"></span>
		</div>

		</div>
		</div>
	</div>
	<div id="wp-fullscreen-statusbar">
		<div id="wp-fullscreen-status">
			<div id="wp-fullscreen-count"><?php printf( __( 'Word count: %s' ), '<span class="word-count">0</span>' ); ?></div>
			<div id="wp-fullscreen-tagline"><?php _e('Just write.'); ?></div>
		</div>
	</div>
	</div>

	<div class="fullscreen-overlay" id="fullscreen-overlay"></div>
	<div class="fullscreen-overlay fullscreen-fader fade-300" id="fullscreen-fader"></div>
	<?php
	}

	/**
	 * Performs post queries for internal linking.
	 *
	 * @since 3.1.0
	 *
	 * @param array $args Optional. Accepts 'pagenum' and 's' (search) arguments.
	 * @return array Results.
	 */
	public static function wp_link_query( $args = array() ) {
		$pts = get_post_types( array( 'public' => true ), 'objects' );
		$pt_names = array_keys( $pts );

		$query = array(
			'post_type' => $pt_names,
			'suppress_filters' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status' => 'publish',
			'posts_per_page' => 20,
		);

		$args['pagenum'] = isset( $args['pagenum'] ) ? absint( $args['pagenum'] ) : 1;

		if ( isset( $args['s'] ) )
			$query['s'] = $args['s'];

		$query['offset'] = $args['pagenum'] > 1 ? $query['posts_per_page'] * ( $args['pagenum'] - 1 ) : 0;

		/**
		 * Filter the link query arguments.
		 *
		 * Allows modification of the link query arguments before querying.
		 *
		 * @see WP_Query for a full list of arguments
		 *
		 * @since 3.7.0
		 *
		 * @param array $query An array of WP_Query arguments.
		 */
		$query = apply_filters( 'wp_link_query_args', $query );

		// Do main query.
		$get_posts = new WP_Query;
		$posts = $get_posts->query( $query );
		// Check if any posts were found.
		if ( ! $get_posts->post_count )
			return false;

		// Build results.
		$results = array();
		foreach ( $posts as $post ) {
			if ( 'post' == $post->post_type )
				$info = mysql2date( __( 'Y/m/d' ), $post->post_date );
			else
				$info = $pts[ $post->post_type ]->labels->singular_name;

			$results[] = array(
				'ID' => $post->ID,
				'title' => trim( esc_html( strip_tags( get_the_title( $post ) ) ) ),
				'permalink' => get_permalink( $post->ID ),
				'info' => $info,
			);
		}

		/**
		 * Filter the link query results.
		 *
		 * Allows modification of the returned link query results.
		 *
		 * @since 3.7.0
		 *
		 * @see 'wp_link_query_args' filter
		 *
		 * @param array $results {
		 *     An associative array of query results.
		 *
		 *     @type array {
		 *         @type int    $ID        Post ID.
		 *         @type string $title     The trimmed, escaped post title.
		 *         @type string $permalink Post permalink.
		 *         @type string $info      A 'Y/m/d'-formatted date for 'post' post type,
		 *                                 the 'singular_name' post type label otherwise.
		 *     }
		 * }
		 * @param array $query  An array of WP_Query arguments.
		 */
		return apply_filters( 'wp_link_query', $results, $query );
	}

	/**
	 * Dialog for internal linking.
	 *
	 * @since 3.1.0
	 */
	public static function wp_link_dialog() {
		$search_panel_visible = '1' == get_user_setting( 'wplink', '0' ) ? ' search-panel-visible' : '';

		// display: none is required here, see #WP27605
		?>
		<div id="wp-link-backdrop" style="display: none"></div>
		<div id="wp-link-wrap" class="wp-core-ui<?php echo $search_panel_visible; ?>" style="display: none">
		<form id="wp-link" tabindex="-1">
		<?php wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false ); ?>
		<div id="link-modal-title">
			<?php _e( 'Insert/edit link' ) ?>
			<div id="wp-link-close" tabindex="0"></div>
	 	</div>
		<div id="link-selector">
			<div id="link-options">
				<p class="howto"><?php _e( 'Enter the destination URL' ); ?></p>
				<div>
					<label><span><?php _e( 'URL' ); ?></span><input id="url-field" type="text" name="href" /></label>
				</div>
				<div>
					<label><span><?php _e( 'Title' ); ?></span><input id="link-title-field" type="text" name="linktitle" /></label>
				</div>
				<div class="link-target">
					<label><span>&nbsp;</span><input type="checkbox" id="link-target-checkbox" /> <?php _e( 'Open link in a new window/tab' ); ?></label>
				</div>
			</div>
			<p class="howto" id="wp-link-search-toggle"><?php _e( 'Or link to existing content' ); ?></p>
			<div id="search-panel">
				<div class="link-search-wrapper">
					<label>
						<span class="search-label"><?php _e( 'Search' ); ?></span>
						<input type="search" id="search-field" class="link-search-field" autocomplete="off" />
						<span class="spinner"></span>
					</label>
				</div>
				<div id="search-results" class="query-results">
					<ul></ul>
					<div class="river-waiting">
						<span class="spinner"></span>
					</div>
				</div>
				<div id="most-recent-results" class="query-results">
					<div class="query-notice"><em><?php _e( 'No search term specified. Showing recent items.' ); ?></em></div>
					<ul></ul>
					<div class="river-waiting">
						<span class="spinner"></span>
					</div>
				</div>
			</div>
		</div>
		<div class="submitbox">
			<div id="wp-link-update">
				<input type="submit" value="<?php esc_attr_e( 'Add Link' ); ?>" class="button button-primary" id="wp-link-submit" name="wp-link-submit">
			</div>
			<div id="wp-link-cancel">
				<a class="submitdelete deletion" href="#"><?php _e( 'Cancel' ); ?></a>
			</div>
		</div>
		</form>
		</div>
		<?php
	}
}
