<?php
/*
Plugin Name: Google Webmaster Tools
Description: Connect your Google Webmaster Tools to your admin area.
Version: 0.1.3
Plugin URI: http://microdataproject.org
Plugin URI: mailto:contact@microdataproject.org
Author: Christopher Dubeau
Author URI: mailto:me@christopherdubeau.com
Author URI: http://christopherdubeau.com
Contributor: Sid Creations
Contributor URI: mailto:contact@sidcreations.com
Contributor URI: http://sidcreations.com


Copyright 2013  Microdata Project / Christopher Dubeau  (email : me@christopherdubeau.com, email: contact@microdataproject.org)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


// DEFINE PLUGIN ID
define('MDP_GWT_WT_ID', 'mdp_gwt_WT');
// DEFINE PLUGIN NICK
define('MDP_GWT_WT_NICK', 'Webmaster Tools');
// DEFINE PLUGIN VERSION
define('MDP_GWT_WT_VERSION', '0.1.0');
//DEFINE PLUGIN DIR
define('MDP_GWT_DIR', WP_PLUGIN_DIR . '/' . str_replace(
		basename(__FILE__), "",
		plugin_basename(__FILE__)
	));
define('MDP_GWT_DOMAIN', str_replace('http://', '', str_replace('www.', '', get_bloginfo('url'))));


if (!class_exists('mdpWebmasterTools')) {

	class mdpWebmasterTools
	{

		public $mdp_gwt_gm_name;

		public function __construct()
		{

			$this->name = 'mdpWebmasterTools';

			register_activation_hook(__FILE__, array($this, 'mdp_gwt_activate'));
			register_deactivation_hook(__FILE__, array($this, 'mdp_gwt_deactivate'));
			register_uninstall_hook(__FILE__, array($this, 'mdp_gwt_uninstall'));

		}

		/** function/activate
		 * Usage: create tables if not exist and activates the plugin
		 * Arg(0): null
		 * Return: void
		 */

		public function mdp_gwt_activate()
		{

			add_option('mdp_gwt_email');
			add_option('mdp_gwt_password');
			add_option('mdp_gwt_position');
			add_option('mdp_gwt_allsites');
			add_option('mdp_gwt_hook');
			add_option("mdp_gwt_version", MDP_GWT_WT_VERSION);

			if (!wp_next_scheduled('mdp_gwt_hook')) {
				wp_schedule_event(time(), 'hourly', 'mdp_gwt_hook');
			}

		}

		/** function/deactivate
		 * Usage: create tables if not exist and activates the plugin
		 * Arg(0): null
		 * Return: void
		 */

		public function mdp_gwt_deactivate()
		{

			unregister_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_email');
			unregister_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_password');
			unregister_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_position');
			unregister_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_allsites');
			unregister_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_version');
			unregister_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_hook');

		}

		/** function/uninstall
		 * Usage: create tables if not exist and activates the plugin
		 * Arg(0): null
		 * Return: void
		 */

		public function mdp_gwt_uninstall()
		{

			delete_option('mdp_gwt_email');
			delete_option('mdp_gwt_password');
			delete_option('mdp_gwt_position');
			delete_option('mdp_gwt_allsites');
			delete_option('mdp_gwt_version');
			delete_option('mdp_gwt_hook');

			global $wpdb;
			$table_query = $wpdb->prefix . "mdp_gwt_query";
			$table_pages = $wpdb->prefix . "mdp_gwt_pages";

			$wpdb->query("DROP TABLE IF EXISTS $table_pages");
			$wpdb->query("DROP TABLE IF EXISTS $table_query");

		}

		/** function/file_path
		 * Usage: includes the plugin file path
		 * Arg(0): null
		 * Return: void
		 */

		public static function mdp_gwt_file_path($file)
		{

			return ABSPATH . 'wp-content/plugins/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)) . $file;
		}


		/** function/register_settings
		 * Usage: registers the plugins options
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_register()
		{

			register_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_email');
			register_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_password');
			register_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_position');
			register_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_allsites');
			register_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_version');
			register_setting(MDP_GWT_WT_ID . '_options', 'mdp_gwt_hook');

			global $wpdb;

			$table_name_queries = $wpdb->prefix . "mdp_gwt_query";
			$table_name_pages = $wpdb->prefix . "mdp_gwt_pages";
			$table_name_keywords = $wpdb->prefix . "mdp_gwt_keywords";
			$table_name_external_links = $wpdb->prefix . "mdp_gwt_external_links";
			$table_name_internal_links = $wpdb->prefix . "mdp_gwt_internal_links";

			$sql_pages = "CREATE TABLE $table_name_pages (
		                        id mediumint(9) NOT NULL AUTO_INCREMENT,
		                        query_id VARCHAR(1000) NOT NULL,
		                        query TEXT NOT NULL,
		                        impressions VARCHAR(20) NOT NULL,
		                        impressions_change VARCHAR(20) NOT NULL,
		                        clicks VARCHAR(20) NOT NULL,
		                        clicks_change VARCHAR(20) NOT NULL,
		                        ctr VARCHAR(20) NOT NULL,
		                        ctr_change VARCHAR(20) NOT NULL,
		                        avg_position VARCHAR(20) NOT NULL,
		                        avg_position_change VARCHAR(20) NOT NULL,
		                        date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		                        PRIMARY KEY id (id),
		                        UNIQUE KEY query_id (query_id)
		                        );
                   		";

			$sql_queries = "CREATE TABLE $table_name_queries (
		                        id mediumint(9) NOT NULL AUTO_INCREMENT,
		                        query_id VARCHAR(100) NOT NULL,
		                        query VARCHAR(255) NOT NULL,
		                        impressions VARCHAR(20) NOT NULL,
		                        impressions_change VARCHAR(20) NOT NULL,
		                        clicks VARCHAR(20) NOT NULL,
		                        clicks_change VARCHAR(20) NOT NULL,
		                        ctr VARCHAR(20) NOT NULL,
		                        ctr_change VARCHAR(20) NOT NULL,
		                        avg_position VARCHAR(20) NOT NULL,
		                        avg_position_change VARCHAR(20) NOT NULL,
		                        date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		                        PRIMARY KEY id (id),
		                        UNIQUE KEY query_id (query_id)
		                        );
                   		";

			$sql_keywords = "CREATE TABLE $table_name_keywords (
		                        id mediumint(9) NOT NULL AUTO_INCREMENT,
		                        query VARCHAR(255) NOT NULL,
		                        occurrences VARCHAR(20) NOT NULL,
		                        variants_encountered TEXT NOT NULL,
		                        top_urls TEXT NOT NULL,
		                        date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		                        PRIMARY KEY id (id),
		                        UNIQUE KEY query (query)
		                        );
                   		";

			$sql_external_links = "CREATE TABLE $table_name_external_links (
		                        id mediumint(9) NOT NULL AUTO_INCREMENT,
		                        domains VARCHAR(255) NOT NULL,
		                        links VARCHAR(20) NOT NULL,
		                        linked_pages VARCHAR(20) NOT NULL,
		                        date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		                        PRIMARY KEY id (id),
		                        UNIQUE KEY domains (domains)
		                        );
                   		";

			$sql_internal_links = "CREATE TABLE $table_name_internal_links (
		                        id mediumint(9) NOT NULL AUTO_INCREMENT,
		                        target_pages VARCHAR(500) NOT NULL,
		                        links VARCHAR(20) NOT NULL,
		                        date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		                        PRIMARY KEY id (id),
		                        UNIQUE KEY target_pages (target_pages)
		                        );
                   		";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			dbDelta($sql_pages);
			dbDelta($sql_queries);
			dbDelta($sql_keywords);
			dbDelta($sql_external_links);
			dbDelta($sql_internal_links);


		}

		/** function/method
		 * Usage: hooking (registering) the plugin menu
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_menu()
		{

			$icon_url = str_replace(basename(__FILE__), "", plugin_basename(__FILE__));
			add_menu_page(MDP_GWT_WT_NICK . ' Plugin Options', MDP_GWT_WT_NICK, '10', MDP_GWT_WT_ID . '_options', array('mdpWebmasterTools', 'mdp_gwt_options_page'), plugins_url($icon_url . 'mdp_icon32.png'));
			add_submenu_page(MDP_GWT_WT_ID . '_options', MDP_GWT_WT_NICK . ' Options', 'Options', '10', MDP_GWT_WT_ID . '_options', array('mdpWebmasterTools', 'mdp_gwt_options_page'));
			add_submenu_page(MDP_GWT_WT_ID . '_options', MDP_GWT_WT_NICK . ' Queries', 'Queries', '10', MDP_GWT_WT_ID . '_queries', array('mdpWebmasterTools', 'mdp_gwt_queries_page'));
			add_submenu_page(MDP_GWT_WT_ID . '_options', MDP_GWT_WT_NICK . ' Top Pages', 'Top Pages', '10', MDP_GWT_WT_ID . '_pages', array('mdpWebmasterTools', 'mdp_gwt_pages_page'));
			add_submenu_page(MDP_GWT_WT_ID . '_options', MDP_GWT_WT_NICK . ' Keywords', 'Keywords', '10', MDP_GWT_WT_ID . '_keywords', array('mdpWebmasterTools', 'mdp_gwt_keywords_page'));
			add_submenu_page(MDP_GWT_WT_ID . '_options', MDP_GWT_WT_NICK . ' Links to Site', 'Links to Site', '10', MDP_GWT_WT_ID . '_external_links', array('mdpWebmasterTools', 'mdp_gwt_external_links_page'));
			add_submenu_page(MDP_GWT_WT_ID . '_options', MDP_GWT_WT_NICK . ' Internal Links', 'Internal Links', '10', MDP_GWT_WT_ID . '_internal_links', array('mdpWebmasterTools', 'mdp_gwt_internal_links_page'));
		}


		/** function/options_page
		 * Usage: show options/settings for plugin
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_options_page()
		{

			$plugin_id = MDP_GWT_WT_ID;
			// display options page
			include(self::mdp_gwt_file_path('options.php'));

		}


		/** function/queries_page
		 * Usage: show query tracking for plugin
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_queries_page()
		{

			$plugin_id = MDP_GWT_WT_ID;
			// display options page
			include(self::mdp_gwt_file_path('queries.php'));

		}

		/** function/pages_page
		 * Usage: show top pages tracking for plugin
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_pages_page()
		{

			$plugin_id = MDP_GWT_WT_ID;
			// display options page
			include(self::mdp_gwt_file_path('pages.php'));

		}


		/** function/keywords_page
		 * Usage: show keywords tracking for plugin
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_keywords_page()
		{

			$plugin_id = MDP_GWT_WT_ID;
			// display options page
			include(self::mdp_gwt_file_path('keywords.php'));

		}

		/** function/external_links_page
		 * Usage: show external links tracking for plugin
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_external_links_page()
		{

			$plugin_id = MDP_GWT_WT_ID;
			// display options page
			include(self::mdp_gwt_file_path('external_links.php'));

		}

		/** function/internal_links_page
		 * Usage: show internal links tracking for plugin
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_internal_links_page()
		{

			$plugin_id = MDP_GWT_WT_ID;
			// display options page
			include(self::mdp_gwt_file_path('internal_links.php'));

		}

		/** function/full_url
		 * Usage: parses the url string and returns with no trailing &
		 * Arg(0): null
		 * Return: parsed url
		 */
		public static function full_url($url)
		{

			$new_url = explode('&', $url);
			return $new_url[0];

		}

		private function mdp_replace_table_pages_query($data, $table_name)
		{
			global $wpdb;

			$query = mysql_real_escape_string($data[0]);
			$query_id = str_replace(' ', '-', $query);
			$impressions = str_replace(',', '', str_replace('<', '-', $data[1]));
			$impressions_change = str_replace('%', '', str_replace('?', '', $data[2]));
			$clicks = str_replace(',', '', str_replace('<', '-', $data[3]));
			$clicks_change = str_replace('%', '', str_replace('?', '', $data[4]));
			$ctr = str_replace(',', '', str_replace('%', '', $data[5]));
			$ctr_change = str_replace('%', '', str_replace('?', '', $data[6]));
			$avg_position = str_replace(',', '', str_replace('<', '-', $data[7]));
			$avg_position_change = str_replace('%', '', str_replace('?', '', $data[8]));

			$wpdb->insert(
				$table_name,
				array(
				     'query_id' => $query_id,
				     'query' => $query,
				     'impressions' => $impressions,
				     'impressions_change' => $impressions_change,
				     'clicks' => $clicks,
				     'clicks_change' => $clicks_change,
				     'ctr' => $ctr,
				     'ctr_change' => $ctr_change,
				     'avg_position' => $avg_position,
				     'avg_position_change' => $avg_position_change,
				     'date' => current_time('mysql', 1)

				)
			);

		}

		private function mdp_replace_table_keywords($data, $table_name)
		{
			global $wpdb;

			$query = mysql_real_escape_string($data[0]);
			$occurrences = $data[1];
			$variants_encountered = $data[2];
			$top_urls = $data[3];

			$wpdb->insert(
				$table_name,
				array(
				     'query' => $query,
				     'occurrences' => $occurrences,
				     'variants_encountered' => $variants_encountered,
				     'top_urls' => $top_urls,
				     'date' => current_time('mysql', 1)

				)
			);

		}

		private function mdp_replace_table_external_links($data, $table_name)
		{
			global $wpdb;

			$domains = mysql_real_escape_string($data[0]);
			$links = $data[1];
			$linked_pages = $data[2];
			$top_pages = mysql_real_escape_string($data[3]);

			$wpdb->insert(
				$table_name,
				array(
				     'domains' => $domains,
				     'links' => $links,
				     'linked_pages' => $linked_pages,
				     'date' => current_time('mysql', 1)

				)
			);

		}

		private function mdp_replace_table_internal_links($data, $table_name)
		{
			global $wpdb;

			$target_pages = mysql_real_escape_string($data[0]);
			$links = str_replace(',', '', $data[1]);

			$wpdb->insert(
				$table_name,
				array(
				     'target_pages' => $target_pages,
				     'links' => $links,
				     'date' => current_time('mysql', 1)

				)
			);

		}

		private function mdp_truncate($table_name)
		{
			global $wpdb;
			$sql = "TRUNCATE TABLE " . $table_name;
			$wpdb->query($wpdb->prepare($sql, '0'));

		}

		public static function listUrls($string)
		{

			$clean = str_replace('[', '', str_replace(']', '', $string));

			$array = explode('/:', $clean);

			$output = "";
			$output .= '<table>';

			foreach ($array as $line) {
				$output .= '<tr><td>' . $line . '</td></tr>';
			}

			$output .= "</table>";
			return $output;

		}

		/** function/mdp_gwt_query_function
		 * Usage: download csv from google and insert data into wp_mdp_gwt_queries table
		 *              also attached to the wp_cron by mdp_gwt_hook
		 * Arg(0): null
		 * Return: void
		 */
		public static function mdp_gwt_query_function()
		{
			global $wpdb;
			$mdpWebmasterTools = new mdpWebmasterTools();
			$dir = MDP_GWT_DIR . 'csv/';

			// test directory exists if not create
			if (!file_exists($dir)) {

				if (!mkdir($dir, 0775)) {
					echo 'Error making directory check your permissions';
				}
			}

			include MDP_GWT_DIR . 'gwt_data.php';

			//erase old files to keep directory clean
			if ((count(glob("$dir*.csv")) > 0)) {

				array_map('unlink', (glob("$dir*.csv")));
			}

			//download the csv files from google
			try {
				/* If hardcoded, don't forget trailing slash!   */
				$gdata = new GWTdata();

				if ($gdata->LogIn(get_option('mdp_gwt_email'), get_option('mdp_gwt_password')) === true) {
					$sites = $gdata->GetSites();

					foreach ($sites as $site) {

						$gdata->DownloadCSV($site, $dir);

					}
					/*  uncomment to see downloaded files
					$files = $gdata->GetDownloadedFiles();
					foreach ($files as $file) {
						print "Saved $file\n";
					}
					*/

				}

			} catch (Exception $e) {

				die($e->getMessage());
			}


			//assign the table name
			$table_name_queries = $wpdb->prefix . "mdp_gwt_query";
			$table_name_pages = $wpdb->prefix . "mdp_gwt_pages";
			$table_name_keywords = $wpdb->prefix . "mdp_gwt_keywords";
			$table_name_external_links = $wpdb->prefix . "mdp_gwt_external_links";
			$table_name_internal_links = $wpdb->prefix . "mdp_gwt_internal_links";

			//Truncate the existing table to allow reset of all sites
			$mdpWebmasterTools->mdp_truncate($table_name_queries);
			$mdpWebmasterTools->mdp_truncate($table_name_pages);
			$mdpWebmasterTools->mdp_truncate($table_name_keywords);
			$mdpWebmasterTools->mdp_truncate($table_name_external_links);
			$mdpWebmasterTools->mdp_truncate($table_name_internal_links);

			//scan the csv directory for files

			if ($files = scandir($dir)) {
				//itterate through the file line by line
				foreach ($files as $file) {

					$query_file = $dir . $file;

					$row_query = 1;

					if (($handle_query = fopen($query_file, "r")) !== FALSE) {

						while (($data_query = fgetcsv($handle_query, 2000, ",")) !== FALSE) {

							if ($row_query > 1) {

								if (preg_match('#QUERIES#is', $file)) {
									$mdpWebmasterTools->mdp_replace_table_pages_query($data_query, $table_name_queries);
								} elseif (preg_match('#PAGES#is', $file)) {
									$mdpWebmasterTools->mdp_replace_table_pages_query($data_query, $table_name_pages);
								} elseif (preg_match('#KEYWORDS#is', $file)) {
									$mdpWebmasterTools->mdp_replace_table_keywords($data_query, $table_name_keywords);
								} elseif (preg_match('#EXTERNAL_LINKS#is', $file)) {
									$mdpWebmasterTools->mdp_replace_table_external_links($data_query, $table_name_external_links);
								} elseif (preg_match('#INTERNAL_LINKS#is', $file)) {
									$mdpWebmasterTools->mdp_replace_table_internal_links($data_query, $table_name_internal_links);
								}
							}
							$row_query++;
						}
					}
					fclose($handle_query);
				}
			}
		}
	}
}

add_action('admin_init', array('mdpWebmasterTools', 'mdp_gwt_register'));
add_action('admin_menu', array('mdpWebmasterTools', 'mdp_gwt_menu'));
add_action('user_admin_menu', array('mdpWebmasterTools', 'mdp_gwt_menu'));
add_action('mdp_gwt_hook', array('mdpWebmasterTools', 'mdp_gwt_query_function'));

$mdpWebmasterTools = new mdpWebmasterTools();


?>