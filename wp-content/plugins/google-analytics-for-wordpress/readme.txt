=== Google Analytics for WordPress ===
Contributors: joostdevalk
Donate link: http://yoast.com/donate/
Tags: analytics, google analytics, statistics, tracking, stats, google
Requires at least: 3.3
Tested up to: 3.5
Stable tag: 4.3.3

Track your WordPress site easily and with lots of metadata: views per author & category, automatic tracking of outbound clicks and pageviews.

== Description ==

The Google Analytics for WordPress plugin allows you to track your blog easily and with lots of metadata. 

Check out the [Google Analytics for WordPress video](http://www.youtube.com/watch?v=tnUXzbvXxSQ):

http://www.youtube.com/watch?v=tnUXzbvXxSQ&hd=1

Full list of features:

* Simple installation through integration with Google Analytics API: authenticate, select the site you want to track and you're done.
* This plugin uses the asynchronous Google Analytics tracking code, the fastest and most reliable tracking code Google Analytics offers.
* Option to manually place the tracking code in another location.
* Automatic Google Analytics site speed tracking.
* Outbound link & downloads tracking.
	* Configurable options to track outbound links either as pageviews.
	* Option to track just downloads as pageviews in Google Analytics.
* Allows usage of custom variables in Google Analytics to track meta data on pages. Support for the following custom variables:
	* Author
	* Single category and / or multiple categories
	* Post type (especially useful if you use custom post types)
	* Logged in users
	* Publication Year
	* Tags
* Possibility to ignore any user level and up, so all editors and higher for instance.
* Easily connect your Google AdSense and Google Analytics accounts.
* Option to tag links with Google Analytics campaign tracking, with the option to use hashes (#).
* Option anonymize IP's, for use in countries like Germany.
* Full [debug mode](http://yoast.com/google-analytics-debug-mode/), including Firebug lite and ga_debug.js for debugging Google Analytics issues.
* Allow local hosting of ga.js file.
* Tracking of search engines not included in Google Analytics default tracking.
* Tracking of login and registration forms.

Other interesting stuff:

* Check out the other [WordPress Plugins](http://yoast.com/wordpress/) by the same author.
* Want to increase traffic to your WordPress blog? Check out the [WordPress SEO](http://yoast.com/articles/wordpress-seo/) Guide!
* Check out the authors [WordPress Hosting](http://yoast.com/articles/wordpress-hosting/) experience. Good hosting is hard to come by, but it doesn't have to be expensive, Joost tells you why!

== Installation ==

This section describes how to install the plugin and get it working.

1. Delete any existing `gapp` or `google-analytics-for-wordpress` folder from the `/wp-content/plugins/` directory
1. Upload `google-analytics-for-wordpress` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the options panel under the 'Settings' menu and add your Analytics account number and set the settings you want.

== Changelog ==

= 4.3.3 =

* Fix a possible fatal error in tracking.

= 4.3.2 =

* Bugfix: Google Analytics crappy API output is different when you have a single GA account versus multiple. Annoying, but fixed now.

= 4.3.1 =

* Removes a left over JS alert.

= 4.3 =

* Major refactor of plugin code, to only load necessary code on front and backend.
* Made entire plugin i18n ready.
* Fixed Google Authentication process (thanks to [Jan Willem Eshuis](http://www.janwillemeshuis.nl/)).

= 4.2.8 =

* Fix a small bug in tracking that could potentially slow down admin.

= 4.2.7 =

* Fix to prevent far too agressive oAuth implementation from breaking other plugins.

= 4.2.6 =

* Fix to prevent far too agressive oAuth implementation from breaking other plugins.

= 4.2.5 =

* Fixed a couple notices.
* Added tracking to better understand configurations to test the plugin with.

= 4.2.4 =

* Fixed bug introduced with 4.2.3 that wouldn't allow saving settings.
* Now only flushing enabled W3TC caches.

= 4.2.3 =

* Removed Dashboard widget.
* Improvements to comment form tracking.

= 4.2.2 =

* Fix for OAuth issues, caused by other plugins that don't check for the existence of a class. Namespaced the whole thing to prevent it.

= 4.2.1 =

* Minor bugfix.

= 4.2 =

* Google Authentication now happens using OAuth. The requests have become signed as an extra security measure and tokens have become more stable, as opposed to the prior tokens used with AuthSub.
* Added support for cross-domain tracking.
* Fixed various small bugs.

= 4.1.3 =

* Security fix: badly crafted comments could lead to insertion of "weird" links into comments. They'd have to pass your moderation, but still... Immediate update advised. Props to David Whitehouse and James Slater for finding it.

= 4.1.2 =

* Fixed bug with custom SE tracking introduced in 4.1.1.

= 4.1.1 =

* Made plugin admin work with jQuery 1.6 and jQuery 1.4.
* Added contextual help.
* Improved cache flushing when using W3TC.
* Fixed various minor other notices.
* First stab at getting ready for full i18n compatibility.

= 4.1 =

* Added:
	* Google Site Speed tracking, turned it on by default.
	
* Fixed:
	* Custom code now properly removes slashes.
	
= 4.0.12 =

* Fixed:
	* Tons of notices in backend and front end when no settings were saved yet.
	* Set proper defaults for all variables.
	* Notice for unset categories array on custom post types.
	* Notice for unset variable.
	* Error when user is not logged in in certain corner cases.
	* Bug where $options was used but never loaded for blogroll links.
	
= 4.0.11 =

* Bugs fixed:
	* You can now disable comment form tracking properly.
	* Removed charset property from script tags to allow validation with HTML5 doctype.
	
= 4.0.10 =

* Known issues:
	* Authentication with Google gives errors in quite a few cases. Please use the manual option to add your UA code until we find a way to reliably fix it.
	
* Added functionality:
	* Option to set `_setAllowHash` to false, for proper subdomain tracking and some other uses.
	* Option to add a custom string of code to the tracking, before the push string is sent out.

* Documentation fixes:
	* Fixed link for `_setDomainName()`.
	* Fixed some grammatical errors (keep emailing me about those, please!)
	* Removed second comment in source output.
	* Fixed version number output in source.

= 4.0.9 =

* Code enhancements:
	* Updated Shopp integration to also work with the upcoming Shopp 1.1 and higher.
	* Switched from [split](http://php.net/split) to [explode](http://php.net/explode), as split has been deprecated in PHP 5.3+.
* New features:
	* A new debug mode has been added, using the new [ga_debug.js](http://analytics.blogspot.com/2010/08/new-tools-to-debug-your-tracking-code.html). Along with this you can now enable Firebug Lite, so you can easily see the output from the debug script in each browser. Admins only, of course.
	* A list of modules has been added to the right sidebar, to allow easy navigation within the settings page.

= 4.0.8 =
* Reverted double quote change from 4.0.7 because it was causing bigger issues.

= 4.0.7 =
* Bugs fixed in this release:
	* Changed access level from "edit_users" to "manage_options" so super-admins in an multi site environment would be able to access.
	* Not a real bug but a fix nonetheless: UA ID is now trimmed on output, so spaces accidently entered in the settings screen won't prevent tracking.
	* Changed double quotes in link tracking output to single quotes to resolve incompatibilities with several plugins.

= 4.0.6 =
* Bugs fixed in this release:
	* Sanitizing relative URL's could go wrong on some blogs installed in subdirectories.
	* Comment form tracking only worked for posts, not for pages, and would sometimes cause other issues. Patch by [Milan Dinić](http://blog.milandinic.com/).
	* Settings page: now correctly hiding internal links to track as outbound block when outbound link tracking is disabled.
* Code sanitization:
	* Hardcoded the [scope for custom variables](http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._setCustomVar) to prevent that from possibly going wrong.
	* Improved method of determining whether current user should be tracked or not.
	* Added plugin version number in script branding comment, and moved branding comment to within CDATA section to assist in debugging, even when people use W3TC or another form of code minification.
* Documentation fixes:
	* Updated custom variable order in settings panel to reflect order of tracking. You can now determine their index key by counting down, first checked box is index 1, second 2, etc.
	* Ignored users dropdown now correctly reflects that ignoring subcribers and up means ignoring ALL logged in users.
	
= 4.0.5 =
* New features in this release: 
	* Added a simple check to see if the UA code, when entered manually, matches a basic pattern of UA-1234567-12.
	* Added integration with [W3 Total Cache](http://wordpress.org/extend/plugins/w3-total-cache/) and [WP Super Cache](http://wordpress.org/extend/plugins/wp-super-cache/). The page cache is now automatically cleared after updating settings. Caching is recommended for all WordPress users, as faster page loads improve tracking reliability and W3 Total Cache is our recommended caching plugin.
	* Added the option to enter a manual location for ga.js, allowing you to host it locally should you wish to.
* Bugs fixed:
	* Fixed implementation of _anonymizeIp, it now correctly anonymizes IP's by setting [_gat._anonymizeIp](http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gat.html#_gat._anonymizeIp).
	* Increased request timeout time for Google Analytics authentication from 10 to 20 seconds, for slow hosts (if this fixes it for you, your hosting is probably not really good, consider another WordPress host).
* Documentation fixes:
	* Added a note about profiles with the same UA code to the Analytics Profile selection.
	* The profile selection dropdown now shows the UA code after the profile name too.
	* Updated the [screenshots](http://wordpress.org/extend/plugins/google-analytics-for-wordpress/screenshots/) and the [FAQ](http://wordpress.org/extend/plugins/google-analytics-for-wordpress/faq/) for this plugin.

= 4.0.4 =
* Fix for stupid boolean mistake in 4.0.3.

= 4.0.3 =
* New features in this release: 
	* Added versioning to the options array, to allow for easy addition of options.
	* Added an option to enable comment form tracking (as this loads jQuery), defaults to on.
* Bugs fixed:
	* If you upgraded from before 4.0 to 4.0.2 you might have an empty value for ignore_userlevel in some edge cases, this is now fixed.
	* Custom search engines were loaded after trackPageview, this was wrong as shown [by these docs](http://code.google.com/intl/sr/apis/analytics/docs/tracking/asyncMigrationExamples.html#SearchEngines), now fixed.

= 4.0.2 =
* Old settings from versions below 4.0 are now properly sanitized and inherited (slaps forehead about simplicity of fix).
* New features in this release: 
	* Link sanitization added: relative links will be rewritten to absolute, so /out/ becomes http://example.com/out/ and is tracked properly.
	* Added a feature to track and label internal links as outbound clicks, for instance /out/ links.
	* Added tracking for mailto: links.
	* Added a filter for text-widgets, all links in those widgets are now tagged too.
	* Added support for [_anonymizeIp](http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gat.html#_gat._anonymizeIp).
* Bugs fixed in this release:
	* Made sure all content filters don't run when the current user is ignored because of his user level.

= 4.0.1 =
* Fix for when you have only 1 site in a specific Analytics profile.

= 4.0 =
* NOTE WHEN UPGRADING: you'll have to reconfigure the plugin so it can fully support all the new features!
* Complete rewrite of the codebase
* Switched to the new asynchronous event tracking model
* Switched link tracking to an event tracking model, because of this change removed 5 settings from the settings panel that were no longer needed
* Implemented custom variable tracking to track: 
	* On the session level: whether the user is logged in or not. 
	* On the page level: the current posts's author, category, tags, year of publication and post type.
* Added Google Analytics API integration, so you can easily select a site to track.
* E-Commerce integration, tracking transactions, support for WP E-Commerce and Shopp.
* Much much more: check out [the release post](http://yoast.com/google-analytics-wordpress-v4/).

= 3.2.3 =
* Added 0 result search tracking inspired by [Justin Cutroni's post](http://www.epikone.com/blog/2009/09/08/tracking-ero-result-searches-in-google-analytics/).

= 3.2.2 =
* Fix to the hashtag redirect so it actually works in all cases.

= 3.2.1 =
* Slight change to RSS URL tagging, now setting campaign to post name, and behaving better when not using rewritten URL's.
* Two patches by [Lee Willis](http://www.leewillis.co.uk):
	* Made some changes so the entire plugin works fine with .co.uk, .co.za etc domains.
	* Made sure internal blogroll links aren't tagged as external clicks.

= 3.2 =
* Added option to add tracking to add tracking to login / register pages, so you can track new signups (under Advanced settings).
* Added beta option to track Google image search as a search engine, needs more testing to make sure it works.
* Fixed a bug in the extra search engine tracking implementation.
* Removed redundant "More Info" section from readme.txt.

= 3.1.1 =
* Stupid typo that caused warnings.

= 3.1 =
* Added 404 tracking as described [here](http://www.google.com/support/googleanalytics/bin/answer.py?hl=en&answer=86927).
* Optimized the tracking script, if extra search engine tracking is disabled it'll be a lot smaller now.
* Various code optimizations to prevent PHP notices and removal of redundant code.

= 3.0.1 =
* Removed no longer needed code to add config page that caused PHP warnings.

= 3.0 =
* Major backend overhaul, using new Yoast backend class.
* Added ability to automatically redirect non hashtagged campaign URLs to hashtagged campaign URL's when setAllowAnchor is set to true (if you don't get it, forget about it, you might need it but don't need to worry)

= 2.9.5 =
* Fixed a bug with the included RSS, which came up when multiple Yoast plugins were installed.

= 2.9.4 =
* Changed to the new Changelog design.
* Removed pre 2.6 compatibility code, plugin now requires WP 2.6 or higher.
* Small changes to the admin screen.

= 2.9.3 =
* Added a new option for RSS link tagging, which allows you to tag your RSS feed links with RSS campaign variables. When you've set campaign variables to use # instead of ?, this will adhere to that setting too. Thanks to [Timan Rebel](http://rebelic.nl/) for the idea and code.

= 2.9.2: =
* Added a check to see whether the wp_footer() call is in footer.php.
* Added a message to the source when tracking code is left out because user is logged in as admin.
* Added option to segment logged in users.
* Added try - catch to script lines like in new Google Analytics scripts.
* Fixed bug in warning when no UA code is entered.
* Prevent link tracking when admin is logged in and admin tracking is disabled.
* Now prevents parsing of non http and https link.

= 2.9 = 
* Re arranged admin panel to have "standard" and "advanced" settings.
* Added domain tracking.
* Added fix for double onclick parameter, as suggested [here](http://wordpress.org/support/topic/241757).

= 2.8 = 
* Added the option to add setAllowAnchor to the tracking code, allowing you to track campaigns with # instead of ?.

= 2.7 = 
* Added option to select either header of footer position.
* Added new AdSense integration options.
* Removed now unneeded adsense tracking script.

= 2.6.6=
* Fixed settings link.

= 2.6.5 = 
* added Ozh admin menu icon and settings link.

= 2.6.4 = 
* Fixes for 2.7.

= 2.6.3 = 
* Fixed bug that didn't allow saving of outbound clicks from comments string.

= 2.6 =
* Fixed incompatibility with WP 2.6.

= 2.5.4 =
* Fixed an issue with pluginpath being used globally.
* Changed links to [new domain](http://yoast.com/).

= 2.2 = 
* Switched to the new tracking code.

= 2.1 = 
* Made sure tracking was disabled on preview pages.

= 2.0 = 
* Added AdSense tracking.

= 1.5 =
* Added option to enable admin tracking, off by default.

== Frequently Asked Questions ==

= Can I run this plugin together with another Google Analytics plugin? =

No. You can not. It will break tracking.

= Another profile than the one I selected is showing as selected? =

You probably have multiple profiles for the same website, that share the same UA-code. If so, it doesn't matter which of the profiles is shown as selected, tracking will be correct.

= I've just installed the new tracking and Google Analytics says it's not receiving data yet? =

Give it a couple of hours, usually it'll be fixed. It can take up to 24 hours to appear though.

= Google Analytics says it's receiving data, but I don't see any stats yet? =

This can take up to 24 hours after the installation of the new tracking code.

= Why is the tracking code loaded in the head section of the site? =

Because that's where it belongs. It makes the page load faster (yes, faster, due to the asynchronous method of loading the script) and tracking more reliable. If you must place it in the footer anyway, switch to manual mode and check out the docs for [manual placement of the Google Analytics code](http://yoast.com/wordpress/google-analytics/manual-placement/).

== Screenshots ==

1. Screenshot of the basic settings panel for this plugin.
2. Screenshot of the custom variable settings panel.
3. Screenshot of the link tracking panel.
4. Screenshot of the advanced settings panel.
5. Screenshot of the debugging mode in action.

== Upgrade Notice ==
