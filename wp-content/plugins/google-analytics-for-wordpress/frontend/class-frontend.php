<?php

/**
 * Code that actually inserts stuff into pages.
 */
if ( !class_exists( 'GA_Filter' ) ) {
	class GA_Filter {

		var $options = array();

		function __construct() {
			$this->options = get_option( 'Yoast_Google_Analytics' );

			if ( !is_array( $this->options ) ) {
				$this->options = get_option( 'GoogleAnalyticsPP' );
				if ( !is_array( $this->options ) )
					return;
			}

			if ( !isset( $this->options['uastring'] ) || $this->options['uastring'] == '' ) {
				add_action( 'wp_head', array( $this, 'not_shown_error' ) );
			} else {
				if ( isset( $this->options['allowanchor'] ) && $this->options['allowanchor'] ) {
					add_action( 'init', array( $this, 'utm_hashtag_redirect' ), 1 );
				}

				if ( ( isset( $this->options['trackoutbound'] ) && $this->options['trackoutbound'] ) ||
					( isset( $this->options['trackcrossdomain'] ) && $this->options['trackcrossdomain'] )
				) {
					// filters alter the existing content
					add_filter( 'the_content', array( $this, 'the_content' ), 99 );
					add_filter( 'widget_text', array( $this, 'widget_content' ), 99 );
					add_filter( 'the_excerpt', array( $this, 'the_content' ), 99 );
					add_filter( 'comment_text', array( $this, 'comment_text' ), 99 );
					add_filter( 'get_bookmarks', array( $this, 'bookmarks' ), 99 );
					add_filter( 'get_comment_author_link', array( $this, 'comment_author_link' ), 99 );
					add_filter( 'wp_nav_menu', array( $this, 'nav_menu' ), 99 );
				}

				if ( $this->options["trackcommentform"] ) {
					global $comment_form_id;
					$comment_form_id = 'commentform';

					add_action( 'comment_form_after', array( $this, 'track_comment_form' ) );
					add_action( 'wp_print_scripts', array( $this, 'track_comment_form_head' ) );
					add_filter( 'comment_form_defaults', array( $this, 'get_comment_form_id' ), 99, 1 );
				}

				if ( isset( $this->options['trackadsense'] ) && $this->options['trackadsense'] )
					add_action( 'wp_head', array( $this, 'spool_adsense' ), 1 );

				if ( !isset( $this->options['position'] ) )
					$this->options['position'] = 'header';

				switch ( $this->options['position'] ) {
					case 'manual':
						// No need to insert here, bail NOW.
						break;
					case 'header':
					default:
						add_action( 'wp_head', array( $this, 'spool_analytics' ), 2 );
						break;
				}

				if ( isset( $this->options['trackregistration'] ) && $this->options['trackregistration'] )
					add_action( 'login_head', array( $this, 'spool_analytics' ), 20 );

				if ( isset( $this->options['rsslinktagging'] ) && $this->options['rsslinktagging'] )
					add_filter( 'the_permalink_rss', array( $this, 'rsslinktagger' ), 99 );
			}
		}

		function not_shown_error() {
			if ( current_user_can( 'manage_options' ) )
				echo "<!-- " . __( "Google Analytics tracking code not shown because you haven't setup Google Analytics for WordPress yet.", "gawp" ) . " -->\n";
		}

		function do_tracking() {
			global $current_user;

			get_currentuserinfo();

			if ( 0 == $current_user->ID )
				return true;

			if ( ( $current_user->user_level >= $this->options["ignore_userlevel"] ) )
				return false;
			else
				return true;
		}

		/**
		 * If setAllowAnchor is set to true, GA ignores all links tagged "normally", so we redirect all "normally" tagged URL's
		 * to one tagged with a hash.
		 */
		function utm_hashtag_redirect() {
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				if ( strpos( $_SERVER['REQUEST_URI'], "utm_" ) !== false ) {
					$url = 'http://';
					if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != "" ) {
						$url = 'https://';
					}
					$url .= $_SERVER['SERVER_NAME'];
					if ( strpos( $_SERVER['REQUEST_URI'], "?utm_" ) !== false ) {
						$url .= str_replace( "?utm_", "#utm_", $_SERVER['REQUEST_URI'] );
					} else if ( strpos( $_SERVER['REQUEST_URI'], "&utm_" ) !== false ) {
						$url .= substr_replace( $_SERVER['REQUEST_URI'], "#utm_", strpos( $_SERVER['REQUEST_URI'], "&utm_" ), 5 );
					}
					wp_redirect( $url, 301 );
					exit;
				}
			}
		}

		/**
		 * Cleans the variable to make it ready for storing in Google Analytics
		 */
		function str_clean( $val ) {
			return remove_accents( str_replace( '---', '-', str_replace( ' ', '-', strtolower( html_entity_decode( $val ) ) ) ) );
		}

		/*
		   * Insert the tracking code into the page
		   */
		function spool_analytics() {
			global $wp_query, $current_user;

			// Make sure $current_user is filled.
			get_currentuserinfo();

			/**
			 * The order of custom variables is very, very important: custom vars should always take up the same slot to make analysis easy.
			 */
			$customvarslot = 1;
			if ( $this->do_tracking() && !is_preview() ) {
				$push = array();

				if ( $this->options['allowanchor'] )
					$push[] = "'_setAllowAnchor',true";

				if ( $this->options['allowlinker'] )
					$push[] = "'_setAllowLinker',true";

				if ( $this->options['anonymizeip'] )
					$push[] = "'_gat._anonymizeIp'";

				if ( isset( $this->options['domain'] ) && $this->options['domain'] != "" )
					$push[] = "'_setDomainName','" . $this->options['domain'] . "'";

				if ( isset( $this->options['trackcrossdomain'] ) && $this->options['trackcrossdomain'] )
					$push[] = "'_setDomainName','" . $this->options['primarycrossdomain'] . "'";

				if ( isset( $this->options['allowhash'] ) && $this->options['allowhash'] )
					$push[] = "'_setAllowHash',false";

				if ( $this->options['cv_loggedin'] ) {
					if ( $current_user && $current_user->ID != 0 )
						$push[] = "'_setCustomVar',$customvarslot,'logged-in','" . $current_user->roles[0] . "',1";
					// Customvar slot needs to be upped even when the user is not logged in, to make sure the variables below are always in the same slot.
					$customvarslot++;
				}

				if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {
					if ( $this->options['cv_post_type'] ) {
						$post_type = get_post_type();
						if ( $post_type ) {
							$push[] = "'_setCustomVar'," . $customvarslot . ",'post_type','" . $post_type . "',3";
							$customvarslot++;
						}
					}
				} else if ( is_singular() && !is_home() ) {
					if ( $this->options['cv_post_type'] ) {
						$post_type = get_post_type();
						if ( $post_type ) {
							$push[] = "'_setCustomVar'," . $customvarslot . ",'post_type','" . $post_type . "',3";
							$customvarslot++;
						}
					}
					if ( $this->options['cv_authorname'] ) {
						$push[] = "'_setCustomVar',$customvarslot,'author','" . $this->str_clean( get_the_author_meta( 'display_name', $wp_query->post->post_author ) ) . "',3";
						$customvarslot++;
					}
					if ( $this->options['cv_tags'] ) {
						$i = 0;
						if ( get_the_tags() ) {
							$tagsstr = '';
							foreach ( get_the_tags() as $tag ) {
								if ( $i > 0 )
									$tagsstr .= ' ';
								$tagsstr .= $tag->slug;
								$i++;
							}
							// Max 64 chars for value and label combined, hence 64 - 4
							$tagsstr = substr( $tagsstr, 0, 60 );
							$push[]  = "'_setCustomVar',$customvarslot,'tags','" . $tagsstr . "',3";
						}
						$customvarslot++;
					}
					if ( is_singular() ) {
						if ( $this->options['cv_year'] ) {
							$push[] = "'_setCustomVar',$customvarslot,'year','" . get_the_time( 'Y' ) . "',3";
							$customvarslot++;
						}
						if ( $this->options['cv_category'] && is_single() ) {
							$cats = get_the_category();
							if ( is_array( $cats ) && isset( $cats[0] ) )
								$push[] = "'_setCustomVar',$customvarslot,'category','" . $cats[0]->slug . "',3";
							$customvarslot++;
						}
						if ( $this->options['cv_all_categories'] && is_single() ) {
							$i       = 0;
							$catsstr = '';
							foreach ( (array) get_the_category() as $cat ) {
								if ( $i > 0 )
									$catsstr .= ' ';
								$catsstr .= $cat->slug;
								$i++;
							}
							// Max 64 chars for value and label combined, hence 64 - 10
							$catsstr = substr( $catsstr, 0, 54 );
							$push[]  = "'_setCustomVar',$customvarslot,'categories','" . $catsstr . "',3";
							$customvarslot++;
						}
					}
				}

				$push = apply_filters( 'yoast-ga-custom-vars', $push, $customvarslot );

				$push = apply_filters( 'yoast-ga-push-before-pageview', $push );

				if ( is_404() ) {
					$push[] = "'_trackPageview','/404.html?page=' + document.location.pathname + document.location.search + '&from=' + document.referrer";
				} else if ( $wp_query->is_search ) {
					$pushstr = "'_trackPageview','" . get_bloginfo( 'url' ) . "/?s=";
					if ( $wp_query->found_posts == 0 ) {
						$push[] = $pushstr . "no-results:" . rawurlencode( $wp_query->query_vars['s'] ) . "&cat=no-results'";
					} else if ( $wp_query->found_posts == 1 ) {
						$push[] = $pushstr . rawurlencode( $wp_query->query_vars['s'] ) . "&cat=1-result'";
					} else if ( $wp_query->found_posts > 1 && $wp_query->found_posts < 6 ) {
						$push[] = $pushstr . rawurlencode( $wp_query->query_vars['s'] ) . "&cat=2-5-results'";
					} else {
						$push[] = $pushstr . rawurlencode( $wp_query->query_vars['s'] ) . "&cat=plus-5-results'";
					}
				} else {
					$push[] = "'_trackPageview'";
				}

				$push = apply_filters( 'yoast-ga-push-after-pageview', $push );

				if ( defined( 'WPSC_VERSION' ) && $this->options['wpec_tracking'] )
					$push = $this->wpec_transaction_tracking( $push );

				if ( $this->options['shopp_tracking'] ) {
					global $Shopp;
					if ( isset( $Shopp ) )
						$push = $this->shopp_transaction_tracking( $push );
				}

				$pushstr = "";
				foreach ( $push as $key ) {
					if ( !empty( $pushstr ) )
						$pushstr .= ",";

					$pushstr .= "[" . $key . "]";
				}

				if ( current_user_can( 'manage_options' ) && $this->options['firebuglite'] && $this->options['debug'] )
					echo '<script src="https://getfirebug.com/firebug-lite.js" type="text/javascript"></script>';
				?>

            <script type="text/javascript">//<![CDATA[
            // Google Analytics for WordPress by Yoast v<?php echo GAWP_VERSION;  ?> | http://yoast.com/wordpress/google-analytics/
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', '<?php echo trim( $this->options["uastring"] ); ?>']);
				<?php
				if ( $this->options["extrase"] ) {
					if ( !empty( $this->options["extraseurl"] ) ) {
						$url = $this->options["extraseurl"];
					} else {
						$url = GAWP_URL . 'custom_se_async.js';
					}
					echo '</script><script src="' . $url . '" type="text/javascript"></script>' . "\n" . '<script type="text/javascript">';
				}

				if ( $this->options['customcode'] && trim( $this->options['customcode'] ) != '' )
					echo "\t" . stripslashes( $this->options['customcode'] ) . "\n";
				?>
            _gaq.push(<?php echo $pushstr; ?>);
            (function () {
                var ga = document.createElement('script');
                ga.type = 'text/javascript';
                ga.async = true;
                ga.src = <?php
					if ( $this->options['gajslocalhosting'] && !empty( $this->options['gajsurl'] ) ) {
						echo "'" . $this->options['gajsurl'] . "';";
					} else {
						$script = 'ga.js';
						if ( current_user_can( 'manage_options' ) && $this->options['debug'] )
							$script = 'u/ga_debug.js';
						echo "('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/" . $script . "'";
					}
					?>;

                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ga, s);
            })();
            //]]></script>
			<?php
			} else if ( $this->options["uastring"] != "" ) {
				echo "<!-- " . sprintf( __( "Google Analytics tracking code not shown because users over level %s are ignored.", "gawp" ), $this->options["ignore_userlevel"] ) . " -->\n";
			}
		}

		/*
		 * Insert the AdSense parameter code into the page. This'll go into the header per Google's instructions.
		 */
		function spool_adsense() {
			if ( $this->do_tracking() && !is_preview() ) {
				echo '<script type="text/javascript">' . "\n";
				echo "\t" . 'window.google_analytics_uacct = "' . $this->options["uastring"] . '";' . "\n";
				echo '</script>' . "\n";
			}
		}

		function get_tracking_prefix() {
			return ( empty( $this->options['trackprefix'] ) ) ? '/yoast-ga/' : $this->options['trackprefix'];
		}

		function get_tracking_link( $prefix, $target, $jsprefix = 'javascript:' ) {
			if (
				( $prefix == 'download' && $this->options['downloadspageview'] ) ||
				( $prefix != 'download' && $this->options['outboundpageview'] )
			) {
				$prefix  = $this->get_tracking_prefix() . $prefix;
				$pushstr = "['_trackPageview','" . $prefix . "/" . esc_js( esc_url( $target ) ) . "']";
			} else {
				$pushstr = "['_trackEvent','" . $prefix . "','" . esc_js( esc_url( $target ) ) . "']";
			}
			return $jsprefix . "_gaq.push(" . $pushstr . ");";
		}

		function parse_link( $category, $matches ) {
			$origin = yoast_ga_get_domain( $_SERVER["HTTP_HOST"] );

			// Break out immediately if the link is not an http or https link.
			if ( strpos( $matches[2], "http" ) !== 0 ) {
				$target = false;
			} else if ( ( strpos( $matches[2], "mailto" ) === 0 ) ) {
				$target = 'email';
			} else {
				$target = yoast_ga_get_domain( $matches[3] );
			}
			$trackBit     = "";
			$extension    = substr( strrchr( $matches[3], '.' ), 1 );
			$dlextensions = explode( ",", str_replace( '.', '', $this->options['dlextensions'] ) );
			if ( $target ) {
				if ( $target == 'email' ) {
					$trackBit = $this->get_tracking_link( 'mailto', str_replace( 'mailto:', '', $matches[3] ), '' );
				} else if ( in_array( $extension, $dlextensions ) ) {
					$trackBit = $this->get_tracking_link( 'download', $matches[3], '' );
				} else if ( $target["domain"] != $origin["domain"] ) {
					$crossdomains = array();
					if ( isset( $this->options['othercrossdomains'] ) && !empty( $this->options['othercrossdomains'] ) )
						$crossdomains = explode( ',', str_replace( ' ', '', $this->options['othercrossdomains'] ) );

					if ( isset( $this->options['trackcrossdomain'] ) && $this->options['trackcrossdomain'] && in_array( $target["host"], $crossdomains ) ) {
						$trackBit = '_gaq.push([\'_link\', \'' . $matches[2] . '//' . $matches[3] . '\']); return false;"';
					} else if ( $this->options['trackoutbound'] && in_array( $this->options['domainorurl'], array( 'domain', 'url' ) ) ) {
						$url      = $this->options['domainorurl'] == 'domain' ? $target["host"] : $matches[3];
						$trackBit = $this->get_tracking_link( $category, $url, '' );
					}
				} else if ( $target["domain"] == $origin["domain"] && isset( $this->options['internallink'] ) && $this->options['internallink'] != '' ) {
					$url         = preg_replace( '|' . $origin["host"] . '|', '', $matches[3] );
					$extintlinks = explode( ',', $this->options['internallink'] );
					foreach ( $extintlinks as $link ) {
						if ( preg_match( '|^' . trim( $link ) . '|', $url, $match ) ) {
							$label = $this->options['internallinklabel'];
							if ( $label == '' )
								$label = 'int';
							$trackBit = $this->get_tracking_link( $category . '-' . $label, $url, '' );
						}
					}
				}
			}
			if ( $trackBit != "" ) {
				if ( preg_match( '/onclick=[\'\"](.*?)[\'\"]/i', $matches[4] ) > 0 ) {
					// Check for manually tagged outbound clicks, and replace them with the tracking of choice.
					if ( preg_match( '/.*_track(Pageview|Event).*/i', $matches[4] ) > 0 ) {
						$matches[4] = preg_replace( '/onclick=[\'\"](javascript:)?(.*;)?[a-zA-Z0-9]+\._track(Pageview|Event)\([^\)]+\)(;)?(.*)?[\'\"]/i', 'onclick="javascript:' . $trackBit . '$2$5"', $matches[4] );
					} else {
						$matches[4] = preg_replace( '/onclick=[\'\"](javascript:)?(.*?)[\'\"]/i', 'onclick="javascript:' . $trackBit . '$2"', $matches[4] );
					}
				} else {
					$matches[4] = 'onclick="javascript:' . $trackBit . '"' . $matches[4];
				}
			}
			return '<a ' . $matches[1] . 'href="' . $matches[2] . '//' . $matches[3] . '"' . ' ' . $matches[4] . '>' . $matches[5] . '</a>';
		}

		function parse_article_link( $matches ) {
			return $this->parse_link( 'outbound-article', $matches );
		}

		function parse_comment_link( $matches ) {
			return $this->parse_link( 'outbound-comment', $matches );
		}

		function parse_widget_link( $matches ) {
			return $this->parse_link( 'outbound-widget', $matches );
		}

		function parse_nav_menu( $matches ) {
			return $this->parse_link( 'outbound-menu', $matches );
		}

		function widget_content( $text ) {
			if ( !$this->do_tracking() )
				return $text;
			static $anchorPattern = '/<a (.*?)href=[\'\"](.*?)\/\/([^\'\"]+?)[\'\"](.*?)>(.*?)<\/a>/i';
			$text = preg_replace_callback( $anchorPattern, array( $this, 'parse_widget_link' ), $text );
			return $text;
		}

		function the_content( $text ) {
			if ( !$this->do_tracking() )
				return $text;

			if ( !is_feed() ) {
				static $anchorPattern = '/<a (.*?)href=[\'\"](.*?)\/\/([^\'\"]+?)[\'\"](.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback( $anchorPattern, array( $this, 'parse_article_link' ), $text );
			}
			return $text;
		}

		function nav_menu( $text ) {
			if ( !$this->do_tracking() )
				return $text;

			if ( !is_feed() ) {
				static $anchorPattern = '/<a (.*?)href=[\'\"](.*?)\/\/([^\'\"]+?)[\'\"](.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback( $anchorPattern, array( $this, 'parse_nav_menu' ), $text );
			}
			return $text;
		}

		function comment_text( $text ) {
			if ( !$this->do_tracking() )
				return $text;

			if ( !is_feed() ) {
				static $anchorPattern = '/<a (.*?)href="(.*?)\/\/(.*?)"(.*?)>(.*?)<\/a>/i';
				$text = preg_replace_callback( $anchorPattern, array( $this, 'parse_comment_link' ), $text );
			}
			return $text;
		}

		function comment_author_link( $text ) {
			if ( !$this->do_tracking() )
				return $text;

			static $anchorPattern = '/(.*\s+.*?href\s*=\s*)["\'](.*?)["\'](.*)/';
			preg_match( $anchorPattern, $text, $matches );
			if ( !isset( $matches[2] ) || $matches[2] == "" ) return $text;

			$trackBit = '';
			$target   = yoast_ga_get_domain( $matches[2] );
			$origin   = yoast_ga_get_domain( $_SERVER["HTTP_HOST"] );
			if ( $target["domain"] != $origin["domain"] ) {
				if ( isset( $this->options['domainorurl'] ) && $this->options['domainorurl'] == "domain" )
					$url = $target["host"];
				else
					$url = $matches[2];
				$trackBit = 'onclick="' . $this->get_tracking_link( 'outbound-commentauthor', $url ) . '"';
			}
			return $matches[1] . "\"" . $matches[2] . "\" " . $trackBit . " " . $matches[3];
		}

		function bookmarks( $bookmarks ) {
			if ( !$this->do_tracking() )
				return $bookmarks;

			$i = 0;
			while ( $i < count( $bookmarks ) ) {
				$target     = yoast_ga_get_domain( $bookmarks[$i]->link_url );
				$sitedomain = yoast_ga_get_domain( get_bloginfo( 'url' ) );
				if ( $target['host'] == $sitedomain['host'] ) {
					$i++;
					continue;
				}
				if ( isset( $this->options['domainorurl'] ) && $this->options['domainorurl'] == "domain" )
					$url = $target["host"];
				else
					$url = $bookmarks[$i]->link_url;
				$trackBit = '" onclick="' . $this->get_tracking_link( 'outbound-blogroll', $url );
				$bookmarks[$i]->link_target .= $trackBit;
				$i++;
			}
			return $bookmarks;
		}

		function rsslinktagger( $guid ) {
			global $post;
			if ( is_feed() ) {
				if ( $this->options['allowanchor'] ) {
					$delimiter = '#';
				} else {
					$delimiter = '?';
					if ( strpos( $guid, $delimiter ) > 0 )
						$delimiter = '&amp;';
				}
				return $guid . $delimiter . 'utm_source=rss&amp;utm_medium=rss&amp;utm_campaign=' . urlencode( $post->post_name );
			}
			return $guid;
		}

		function wpec_transaction_tracking( $push ) {
			global $wpdb, $purchlogs, $cart_log_id;
			if ( !isset( $cart_log_id ) || empty( $cart_log_id ) )
				return $push;

			$city = $wpdb->get_var( "SELECT tf.value
		                               FROM " . WPSC_TABLE_SUBMITED_FORM_DATA . " tf
		                          LEFT JOIN " . WPSC_TABLE_CHECKOUT_FORMS . " cf
		                                 ON cf.id = tf.form_id
		                              WHERE cf.type = 'city'
		                                AND log_id = " . $cart_log_id );

			$country = $wpdb->get_var( "SELECT tf.value
		                                  FROM " . WPSC_TABLE_SUBMITED_FORM_DATA . " tf
		                             LEFT JOIN " . WPSC_TABLE_CHECKOUT_FORMS . " cf
		                                    ON cf.id = tf.form_id
		                                 WHERE cf.type = 'country'
		                                   AND log_id = " . $cart_log_id );

			$cart_items = $wpdb->get_results( "SELECT * FROM " . WPSC_TABLE_CART_CONTENTS . " WHERE purchaseid = " . $cart_log_id, ARRAY_A );

			$total_shipping = $purchlogs->allpurchaselogs[0]->base_shipping;
			$total_tax      = 0;
			foreach ( $cart_items as $item ) {
				$total_shipping += $item['pnp'];
				$total_tax += $item['tax_charged'];
			}

			$push[] = "'_addTrans','" . $cart_log_id . "'," // Order ID
				. "'" . $this->str_clean( get_bloginfo( 'name' ) ) . "'," // Store name
				. "'" . nzshpcrt_currency_display( $purchlogs->allpurchaselogs[0]->totalprice, 1, true, false, true ) . "'," // Total price
				. "'" . nzshpcrt_currency_display( $total_tax, 1, true, false, true ) . "'," // Tax
				. "'" . nzshpcrt_currency_display( $total_shipping, 1, true, false, true ) . "'," // Shipping
				. "'" . $city . "'," // City
				. "''," // State
				. "'" . $country . "'"; // Country

			foreach ( $cart_items as $item ) {
				$item['sku'] = $wpdb->get_var( "SELECT meta_value FROM " . WPSC_TABLE_PRODUCTMETA . " WHERE meta_key = 'sku' AND product_id = '" . $item['prodid'] . "' LIMIT 1" );

				$item['category'] = $wpdb->get_var( "SELECT pc.name FROM " . WPSC_TABLE_PRODUCT_CATEGORIES . " pc LEFT JOIN " . WPSC_TABLE_ITEM_CATEGORY_ASSOC . " ca ON pc.id = ca.category_id WHERE pc.group_id = '1' AND ca.product_id = '" . $item['prodid'] . "'" );
				$push[]           = "'_addItem',"
					. "'" . $cart_log_id . "'," // Order ID
					. "'" . $item['sku'] . "'," // Item SKU
					. "'" . str_replace( "'", "", $item['name'] ) . "'," // Item Name
					. "'" . $item['category'] . "'," // Item Category
					. "'" . $item['price'] . "'," // Item Price
					. "'" . $item['quantity'] . "'"; // Item Quantity
			}
			$push[] = "'_trackTrans'";

			return $push;
		}

		function shopp_transaction_tracking( $push ) {
			global $Shopp;

			// Only process if we're in the checkout process (receipt page)
			if ( version_compare( substr( SHOPP_VERSION, 0, 3 ), '1.1' ) >= 0 ) {
				// Only process if we're in the checkout process (receipt page)
				if ( function_exists( 'is_shopp_page' ) && !is_shopp_page( 'checkout' ) ) return $push;
				if ( empty( $Shopp->Order->purchase ) ) return $push;

				$Purchase = new Purchase( $Shopp->Order->purchase );
				$Purchase->load_purchased();
			} else {
				// For 1.0.x
				// Only process if we're in the checkout process (receipt page)
				if ( function_exists( 'is_shopp_page' ) && !is_shopp_page( 'checkout' ) ) return $push;
				// Only process if we have valid order data
				if ( !isset( $Shopp->Cart->data->Purchase ) ) return $push;
				if ( empty( $Shopp->Cart->data->Purchase->id ) ) return $push;

				$Purchase = $Shopp->Cart->data->Purchase;
			}

			$push[] = "'_addTrans',"
				. "'" . $Purchase->id . "'," // Order ID
				. "'" . $this->str_clean( get_bloginfo( 'name' ) ) . "'," // Store
				. "'" . number_format( $Purchase->total, 2 ) . "'," // Total price
				. "'" . number_format( $Purchase->tax, 2 ) . "'," // Tax
				. "'" . number_format( $Purchase->shipping, 2 ) . "'," // Shipping
				. "'" . $Purchase->city . "'," // City
				. "'" . $Purchase->state . "'," // State
				. "'.$Purchase->country.'"; // Country

			foreach ( $Purchase->purchased as $item ) {
				$sku    = empty( $item->sku ) ? 'PID-' . $item->product . str_pad( $item->price, 4, '0', STR_PAD_LEFT ) : $item->sku;
				$push[] = "'_addItem',"
					. "'" . $Purchase->id . "',"
					. "'" . $sku . "',"
					. "'" . str_replace( "'", "", $item->name ) . "',"
					. "'" . $item->optionlabel . "',"
					. "'" . number_format( $item->unitprice, 2 ) . "',"
					. "'" . $item->quantity . "'";
			}
			$push[] = "'_trackTrans'";
			return $push;
		}

		function track_comment_form() {
			if ( !is_singular() )
				return;

			global $comment_form_id;
			?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#<?php echo $comment_form_id; ?>').submit(function () {
                    _gaq.push(
                            ['_setAccount', '<?php echo $this->options["uastring"]; ?>'],
                            ['_trackEvent', 'comment', 'submit']
                    );
                });
            });
        </script>
		<?php
		}

		function track_comment_form_head() {
			if ( !is_singular() )
				return;

			global $post;
			if ( 'open' == $post->comment_status )
				wp_enqueue_script( 'jquery' );
		}

		function get_comment_form_id( $args ) {
			global $comment_form_id;
			$comment_form_id = $args['id_form'];
			return $args;
		}

	} // class GA_Filter
} // endif

$yoast_ga = new GA_Filter();

function yoast_analytics() {
	global $yoast_ga;
	$options = get_option( 'Yoast_Google_Analytics' );
	if ( $options['position'] == 'manual' )
		$yoast_ga->spool_analytics();
	else
		echo '<!-- ' . __( 'Please set Google Analytics position to "manual" in the settings, or remove this call to yoast_analytics();', 'gawp' ) . ' -->';
}

