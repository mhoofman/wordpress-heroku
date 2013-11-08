<?php

/*
 $Id: sitemap.php 779425 2013-09-27 22:49:51Z arnee $

 Google XML Sitemaps Generator for WordPress
 ==============================================================================

 This generator will create a sitemaps.org compliant sitemap of your WordPress blog.
 Currently homepage, posts, static pages, categories, archives and author pages are supported.

 The priority of a post depends on its comments. You can choose the way the priority
 is calculated in the options screen.

 Feel free to visit my website under www.arnebrachhold.de!

 For aditional details like installation instructions, please check the readme.txt and documentation.txt files.

 Have fun!
   Arne


 Info for WordPress:
 ==============================================================================
 Plugin Name: Google XML Sitemaps
 Plugin URI: http://www.arnebrachhold.de/redir/sitemap-home/
 Description: This plugin will generate a special XML sitemap which will help search engines like Google, Yahoo, Bing and Ask.com to better index your blog.
 Version: 3.3
 Author: Arne Brachhold
 Author URI: http://www.arnebrachhold.de/
 Text Domain: sitemap
 Domain Path: /lang/

*/

/**
 * Loader class for the Google Sitemap Generator
 *
 * This class takes care of the sitemap plugin and tries to load the different parts as late as possible.
 * On normal requests, only this small class is loaded. When the sitemap needs to be rebuild, the generator itself is loaded.
 * The last stage is the user interface which is loaded when the administration page is requested.
 */
class GoogleSitemapGeneratorLoader {
	/**
	 * Enabled the sitemap plugin with registering all required hooks
	 *
	 * If the sm_command and sm_key GET params are given, the function will init the generator to rebuild the sitemap.
	 */
	function Enable() {

		//Check for 3.0 multisite, NOT supported yet!
		if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite())) {
			if(function_exists('is_super_admin') && is_super_admin()) {
				add_action('admin_notices',  array('GoogleSitemapGeneratorLoader', 'AddMultisiteWarning'));
			}

			return;
		}

		//Register the sitemap creator to wordpress...
		add_action('admin_menu', array('GoogleSitemapGeneratorLoader', 'RegisterAdminPage'));

		//Nice icon for Admin Menu (requires Ozh Admin Drop Down Plugin)
		add_filter('ozh_adminmenu_icon', array('GoogleSitemapGeneratorLoader', 'RegisterAdminIcon'));

		//Additional links on the plugin page
		add_filter('plugin_row_meta', array('GoogleSitemapGeneratorLoader', 'RegisterPluginLinks'),10,2);

		//Existing posts was deleted
		add_action('delete_post', array('GoogleSitemapGeneratorLoader', 'CallCheckForAutoBuild'),9999,1);

		//Existing post was published
		add_action('publish_post', array('GoogleSitemapGeneratorLoader', 'CallCheckForAutoBuild'),9999,1);

		//Existing page was published
		add_action('publish_page', array('GoogleSitemapGeneratorLoader', 'CallCheckForAutoBuild'),9999,1);

		//WP Cron hook
		add_action('sm_build_cron', array('GoogleSitemapGeneratorLoader', 'CallBuildSitemap'),1,0);

		//External build hook
		add_action('sm_rebuild', array('GoogleSitemapGeneratorLoader', 'CallBuildNowRequest'),1,0);

		//Robots.txt request
		add_action('do_robots', array('GoogleSitemapGeneratorLoader', 'CallDoRobots'),100,0);

		//Help topics for context sensitive help
		add_filter('contextual_help_list', array('GoogleSitemapGeneratorLoader', 'CallHtmlShowHelpList'),9999,2);

		//Check if this is a BUILD-NOW request (key will be checked later)
		if(!empty($_GET["sm_command"]) && !empty($_GET["sm_key"])) {
			GoogleSitemapGeneratorLoader::CallCheckForManualBuild();
		}

		//Check if the result of a ping request should be shown
		if(!empty($_GET["sm_ping_service"])) {
			GoogleSitemapGeneratorLoader::CallShowPingResult();
		}
	}

	/**
	 * Outputs the warning bar if multisite mode is activated
	 */
	function AddMultisiteWarning() {
		echo "<div id='sm-multisite-warning' class='error fade'><p><strong>".__('This version of Google XML Sitemaps is not multisite compatible.','sitemap')."</strong><br /> ".sprintf(__('Unfortunately this version of the Google XML Sitemaps plugin was not tested with the multisite feature of WordPress 3.0 yet. <br />The plugin will not be active until you disable the multisite mode. <br />Or you can <a href="%1$s">try the new beta</a> which supports all the new WordPress features!','sitemap'), "http://www.arnebrachhold.de/redir/sitemap-info-beta/")."</p></div>";
	}

	/**
	 * Registers the plugin in the admin menu system
	 */
	function RegisterAdminPage() {

		if (function_exists('add_options_page')) {
			add_options_page(__('XML-Sitemap Generator','sitemap'), __('XML-Sitemap','sitemap'), 'level_10', GoogleSitemapGeneratorLoader::GetBaseName(), array('GoogleSitemapGeneratorLoader','CallHtmlShowOptionsPage'));
		}
	}

	function RegisterAdminIcon($hook) {
		if ( $hook == GoogleSitemapGeneratorLoader::GetBaseName() && function_exists('plugins_url')) {
			return plugins_url('img/icon-arne.gif',GoogleSitemapGeneratorLoader::GetBaseName());
		}
		return $hook;
	}

	function RegisterPluginLinks($links, $file) {
		$base = GoogleSitemapGeneratorLoader::GetBaseName();
		if ($file == $base) {
			$links[] = '<a href="options-general.php?page=' . GoogleSitemapGeneratorLoader::GetBaseName() .'">' . __('Settings','sitemap') . '</a>';
			$links[] = '<a href="http://www.arnebrachhold.de/redir/sitemap-plist-faq/">' . __('FAQ','sitemap') . '</a>';
			$links[] = '<a href="http://www.arnebrachhold.de/redir/sitemap-plist-support/">' . __('Support','sitemap') . '</a>';
			$links[] = '<a href="http://www.arnebrachhold.de/redir/sitemap-plist-donate/">' . __('Donate','sitemap') . '</a>';
		}
		return $links;
	}

	/**
	 * Invokes the HtmlShowOptionsPage method of the generator
	 */
	function CallHtmlShowOptionsPage() {
		if(GoogleSitemapGeneratorLoader::LoadPlugin()) {
			$gs = &GoogleSitemapGenerator::GetInstance();
			$gs->HtmlShowOptionsPage();
		}
	}

	/**
	 * Invokes the CheckForAutoBuild method of the generator
	 */
	function CallCheckForAutoBuild($args) {
		if(GoogleSitemapGeneratorLoader::LoadPlugin()) {
			$gs = &GoogleSitemapGenerator::GetInstance();
			$gs->CheckForAutoBuild($args);
		}
	}

	/**
	 * Invokes the CheckForAutoBuild method of the generator
	 */
	function CallBuildNowRequest() {
		if(GoogleSitemapGeneratorLoader::LoadPlugin()) {
			$gs = &GoogleSitemapGenerator::GetInstance();
			$gs->BuildNowRequest();
		}
	}

	/**
	 * Invokes the BuildSitemap method of the generator
	 */
	function CallBuildSitemap() {
		if(GoogleSitemapGeneratorLoader::LoadPlugin()) {
			$gs = &GoogleSitemapGenerator::GetInstance();
			$gs->BuildSitemap();
		}
	}

	/**
	 * Invokes the CheckForManualBuild method of the generator
	 */
	function CallCheckForManualBuild() {
		if(GoogleSitemapGeneratorLoader::LoadPlugin()) {
			$gs = &GoogleSitemapGenerator::GetInstance();
			$gs->CheckForManualBuild();
		}
	}

	/**
	 * Invokes the ShowPingResult method of the generator
	 */
	function CallShowPingResult() {
		if(GoogleSitemapGeneratorLoader::LoadPlugin()) {
			$gs = &GoogleSitemapGenerator::GetInstance();
			$gs->ShowPingResult();
		}
	}


	function CallHtmlShowHelpList($filterVal,$screen) {

		$id = get_plugin_page_hookname(GoogleSitemapGeneratorLoader::GetBaseName(),'options-general.php');

		if($screen == $id) {
			$links = array(
				__('Plugin Homepage','sitemap')=>'http://www.arnebrachhold.de/redir/sitemap-help-home/',
				__('My Sitemaps FAQ','sitemap')=>'http://www.arnebrachhold.de/redir/sitemap-help-faq/'
			);

			$filterVal[$id] = '';

			$i=0;
			foreach($links AS $text=>$url) {
				$filterVal[$id].='<a href="' . $url . '">' . $text . '</a>' . ($i < (count($links)-1)?'<br />':'') ;
				$i++;
			}
		}
		return $filterVal;
	}

	function CallDoRobots() {
		if(GoogleSitemapGeneratorLoader::LoadPlugin()) {
			$gs = &GoogleSitemapGenerator::GetInstance();
			$gs->DoRobots();
		}
	}

	/**
	 * Loads the actual generator class and tries to raise the memory and time limits if not already done by WP
	 *
	 * @return boolean true if run successfully
	 */
	function LoadPlugin() {

		$mem = abs(intval(@ini_get('memory_limit')));
		if($mem && $mem < 64) {
			@ini_set('memory_limit', '64M');
		}

		$time = abs(intval(@ini_get("max_execution_time")));
		if($time != 0 && $time < 120) {
			@set_time_limit(120);
		}

		if(!class_exists("GoogleSitemapGenerator")) {

			$path = trailingslashit(dirname(__FILE__));

			if(!file_exists( $path . 'sitemap-core.php')) return false;
			require_once($path. 'sitemap-core.php');
		}

		GoogleSitemapGenerator::Enable();
		return true;
	}

	/**
	 * Returns the plugin basename of the plugin (using __FILE__)
	 *
	 * @return string The plugin basename, "sitemap" for example
	 */
	function GetBaseName() {
		return plugin_basename(__FILE__);
	}

	/**
	 * Returns the name of this loader script, using __FILE__
	 *
	 * @return string The __FILE__ value of this loader script
	 */
	function GetPluginFile() {
		return __FILE__;
	}

	/**
	 * Returns the plugin version
	 *
	 * Uses the WP API to get the meta data from the top of this file (comment)
	 *
	 * @return string The version like 3.1.1
	 */
	function GetVersion() {
		if(!isset($GLOBALS["sm_version"])) {
			if(!function_exists('get_plugin_data')) {
				if(file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) require_once(ABSPATH . 'wp-admin/includes/plugin.php'); //2.3+
				else if(file_exists(ABSPATH . 'wp-admin/admin-functions.php')) require_once(ABSPATH . 'wp-admin/admin-functions.php'); //2.1
				else return "0.ERROR";
			}
			$data = get_plugin_data(__FILE__, false, false);
			$GLOBALS["sm_version"] = $data['Version'];
		}
		return $GLOBALS["sm_version"];
	}
}

//Enable the plugin for the init hook, but only if WP is loaded. Calling this php file directly will do nothing.
if(defined('ABSPATH') && defined('WPINC')) {
	add_action("init",array("GoogleSitemapGeneratorLoader","Enable"),1000,0);
}
?>