<?php
/*

 $Id: sitemap-core.php 779425 2013-09-27 22:49:51Z arnee $

*/

//Enable for dev! Good code doesn't generate any notices...
//error_reporting(E_ALL);
//ini_set("display_errors",1);

/**
 * Represents the status (success and failures) of a building process
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0b5
 */
class GoogleSitemapGeneratorStatus {

	function GoogleSitemapGeneratorStatus() {
		$this->_startTime = $this->GetMicrotimeFloat();

		$exists = get_option("sm_status");

		if($exists === false) add_option("sm_status","",null,"no");

		$this->Save();
	}

	function Save() {
		update_option("sm_status",$this);
	}

	/**
	 * Returns the last saved status object or null
	 *
	 * @return GoogleSitemapGeneratorStatus
	 */
	function &Load() {
		$status = @get_option("sm_status");
		if(is_a($status,"GoogleSitemapGeneratorStatus")) return $status;
		else return null;
	}

	/**
	 * @var float $_startTime The start time of the building process
	 * @access private
	 */
	var $_startTime = 0;

	/**
	 * @var float $_endTime The end time of the building process
	 * @access private
	 */
	var $_endTime = 0;

	/**
	 * @var bool $$_hasChanged Indicates if the sitemap content has changed
	 * @access private
	 */
	var $_hasChanged = true;

	/**
	 * @var int $_memoryUsage The amount of memory used in bytes
	 * @access private
	 */
	var $_memoryUsage = 0;

	/**
	 * @var int $_lastPost The number of posts processed. This value is updated every 50 posts.
	 * @access private
	 */
	var $_lastPost = 0;

	/**
	 * @var int $_lastTime The time when the last step-update occured. This value is updated every 50 posts.
	 * @access private
	 */
	var $_lastTime = 0;

	function End($hasChanged = true) {
		$this->_endTime = $this->GetMicrotimeFloat();

		$this->SetMemoryUsage();

		$this->_hasChanged = $hasChanged;

		$this->Save();
	}

	function SetMemoryUsage() {
		if(function_exists("memory_get_peak_usage")) {
			$this->_memoryUsage = memory_get_peak_usage(true);
		} else if(function_exists("memory_get_usage")) {
			$this->_memoryUsage =  memory_get_usage(true);
		}
	}

	function GetMemoryUsage() {
		return round($this->_memoryUsage / 1024 / 1024,2);
	}

	function SaveStep($postCount) {
		$this->SetMemoryUsage();
		$this->_lastPost = $postCount;
		$this->_lastTime = $this->GetMicrotimeFloat();

		$this->Save();
	}

	function GetTime() {
		return round($this->_endTime - $this->_startTime,2);
	}

	function GetStartTime() {
		return round($this->_startTime, 2);
	}

	function GetLastTime() {
		return round($this->_lastTime - $this->_startTime,2);
	}

	function GetLastPost() {
		return $this->_lastPost;
	}

	var $_usedXml = false;
	var $_xmlSuccess = false;
	var $_xmlPath = '';
	var $_xmlUrl = '';

	function StartXml($path,$url) {
		$this->_usedXml = true;
		$this->_xmlPath = $path;
		$this->_xmlUrl = $url;

		$this->Save();
	}

	function EndXml($success) {
		$this->_xmlSuccess = $success;

		$this->Save();
	}


	var $_usedZip = false;
	var $_zipSuccess = false;
	var $_zipPath = '';
	var $_zipUrl = '';

	function StartZip($path,$url) {
		$this->_usedZip = true;
		$this->_zipPath = $path;
		$this->_zipUrl = $url;

		$this->Save();
	}

	function EndZip($success) {
		$this->_zipSuccess = $success;

		$this->Save();
	}

	var $_usedGoogle = false;
	var $_googleUrl = '';
	var $_gooogleSuccess = false;
	var $_googleStartTime = 0;
	var $_googleEndTime = 0;

	function StartGooglePing($url) {
		$this->_googleUrl = $url;
		$this->_usedGoogle = true;
		$this->_googleStartTime = $this->GetMicrotimeFloat();

		$this->Save();
	}

	function EndGooglePing($success) {
		$this->_googleEndTime = $this->GetMicrotimeFloat();
		$this->_gooogleSuccess = $success;

		$this->Save();
	}

	function GetGoogleTime() {
		return round($this->_googleEndTime - $this->_googleStartTime,2);
	}

	var $_usedMsn = false;
	var $_msnUrl = '';
	var $_msnSuccess = false;
	var $_msnStartTime = 0;
	var $_msnEndTime = 0;

	function StartMsnPing($url) {
		$this->_usedMsn = true;
		$this->_msnUrl = $url;
		$this->_msnStartTime = $this->GetMicrotimeFloat();

		$this->Save();
	}

	function EndMsnPing($success) {
		$this->_msnEndTime = $this->GetMicrotimeFloat();
		$this->_msnSuccess = $success;

		$this->Save();
	}

	function GetMsnTime() {
		return round($this->_msnEndTime - $this->_msnStartTime,2);
	}

	function GetMicrotimeFloat() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
		}

/**
 * Represents an item in the page list
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPage {

	/**
	 * @var string $_url Sets the URL or the relative path to the blog dir of the page
	 * @access private
	 */
	var $_url;

	/**
	 * @var float $_priority Sets the priority of this page
	 * @access private
	 */
	var $_priority;

	/**
	 * @var string $_changeFreq Sets the chanfe frequency of the page. I want Enums!
	 * @access private
	 */
	var $_changeFreq;

	/**
	 * @var int $_lastMod Sets the lastMod date as a UNIX timestamp.
	 * @access private
	 */
	var $_lastMod;

	/**
	 * Initialize a new page object
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @param bool $enabled Should this page be included in thesitemap
	 * @param string $url The URL or path of the file
	 * @param float $priority The Priority of the page 0.0 to 1.0
	 * @param string $changeFreq The change frequency like daily, hourly, weekly
	 * @param int $lastMod The last mod date as a unix timestamp
	 */
	function GoogleSitemapGeneratorPage($url="",$priority=0.0,$changeFreq="never",$lastMod=0) {
		$this->SetUrl($url);
		$this->SetProprity($priority);
		$this->SetChangeFreq($changeFreq);
		$this->SetLastMod($lastMod);
	}

	/**
	 * Returns the URL of the page
	 *
	 * @return string The URL
	 */
	function GetUrl() {
		return $this->_url;
	}

	/**
	 * Sets the URL of the page
	 *
	 * @param string $url The new URL
	 */
	function SetUrl($url) {
		$this->_url=(string) $url;
	}

	/**
	 * Returns the priority of this page
	 *
	 * @return float the priority, from 0.0 to 1.0
	 */
	function GetPriority() {
		return $this->_priority;
	}

	/**
	 * Sets the priority of the page
	 *
	 * @param float $priority The new priority from 0.1 to 1.0
	 */
	function SetProprity($priority) {
		$this->_priority=floatval($priority);
	}

	/**
	 * Returns the change frequency of the page
	 *
	 * @return string The change frequncy like hourly, weekly, monthly etc.
	 */
	function GetChangeFreq() {
		return $this->_changeFreq;
	}

	/**
	 * Sets the change frequency of the page
	 *
	 * @param string $changeFreq The new change frequency
	 */
	function SetChangeFreq($changeFreq) {
		$this->_changeFreq=(string) $changeFreq;
	}

	/**
	 * Returns the last mod of the page
	 *
	 * @return int The lastmod value in seconds
	 */
	function GetLastMod() {
		return $this->_lastMod;
	}

	/**
	 * Sets the last mod of the page
	 *
	 * @param int $lastMod The lastmod of the page
	 */
	function SetLastMod($lastMod) {
		$this->_lastMod=intval($lastMod);
	}

	function Render() {

		if($this->_url == "/" || empty($this->_url)) return '';

		$r="";
		$r.= "\t<url>\n";
		$r.= "\t\t<loc>" . $this->EscapeXML($this->_url) . "</loc>\n";
		if($this->_lastMod>0) $r.= "\t\t<lastmod>" . date('Y-m-d\TH:i:s+00:00',$this->_lastMod) . "</lastmod>\n";
		if(!empty($this->_changeFreq)) $r.= "\t\t<changefreq>" .  $this->EscapeXML($this->_changeFreq) . "</changefreq>\n";
		if($this->_priority!==false && $this->_priority!=="") $r.= "\t\t<priority>" . number_format($this->_priority,1) . "</priority>\n";
		$r.= "\t</url>\n";
		return $r;
	}

	function EscapeXML($string) {
		return str_replace ( array ( '&', '"', "'", '<', '>'), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;'), $string);
	}
}

class GoogleSitemapGeneratorXmlEntry {

	var $_xml;

	function GoogleSitemapGeneratorXmlEntry($xml) {
		$this->_xml = $xml;
	}

	function Render() {
		return $this->_xml;
	}
}

class GoogleSitemapGeneratorDebugEntry extends GoogleSitemapGeneratorXmlEntry {

	function Render() {
		return "<!-- " . $this->_xml . " -->\n";
	}
}

/**
 * Base class for all priority providers
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioProviderBase {

	/**
	 * @var int $_totalComments The total number of comments of all posts
	 * @access protected
	 */
	var $_totalComments=0;

	/**
	 * @var int $_totalComments The total number of posts
	 * @access protected
	 */
	var $_totalPosts=0;

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return string The translated name
	*/
	function GetName() {
		return "";
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return string The translated description
	*/
	function GetDescription() {
		return "";
	}

	/**
	 * Initializes a new priority provider
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	*/
	function GoogleSitemapGeneratorPrioProviderBase($totalComments,$totalPosts) {
		$this->_totalComments=$totalComments;
		$this->_totalPosts=$totalPosts;

	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return int The calculated priority
	*/
	function GetPostPriority($postID,$commentCount) {
		return 0;
	}
}

/**
 * Priority Provider which calculates the priority based on the number of comments
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioByCountProvider extends GoogleSitemapGeneratorPrioProviderBase {

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return string The translated name
	*/
	function GetName() {
		return __("Comment Count",'sitemap');
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return string The translated description
	*/
	function GetDescription() {
		return __("Uses the number of comments of the post to calculate the priority",'sitemap');
	}

	/**
	 * Initializes a new priority provider which calculates the post priority based on the number of comments
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	*/
	function GoogleSitemapGeneratorPrioByCountProvider($totalComments,$totalPosts) {
		parent::GoogleSitemapGeneratorPrioProviderBase($totalComments,$totalPosts);
	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return int The calculated priority
	*/
	function GetPostPriority($postID,$commentCount) {
		$prio=0;
		if($this->_totalComments>0 && $commentCount>0) {
			$prio = round(($commentCount*100/$this->_totalComments)/100,1);
		} else {
			$prio = 0;
		}
		return $prio;
	}
}

/**
 * Priority Provider which calculates the priority based on the average number of comments
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioByAverageProvider extends GoogleSitemapGeneratorPrioProviderBase {

	/**
	 * @var int $_average The average number of comments per post
	 * @access protected
	 */
	var $_average=0.0;

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return string The translated name
	*/
	function GetName() {
		return __("Comment Average",'sitemap');
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return string The translated description
	*/
	function GetDescription() {
		return __("Uses the average comment count to calculate the priority",'sitemap');
	}

	/**
	 * Initializes a new priority provider which calculates the post priority based on the average number of comments
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	*/
	function GoogleSitemapGeneratorPrioByAverageProvider($totalComments,$totalPosts) {
		parent::GoogleSitemapGeneratorPrioProviderBase($totalComments,$totalPosts);

		if($this->_totalComments>0 && $this->_totalPosts>0) {
			$this->_average= (double) $this->_totalComments / $this->_totalPosts;
		}
	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return int The calculated priority
	*/
	function GetPostPriority($postID,$commentCount) {
		$prio = 0;
		//Do not divide by zero!
		if($this->_average==0) {
			if($commentCount>0)	$prio = 1;
			else $prio = 0;
		} else {
			$prio = $commentCount/$this->_average;
			if($prio>1) $prio = 1;
			else if($prio<0) $prio = 0;
		}

		return round($prio,1);
	}
}

/**
 * Priority Provider which calculates the priority based on the popularity by the PopularityContest Plugin
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioByPopularityContestProvider extends GoogleSitemapGeneratorPrioProviderBase {

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return string The translated name
	*/
	function GetName() {
		return __("Popularity Contest",'sitemap');
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return string The translated description
	*/
	function GetDescription() {
		return str_replace("%4","index.php?page=popularity-contest.php",str_replace("%3","options-general.php?page=popularity-contest.php",str_replace("%2","http://www.alexking.org/",str_replace("%1","http://www.alexking.org/index.php?content=software/wordpress/content.php",__("Uses the activated <a href=\"%1\">Popularity Contest Plugin</a> from <a href=\"%2\">Alex King</a>. See <a href=\"%3\">Settings</a> and <a href=\"%4\">Most Popular Posts</a>",'sitemap')))));
	}

	/**
	 * Initializes a new priority provider which calculates the post priority based on the popularity by the PopularityContest Plugin
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	*/
	function GoogleSitemapGeneratorPrioByPopularityContestProvider($totalComments,$totalPosts) {
		parent::GoogleSitemapGeneratorPrioProviderBase($totalComments,$totalPosts);
	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return int The calculated priority
	*/
	function GetPostPriority($postID,$commentCount) {
		//$akpc is the global instance of the Popularity Contest Plugin
		global $akpc,$posts;

		$res=0;
		//Better check if its there
		if(!empty($akpc) && is_object($akpc)) {
			//Is the method we rely on available?
		if(method_exists($akpc,"get_post_rank")) {
			if(!is_array($posts) || !$posts) $posts = array();
				if(!isset($posts[$postID])) $posts[$postID] = get_post($postID);
				//popresult comes as a percent value
				$popresult=$akpc->get_post_rank($postID);
				if(!empty($popresult) && strpos($popresult,"%")!==false) {
					//We need to parse it to get the priority as an int (percent)
					$matches=null;
					preg_match("/([0-9]{1,3})\%/si",$popresult,$matches);
					if(!empty($matches) && is_array($matches) && count($matches)==2) {
						//Divide it so 100% = 1, 10% = 0.1
						$res=round(intval($matches[1])/100,1);
					}
				}
			}
		}
		return $res;
	}
}

/**
 * Class to generate a sitemaps.org Sitemaps compliant sitemap of a WordPress blog.
 *
 * @package sitemap
 * @author Arne Brachhold
 * @since 3.0
*/
class GoogleSitemapGenerator {
	/**
	 * @var Version of the generator in SVN
	*/
	var $_svnVersion = '$Id: sitemap-core.php 779425 2013-09-27 22:49:51Z arnee $';

	/**
	 * @var array The unserialized array with the stored options
	 */
	var $_options = array();

	/**
	 * @var array The saved additional pages
	 */
	var $_pages = array();

	/**
	 * @var array The values and names of the change frequencies
	 */
	var $_freqNames = array();

	/**
	 * @var array A list of class names which my be called for priority calculation
	 */
	var $_prioProviders = array();

	/**
	 * @var bool True if init complete (options loaded etc)
	 */
	var $_initiated = false;

	/**
	 * @var string Holds the last error if one occurs when writing the files
	 */
	var $_lastError=null;

	/**
	 * @var int The last handled post ID
	 */
	var $_lastPostID = 0;

	/**
	 * @var bool Defines if the sitemap building process is active at the moment
	 */
	var $_isActive = false;

	/**
	 * @var bool Defines if the sitemap building process has been scheduled via Wp cron
	 */
	var $_isScheduled = false;

	/**
	 * @var object The file handle which is used to write the sitemap file
	 */
	var $_fileHandle = null;

	/**
	 * @var object The file handle which is used to write the zipped sitemap file
	 */
	var $_fileZipHandle = null;

	/**
	 * Holds the user interface object
	 *
	 * @since 3.1.1
	 * @var GoogleSitemapGeneratorUI
	 */
	var $_ui = null;

	/**
	 * Returns the path to the blog directory
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @return string The full path to the blog directory
	*/
	function GetHomePath() {

		$res="";
		//Check if we are in the admin area -> get_home_path() is avaiable
		if(function_exists("get_home_path")) {
			$res = get_home_path();
		} else {
			//get_home_path() is not available, but we can't include the admin
			//libraries because many plugins check for the "check_admin_referer"
			//function to detect if you are on an admin page. So we have to copy
			//the get_home_path function in our own...
			$home = get_option( 'home' );
			if ( $home != '' && $home != get_option( 'url' ) ) {
				$home_path = parse_url( $home );
				$home_path = $home_path['path'];
				$root = str_replace( $_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"] );
				$home_path = trailingslashit( $root.$home_path );
			} else {
				$home_path = ABSPATH;
			}

			$res = $home_path;
		}
		return $res;
	}

	/**
	 * Returns the path to the directory where the plugin file is located
	 * @since 3.0b5
	 * @access private
	 * @author Arne Brachhold
	 * @return string The path to the plugin directory
	 */
	function GetPluginPath() {
		$path = dirname(__FILE__);
		return trailingslashit(str_replace("\\","/",$path));
	}

	/**
	 * Returns the URL to the directory where the plugin file is located
	 * @since 3.0b5
	 * @access private
	 * @author Arne Brachhold
	 * @return string The URL to the plugin directory
	 */
	function GetPluginUrl() {

		//Try to use WP API if possible, introduced in WP 2.6
		if (function_exists('plugins_url')) return trailingslashit(plugins_url(basename(dirname(__FILE__))));

		//Try to find manually... can't work if wp-content was renamed or is redirected
		$path = dirname(__FILE__);
		$path = str_replace("\\","/",$path);
		$path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
		return $path;
	}

	/**
	 * Returns the URL to default XSLT style if it exists
	 * @since 3.0b5
	 * @access private
	 * @author Arne Brachhold
	 * @return string The URL to the default stylesheet, empty string if not available.
	 */
	function GetDefaultStyle() {
		$p = $this->GetPluginPath();
		if(file_exists($p . "sitemap.xsl")) {
			$url = $this->GetPluginUrl();
			//If called over the admin area using HTTPS, the stylesheet would also be https url, even if the blog frontend is not.
			if(substr(get_bloginfo('url'),0,5) !="https" && substr($url,0,5)=="https") $url="http" . substr($url,5);
			return $url . 'sitemap.xsl';
		}
		return '';
	}

	/**
	 * Sets up the default configuration
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	*/
	function InitOptions() {

		$this->_options=array();
		$this->_options["sm_b_prio_provider"]="GoogleSitemapGeneratorPrioByCountProvider";			//Provider for automatic priority calculation
		$this->_options["sm_b_filename"]="sitemap.xml";		//Name of the Sitemap file
		$this->_options["sm_b_debug"]=true;					//Write debug messages in the xml file
		$this->_options["sm_b_xml"]=true;					//Create a .xml file
		$this->_options["sm_b_gzip"]=true;					//Create a gzipped .xml file(.gz) file
		$this->_options["sm_b_ping"]=true;					//Auto ping Google
		$this->_options["sm_b_pingmsn"]=true;				//Auto ping MSN
		$this->_options["sm_b_manual_enabled"]=false;		//Allow manual creation of the sitemap via GET request
		$this->_options["sm_b_auto_enabled"]=true;			//Rebuild sitemap when content is changed
		$this->_options["sm_b_auto_delay"]=true;			//Use WP Cron to execute the building process in the background
		$this->_options["sm_b_manual_key"]=md5(microtime());//The secret key to build the sitemap via GET request
		$this->_options["sm_b_memory"] = '';				//Set Memory Limit (e.g. 16M)
		$this->_options["sm_b_time"] = -1;					//Set time limit in seconds, 0 for unlimited, -1 for disabled
		$this->_options["sm_b_max_posts"] = -1;				//Maximum number of posts, <= 0 for all
		$this->_options["sm_b_safemode"] = false;			//Enable MySQL Safe Mode (doesn't use unbuffered results)
		$this->_options["sm_b_style_default"] = true;		//Use default style
		$this->_options["sm_b_style"] = '';					//Include a stylesheet in the XML
		$this->_options["sm_b_robots"] = true;				//Add sitemap location to WordPress' virtual robots.txt file
		$this->_options["sm_b_exclude"] = array();			//List of post / page IDs to exclude
		$this->_options["sm_b_exclude_cats"] = array();		//List of post / page IDs to exclude
		$this->_options["sm_b_location_mode"]="auto";		//Mode of location, auto or manual
		$this->_options["sm_b_filename_manual"]="";			//Manuel filename
		$this->_options["sm_b_fileurl_manual"]="";			//Manuel fileurl

		$this->_options["sm_in_home"]=true;					//Include homepage
		$this->_options["sm_in_posts"]=true;				//Include posts
		$this->_options["sm_in_posts_sub"]=false;			//Include post pages (<!--nextpage--> tag)
		$this->_options["sm_in_pages"]=true;				//Include static pages
		$this->_options["sm_in_cats"]=false;				//Include categories
		$this->_options["sm_in_arch"]=false;				//Include archives
		$this->_options["sm_in_auth"]=false;				//Include author pages
		$this->_options["sm_in_tags"]=false;				//Include tag pages
		$this->_options["sm_in_tax"]=array();				//Include additional taxonomies
		$this->_options["sm_in_customtypes"]=array();		//Include custom post types
		$this->_options["sm_in_lastmod"]=true;				//Include the last modification date

		$this->_options["sm_cf_home"]="daily";				//Change frequency of the homepage
		$this->_options["sm_cf_posts"]="monthly";			//Change frequency of posts
		$this->_options["sm_cf_pages"]="weekly";			//Change frequency of static pages
		$this->_options["sm_cf_cats"]="weekly";				//Change frequency of categories
		$this->_options["sm_cf_auth"]="weekly";				//Change frequency of author pages
		$this->_options["sm_cf_arch_curr"]="daily";			//Change frequency of the current archive (this month)
		$this->_options["sm_cf_arch_old"]="yearly";			//Change frequency of older archives
		$this->_options["sm_cf_tags"]="weekly";				//Change frequency of tags

		$this->_options["sm_pr_home"]=1.0;					//Priority of the homepage
		$this->_options["sm_pr_posts"]=0.6;					//Priority of posts (if auto prio is disabled)
		$this->_options["sm_pr_posts_min"]=0.2;				//Minimum Priority of posts, even if autocalc is enabled
		$this->_options["sm_pr_pages"]=0.6;					//Priority of static pages
		$this->_options["sm_pr_cats"]=0.3;					//Priority of categories
		$this->_options["sm_pr_arch"]=0.3;					//Priority of archives
		$this->_options["sm_pr_auth"]=0.3;					//Priority of author pages
		$this->_options["sm_pr_tags"]=0.3;					//Priority of tags

		$this->_options["sm_i_donated"]=false;				//Did you donate? Thank you! :)
		$this->_options["sm_i_hide_donated"]=false;			//And hide the thank you..
		$this->_options["sm_i_install_date"]=time();		//The installation date
		$this->_options["sm_i_hide_note"]=false;			//Hide the note which appears after 30 days
		$this->_options["sm_i_hide_works"]=false;			//Hide the "works?" message which appears after 15 days
		$this->_options["sm_i_hide_donors"]=false;			//Hide the list of donations
	}

	/**
	 * Loads the configuration from the database
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	*/
	function LoadOptions() {

		$this->InitOptions();

		//First init default values, then overwrite it with stored values so we can add default
		//values with an update which get stored by the next edit.
		$storedoptions=get_option("sm_options");
		if($storedoptions && is_array($storedoptions)) {
			foreach($storedoptions AS $k=>$v) {
				$this->_options[$k]=$v;
			}
		} else update_option("sm_options",$this->_options); //First time use, store default values
	}

	/**
	 * Initializes a new Google Sitemap Generator
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	*/
	function GoogleSitemapGenerator() {




	}

	/**
	 * Returns the version of the generator
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @return int The version
	*/
	function GetVersion() {
		return GoogleSitemapGeneratorLoader::GetVersion();
	}

	/**
	 * Returns all parent classes of a class
	 *
	 * @param $className string The name of the class
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @return array An array which contains the names of the parent classes
	*/
	function GetParentClasses($classname) {
		$parent = get_parent_class($classname);
		$parents = array();
		if (!empty($parent)) {
			$parents = $this->GetParentClasses($parent);
			$parents[] = strtolower($parent);
		}
		return $parents;
	}

	/**
	 * Returns if a class is a subclass of another class
	 *
	 * @param $className string The name of the class
	 * @param $$parentName string The name of the parent class
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @return bool true if the given class is a subclass of the other one
	*/
	function IsSubclassOf($className, $parentName) {

		$className = strtolower($className);
		$parentName = strtolower($parentName);

		if(empty($className) || empty($parentName) || !class_exists($className) || !class_exists($parentName)) return false;

		$parents=$this->GetParentClasses($className);

		return in_array($parentName,$parents);
	}

	/**
	 * Loads up the configuration and validates the prioity providers
	 *
	 * This method is only called if the sitemaps needs to be build or the admin page is displayed.
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	*/
	function Initate() {
		if(!$this->_initiated) {

			//Loading language file...
			//load_plugin_textdomain('sitemap');
			//Hmm, doesn't work if the plugin file has its own directory.
			//Let's make it our way... load_plugin_textdomain() searches only in the wp-content/plugins dir.
			$currentLocale = get_locale();
			if(!empty($currentLocale)) {
				$moFile = dirname(__FILE__) . "/lang/sitemap-" . $currentLocale . ".mo";
				if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('sitemap', $moFile);
			}

			$this->_freqNames = array(
				"always"=>__("Always","sitemap"),
				"hourly"=>__("Hourly","sitemap"),
				"daily"=>__("Daily","sitemap"),
				"weekly"=>__("Weekly","sitemap"),
				"monthly"=>__("Monthly","sitemap"),
				"yearly"=>__("Yearly","sitemap"),
				"never"=>__("Never","sitemap")
			);


			$this->LoadOptions();
			$this->LoadPages();

			//Register our own priority providers
			add_filter("sm_add_prio_provider",array(&$this, 'AddDefaultPrioProviders'));

			//Let other plugins register their providers
			$r = apply_filters("sm_add_prio_provider",$this->_prioProviders);

			//Check if no plugin return null
			if($r != null) $this->_prioProviders = $r;

			$this->ValidatePrioProviders();

			$this->_initiated = true;
		}
	}

	/**
	 * Returns the instance of the Sitemap Generator
	 *
	 * @since 3.0
	 * @access public
	 * @return GoogleSitemapGenerator The instance or null if not available.
	 * @author Arne Brachhold
	*/
	function &GetInstance() {
		if(isset($GLOBALS["sm_instance"])) {
			return $GLOBALS["sm_instance"];
		} else return null;
	}

	/**
	 * Returns if the sitemap building process is currently active
	 *
	 * @since 3.0
	 * @access public
	 * @return bool true if active
	 * @author Arne Brachhold
	*/
	function IsActive() {
		$inst = &GoogleSitemapGenerator::GetInstance();
		return ($inst != null && $inst->_isActive);
	}

	/**
	 * Returns if the compressed sitemap was activated
	 *
	 * @since 3.0b8
	 * @access private
	 * @author Arne Brachhold
	 * @return true if compressed
	 */
	function IsGzipEnabled() {
		return ($this->GetOption("b_gzip")===true && function_exists("gzwrite"));
	}

	/**
	 * Returns if this version of WordPress supports the new taxonomy system
	 *
	 * @since 3.0b8
	 * @access private
	 * @author Arne Brachhold
	 * @return true if supported
	 */
	function IsTaxonomySupported() {
		return (function_exists("get_taxonomy") && function_exists("get_terms"));
	}

	/**
	 * Returns if this version of WordPress supports custom post types
	 *
	 * @since 3.2.5
	 * @access private
	 * @author Lee Willis
	 * @return true if supported
	 */
	function IsCustomPostTypesSupported() {
		return (function_exists("get_post_types") && function_exists("register_post_type"));
	}

	/**
	 * Returns the list of custom taxonies. These are basically all taxonomies without categories and post tags
	 *
	 * @since 3.1.7
	 * @return array Array of names of user-defined taxonomies
	 */
	function GetCustomTaxonomies() {
		$taxonomies = get_object_taxonomies('post');
		return array_diff($taxonomies,array("category","post_tag","post_format"));
	}

	/**
	 * Returns the list of custom post types. These are all custome post types except post, page and attachment
	 *
	 * @since 3.2.5
	 * @author Lee Willis
	 * @return array Array of custom post types as per get_post_types
	 */
	function GetCustomPostTypes() {
		$post_types = get_post_types(array("public"=>1));

		$post_types = array_diff($post_types,array("post","page","attachment"));
		return $post_types;
	}

	/**
	 * Enables the Google Sitemap Generator and registers the WordPress hooks
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	*/
	function Enable() {
		if(!isset($GLOBALS["sm_instance"])) {
			$GLOBALS["sm_instance"]=new GoogleSitemapGenerator();
		}
	}

	/**
	 * Checks if sitemap building after content changed is enabled and rebuild the sitemap
	 *
	 * @param int $postID The ID of the post to handle. Used to avoid double rebuilding if more than one hook was fired.
	 * @param bool $external Added in 3.1.9. Skips checking of b_auto_enabled if set to true
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	*/
	function CheckForAutoBuild($postID, $external = false) {
		global $wp_version;
		$this->Initate();
		//Build one time per post and if not importing.
		if((($this->GetOption("b_auto_enabled")===true && $this->_lastPostID != $postID) || $external) && (!defined('WP_IMPORTING') || WP_IMPORTING != true)) {

			//Build the sitemap directly or schedule it with WP cron
			if($this->GetOption("b_auto_delay")==true && floatval($wp_version) >= 2.1) {
				if(!$this->_isScheduled) {
					//Schedule in 15 seconds, this should be enough to catch all changes.
					//Clear all other existing hooks, so the sitemap is only built once.
					wp_clear_scheduled_hook('sm_build_cron');
					wp_schedule_single_event(time()+15,'sm_build_cron');
					$this->_isScheduled = true;
				}
			} else {
				//Build sitemap only once and never in bulk mode
				if(!$this->_lastPostID && (!isset($_GET["delete"]) || count((array) $_GET['delete'])<=0)) {
					$this->BuildSitemap();
				}
			}
			$this->_lastPostID = $postID;
		}
	}

	/**
	 * Builds the sitemap by external request, for example other plugins.
	 *
	 * @since 3.1.9
	 * @return null
	 */
	function BuildNowRequest() {
		$this->CheckForAutoBuild(null, true);
	}

	/**
	 * Checks if the rebuild request was send and starts to rebuilt the sitemap
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	*/
	function CheckForManualBuild() {
		if(!empty($_GET["sm_command"]) && !empty($_GET["sm_key"])) {
			$this->Initate();
			if($this->GetOption("b_manual_enabled")===true && $_GET["sm_command"]=="build" && $_GET["sm_key"]==$this->GetOption("b_manual_key")) {
				$this->BuildSitemap();
				echo "DONE";
				exit;
			}
		}
	}

	/**
	 * Validates all given Priority Providers by checking them for required methods and existence
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	*/
	function ValidatePrioProviders() {
		$validProviders=array();

		for($i=0; $i<count($this->_prioProviders); $i++) {
			if(class_exists($this->_prioProviders[$i])) {
				if($this->IsSubclassOf($this->_prioProviders[$i],"GoogleSitemapGeneratorPrioProviderBase")) {
					array_push($validProviders,$this->_prioProviders[$i]);
				}
			}
		}
		$this->_prioProviders=$validProviders;

		if(!$this->GetOption("b_prio_provider")) {
			if(!in_array($this->GetOption("b_prio_provider"),$this->_prioProviders,true)) {
				$this->SetOption("b_prio_provider","");
			}
		}
	}

	/**
	 * Adds the default Priority Providers to the provider list
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	*/
	function AddDefaultPrioProviders($providers) {
		array_push($providers,"GoogleSitemapGeneratorPrioByCountProvider");
		array_push($providers,"GoogleSitemapGeneratorPrioByAverageProvider");
		if(class_exists("ak_popularity_contest")) {
			array_push($providers,"GoogleSitemapGeneratorPrioByPopularityContestProvider");
		}
		return $providers;
	}

	/**
	 * Loads the stored pages from the database
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	*/
	function LoadPages() {
		global $wpdb;

		$needsUpdate=false;

		$pagesString=$wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'sm_cpages'");

		//Class sm_page was renamed with 3.0 -> rename it in serialized value for compatibility
		if(!empty($pagesString) && strpos($pagesString,"sm_page")!==false) {
			$pagesString = str_replace("O:7:\"sm_page\"","O:26:\"GoogleSitemapGeneratorPage\"",$pagesString);
			$needsUpdate=true;
		}

		if(!empty($pagesString)) {
			$storedpages=unserialize($pagesString);
			$this->_pages=$storedpages;
		} else {
			$this->_pages=array();
		}

		if($needsUpdate) $this->SavePages();
	}

	/**
	 * Saved the additional pages back to the database
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @return true on success
	*/
	function SavePages() {
		$oldvalue = get_option("sm_cpages");
		if($oldvalue == $this->_pages) {
			return true;
		} else {
			delete_option("sm_cpages");
			//Add the option, Note the autoload=false because when the autoload happens, our class GoogleSitemapGeneratorPage doesn't exist
			add_option("sm_cpages",$this->_pages,null,"no");
			return true;
		}
	}


	/**
	 * Returns the URL for the sitemap file
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param bool $forceAuto Force the return value to the autodetected value.
	 * @return The URL to the Sitemap file
	*/
	function GetXmlUrl($forceAuto=false) {

		$name ="";

		if(!$forceAuto && $this->GetOption("b_location_mode")=="manual") {
			$name =  $this->GetOption("b_fileurl_manual");
		} else {
			$name = trailingslashit(get_bloginfo('url')). $this->GetOption("b_filename");
		}

		if(substr($name,-4)!=".xml") {
			$name.=".xml";
		}

		return $name;
	}

	/**
	 * Returns the URL for the gzipped sitemap file
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param bool $forceAuto Force the return value to the autodetected value.
	 * @return The URL to the gzipped Sitemap file
	*/
	function GetZipUrl($forceAuto=false) {
		return $this->GetXmlUrl($forceAuto) . ".gz";
	}

	/**
	 * Returns the file system path to the sitemap file
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param bool $forceAuto Force the return value to the autodetected value.
	 * @return The file system path;
	*/
	function GetXmlPath($forceAuto=false) {

		$name ="";

		if(!$forceAuto && $this->GetOption("b_location_mode")=="manual") {
			$name = $this->GetOption("b_filename_manual");
		} else {
			$name = $this->GetHomePath()  . $this->GetOption("b_filename");
		}

		if(substr($name,-4)!=".xml") {
			$name.=".xml";
		}

		return $name;
	}

	/**
	 * Returns the file system path to the gzipped sitemap file
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param bool $forceAuto Force the return value to the autodetected value.
	 * @return The file system path;
	*/
	function GetZipPath($forceAuto=false) {
		return $this->GetXmlPath($forceAuto) . ".gz";
	}

	/**
	 * Returns the option value for the given key
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $key string The Configuration Key
	 * @return mixed The value
	 */
	function GetOption($key) {
		$key="sm_" . $key;
		if(array_key_exists($key,$this->_options)) {
			return $this->_options[$key];
		} else return null;
	}

	/**
	 * Sets an option to a new value
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $key string The configuration key
	 * @param $value mixed The new object
	 */
	function SetOption($key,$value) {
		if(strstr($key,"sm_")!==0) $key="sm_" . $key;

		$this->_options[$key]=$value;
	}

	/**
	 * Saves the options back to the database
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @return bool true on success
	 */
	function SaveOptions() {
		$oldvalue = get_option("sm_options");
		if($oldvalue == $this->_options) {
			return true;
		} else return update_option("sm_options",$this->_options);
	}

	/**
	 * Retrieves the number of comments of a post in a asso. array
	 * The key is the postID, the value the number of comments
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @return array An array with postIDs and their comment count
	 */
	function GetComments() {
		global $wpdb;
		$comments=array();

		//Query comments and add them into the array
		$commentRes=$wpdb->get_results("SELECT `comment_post_ID` as `post_id`, COUNT(comment_ID) as `comment_count` FROM `" . $wpdb->comments . "` WHERE `comment_approved`='1' GROUP BY `comment_post_ID`");
		if($commentRes) {
			foreach($commentRes as $comment) {
				$comments[$comment->post_id]=$comment->comment_count;
			}
		}
		return $comments;
	}

	/**
	 * Calculates the full number of comments from an sm_getComments() generated array
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $comments array The Array with posts and c0mment count
	 * @see sm_getComments
	 * @return The full number of comments
	 */
	function GetCommentCount($comments) {
		$commentCount=0;
		foreach($comments AS $k=>$v) {
			$commentCount+=$v;
		}
		return $commentCount;
	}

	/**
	 * Adds a url to the sitemap. You can use this method or call AddElement directly.
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold
	 * @param $loc string The location (url) of the page
	 * @param $lastMod int The last Modification time as a UNIX timestamp
	 * @param $changeFreq string The change frequenty of the page, Valid values are "always", "hourly", "daily", "weekly", "monthly", "yearly" and "never".
	 * @param $priorty float The priority of the page, between 0.0 and 1.0
	 * @see AddElement
	 * @return string The URL node
	 */
	function AddUrl($loc, $lastMod = 0, $changeFreq = "monthly", $priority = 0.5) {
		//Strip out the last modification time if activated
		if($this->GetOption('in_lastmod')===false) $lastMod = 0;

		if(($hashPosition = strpos($loc, '#')) !== false) {
			if($hashPosition == 0) return;
			else $loc = substr($loc, 0, $hashPosition);
		}

		$page = new GoogleSitemapGeneratorPage($loc, $priority, $changeFreq, $lastMod);

		$this->AddElement($page);
	}

	/**
	 * Adds an element to the sitemap
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $page The element
	 */
	function AddElement(&$page) {
		if(empty($page)) return;

		$s = $page->Render();

		if($this->_fileZipHandle && $this->IsGzipEnabled()) {
			gzwrite($this->_fileZipHandle,$s);
		}

		if($this->_fileHandle && $this->GetOption("b_xml")) {
			fwrite($this->_fileHandle,$s);
		}
	}

	/**
	 * Checks if a file is writable and tries to make it if not.
	 *
	 * @since 3.05b
	 * @access private
	 * @author  VJTD3 <http://www.VJTD3.com>
	 * @return bool true if writable
	 */
	function IsFileWritable($filename) {
		//can we write?
		if(!is_writable($filename)) {
			//no we can't.
			if(!@chmod($filename, 0666)) {
				$pathtofilename = dirname($filename);
				//Lets check if parent directory is writable.
				if(!is_writable($pathtofilename)) {
					//it's not writeable too.
					if(!@chmod($pathtofilename, 0666)) {
						//darn couldn't fix up parrent directory this hosting is foobar.
						//Lets error because of the permissions problems.
						return false;
					}
				}
			}
		}
		//we can write, return 1/true/happy dance.
		return true;
	}

	/**
	 * Adds the sitemap to the virtual robots.txt file
	 * This function is executed by WordPress with the do_robots hook
	 *
	 * @since 3.1.2
	 */
	function DoRobots() {
		$this->Initate();
		if($this->GetOption('b_robots') === true) {

			$smUrl = $this->GetXmlUrl();
			if($this->IsGzipEnabled()) {
				$smUrl = $this->GetZipUrl();
			}

			echo  "\nSitemap: " . $smUrl . "\n";
		}
	}

	/**
	 * Builds the sitemap and writes it into a xml file.
	 *
	 * ATTENTION PLUGIN DEVELOPERS! DONT CALL THIS METHOD DIRECTLY!
	 * The method is probably not available, since it is only loaded when needed.
	 * Use do_action("sm_rebuild"); if you want to rebuild the sitemap.
	 * Please refer to the documentation.txt for more details.
	 *
	 * @since 3.0
	 * @access public
	 * @author Arne Brachhold <himself [at] arnebrachhold [dot] de>
	 * @return array An array with messages such as failed writes etc.
	 */
	function BuildSitemap() {
		global $wpdb, $posts, $wp_version;
		$this->Initate();

		if($this->GetOption("b_memory")!='') {
			@ini_set("memory_limit",$this->GetOption("b_memory"));
		}

		if($this->GetOption("b_time")!=-1) {
			@set_time_limit($this->GetOption("b_time"));
		}

		//This object saves the status information of the script directly to the database
		$status = new GoogleSitemapGeneratorStatus();

		//Other plugins can detect if the building process is active
		$this->_isActive = true;

		//$this->AddElement(new GoogleSitemapGeneratorXmlEntry());

		//Debug mode?
		$debug=$this->GetOption("b_debug");

		if($this->GetOption("b_xml")) {
			$fileName = $this->GetXmlPath();
			$status->StartXml($this->GetXmlPath(),$this->GetXmlUrl());

			if($this->IsFileWritable($fileName)) {

				$this->_fileHandle = fopen($fileName,"w");
				if(!$this->_fileHandle) $status->EndXml(false,"Not openable");

			} else $status->EndXml(false,"not writable");
		}

		//Write gzipped sitemap file
		if($this->IsGzipEnabled()) {
			$fileName = $this->GetZipPath();
			$status->StartZip($this->GetZipPath(),$this->GetZipUrl());

			if($this->IsFileWritable($fileName)) {

				$this->_fileZipHandle = gzopen($fileName,"w1");
				if(!$this->_fileZipHandle) $status->EndZip(false,"Not openable");

			} else $status->EndZip(false,"not writable");
		}

		if(!$this->_fileHandle && !$this->_fileZipHandle) {
			$status->End();
			return;
		}


		//Content of the XML file
		$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<?xml version="1.0" encoding="UTF-8"' . '?' . '>'));

		$styleSheet = ($this->GetDefaultStyle() && $this->GetOption('b_style_default')===true?$this->GetDefaultStyle():$this->GetOption('b_style'));

		if(!empty($styleSheet)) {
			$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<' . '?xml-stylesheet type="text/xsl" href="' . $styleSheet . '"?' . '>'));
		}

		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("generator=\"wordpress/" . get_bloginfo('version') . "\""));
		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("sitemap-generator-url=\"http://www.arnebrachhold.de\" sitemap-generator-version=\"" . $this->GetVersion() . "\""));
		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("generated-on=\"" . date(get_option("date_format") . " " . get_option("time_format")) . "\""));

		//All comments as an asso. Array (postID=>commentCount)
		$comments=($this->GetOption("b_prio_provider")!=""?$this->GetComments():array());

		//Full number of comments
		$commentCount=(count($comments)>0?$this->GetCommentCount($comments):0);

		if($debug && $this->GetOption("b_prio_provider")!="") {
			$this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Total comment count: " . $commentCount));
		}

		//Go XML!
		$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'));

		$home = get_bloginfo('url');

		$homePid = 0;

		//Add the home page (WITH a slash!)
		if($this->GetOption("in_home")) {
			if('page' == get_option('show_on_front') && get_option('page_on_front')) {
				$pageOnFront = get_option('page_on_front');
				$p = get_page($pageOnFront);
				if($p) {
					$homePid = $p->ID;
					$this->AddUrl(trailingslashit($home),$this->GetTimestampFromMySql(($p->post_modified_gmt && $p->post_modified_gmt!='0000-00-00 00:00:00'?$p->post_modified_gmt:$p->post_date_gmt)),$this->GetOption("cf_home"),$this->GetOption("pr_home"));
				}
			} else {
				$this->AddUrl(trailingslashit($home),$this->GetTimestampFromMySql(get_lastpostmodified('GMT')),$this->GetOption("cf_home"),$this->GetOption("pr_home"));
			}
		}

		//Add the posts
		if($this->GetOption("in_posts") || $this->GetOption("in_pages")) {

			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Postings"));

			//Pre 2.1 compatibility. 2.1 introduced 'future' as post_status so we don't need to check post_date
			$wpCompat = (floatval($wp_version) < 2.1);

			$useQTransLate = false; //function_exists('qtrans_convertURL') && function_exists('qtrans_getEnabledLanguages'); Not really working yet

			$excludes = $this->GetOption('b_exclude'); //Excluded posts and pages (user enetered ID)

			$exclCats = $this->GetOption("b_exclude_cats"); // Excluded cats

			if($exclCats && count($exclCats)>0 && $this->IsTaxonomySupported()) {

				$excludedCatPosts = get_objects_in_term($exclCats,"category"); // Get all posts in excl. cats. Unforttunately this also gives us pages, revisions and so on...

				//Remove the pages, revisions etc from the exclude by category list, because they are always in the uncategorized one.
				if(count($excludedCatPosts)>0) {
					$exclPages = $wpdb->get_col("SELECT ID FROM `" . $wpdb->posts . "` WHERE post_type!='post' AND ID IN ('" . implode("','",$excludedCatPosts) . "')");

					$exclPages = array_map('intval', $exclPages);

					//Remove the pages from the exlusion list before
					if(count($exclPages)>0)	$excludedCatPosts = array_diff($excludedCatPosts, $exclPages);

					//Merge the category exclusion list with the users one
					if(count($excludedCatPosts)>0) $excludes = array_merge($excludes, $excludedCatPosts);
				}
			}


			$contentStmt = '';
			if($useQTransLate) {
				$contentStmt.=', post_content ';
			}

			$postPageStmt = '';

			$inSubPages = ($this->GetOption('in_posts_sub')===true);

			if($inSubPages && $this->GetOption('in_posts')===true) {
				$pageDivider='<!--nextpage-->';
				$postPageStmt = ", (character_length(`post_content`)  - character_length(REPLACE(`post_content`, '$pageDivider', ''))) / " . strlen($pageDivider) . " as postPages";
			}

			$sql="SELECT `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_status`, `post_name`, `post_modified`, `post_modified_gmt`, `post_parent`, `post_type` $postPageStmt $contentStmt FROM `" . $wpdb->posts . "` WHERE ";

			$where = '(';

			if($this->GetOption('in_posts')) {
				//WP < 2.1: posts are post_status = publish
				//WP >= 2.1: post_type must be 'post', no date check required because future posts are post_status='future'
				if($wpCompat) $where.="(post_status = 'publish' AND post_date_gmt <= '" . gmdate('Y-m-d H:i:59') . "')";
				else if ($this->IsCustomPostTypesSupported() && count($this->GetOption('in_customtypes'))>0) {
					$where.=" (post_status = 'publish' AND (post_type in ('','post'";
					foreach ($this->GetOption('in_customtypes') as $customType) {
						$where.= ",'$customType'";
					}
					$where .= "))) ";
				} else {
					$where.=" (post_status = 'publish' AND (post_type = 'post' OR post_type = '')) ";
				}
			}

			if($this->GetOption('in_pages')) {
				if($this->GetOption('in_posts')) {
					$where.=" OR ";
				}
				if($wpCompat) {
					//WP < 2.1: posts have post_status = published, pages have post_status = static
					$where.=" post_status='static' ";
				} else {
					//WP >= 2.1: posts have post_type = 'post' and pages have post_type = 'page'. Both must be published.
					$where.=" (post_status = 'publish' AND post_type = 'page') ";
				}
			}

			$where.=") ";


			if(is_array($excludes) && count($excludes)>0) {
				$where.=" AND ID NOT IN ('" . implode("','",$excludes) . "')";
			}

			$where.=" AND post_password='' ORDER BY post_modified DESC";

			$sql .= $where;

			if($this->GetOption("b_max_posts")>0) {
				$sql.=" LIMIT 0," . $this->GetOption("b_max_posts");
			}

			$postCount = intval($wpdb->get_var("SELECT COUNT(*) AS cnt FROM `" . $wpdb->posts . "` WHERE ". $where,0,0));

			//Create a new connection because we are using mysql_unbuffered_query and don't want to disturb the WP connection
			//Safe Mode for other plugins which use mysql_query() without a connection handler and will destroy our resultset :(
			$con = $postRes = null;

			//In 2.2, a bug which prevented additional DB connections was fixed
			if(floatval($wp_version) < 2.2) {
				$this->SetOption("b_safemode",true);
			}

			if($this->GetOption("b_safemode")===true) {
				$postRes = mysql_query($sql,$wpdb->dbh);
				if(!$postRes) {
					trigger_error("MySQL query failed: " . mysql_error(),E_USER_NOTICE); //E_USER_NOTICE will be displayed on our debug mode
					return;
				}
			} else {
				$con = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD,true);
				if(!$con) {
					trigger_error("MySQL Connection failed: " . mysql_error(),E_USER_NOTICE);
					return;
				}
				if(!mysql_select_db(DB_NAME,$con)) {
					trigger_error("MySQL DB Select failed: " . mysql_error(),E_USER_NOTICE);
					return;
				}
				$postRes = mysql_unbuffered_query($sql,$con);

				if(!$postRes) {
					trigger_error("MySQL unbuffered query failed: " . mysql_error(),E_USER_NOTICE);
					return;
				}
			}

			if($postRes) {

				//#type $prioProvider GoogleSitemapGeneratorPrioProviderBase
				$prioProvider=NULL;

				if($this->GetOption("b_prio_provider") != '') {
					$providerClass=$this->GetOption('b_prio_provider');
					$prioProvider = new $providerClass($commentCount,$postCount);
				}

				//$posts is used by Alex King's Popularity Contest plugin
				//if($posts == null || !is_array($posts)) {
				//	$posts = &$postRes;
				//}

				$z = 1;
				$zz = 1;

				//Default priorities
				$default_prio_posts = $this->GetOption('pr_posts');
				$default_prio_pages = $this->GetOption('pr_pages');

				//Change frequencies
				$cf_pages = $this->GetOption('cf_pages');
				$cf_posts = $this->GetOption('cf_posts');

				$minPrio=$this->GetOption('pr_posts_min');


				//Cycle through all posts and add them
				while($post = mysql_fetch_object($postRes)) {

					//Fill the cache with our DB result. Since it's incomplete (no text-content for example), we will clean it later.
					$cache = array(&$post);
					update_post_cache($cache);

					//Set the current working post for other plugins which depend on "the loop"
					$GLOBALS['post'] = &$post;

					$permalink = get_permalink($post->ID);
					if($permalink != $home && $post->ID != $homePid) {

						$isPage = false;
						if($wpCompat) {
							$isPage = ($post->post_status == 'static');
						} else {
							$isPage = ($post->post_type == 'page');
						}


						//Default Priority if auto calc is disabled
						$prio = 0;

						if($isPage) {
							//Priority for static pages
							$prio = $default_prio_pages;
						} else {
							//Priority for normal posts
							$prio = $default_prio_posts;
						}

						//If priority calc. is enabled, calculate (but only for posts, not pages)!
						if($prioProvider !== null && !$isPage) {

							//Comment count for this post
							$cmtcnt = (isset($comments[$post->ID])?$comments[$post->ID]:0);
							$prio = $prioProvider->GetPostPriority($post->ID, $cmtcnt, $post);

							if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry('Debug: Priority report of postID ' . $post->ID . ': Comments: ' . $cmtcnt . ' of ' . $commentCount . ' = ' . $prio . ' points'));
						}

						if(!$isPage && $minPrio>0 && $prio<$minPrio) {
							$prio = $minPrio;
						}

						//Add it
						$this->AddUrl($permalink,$this->GetTimestampFromMySql(($post->post_modified_gmt && $post->post_modified_gmt!='0000-00-00 00:00:00'?$post->post_modified_gmt:$post->post_date_gmt)),($isPage?$cf_pages:$cf_posts),$prio);

						if($inSubPages) {
							$subPage = '';
							for($p = 1; $p <= $post->postPages; $p++) {
								if(get_option('permalink_structure') == '') {
									$subPage = $permalink . '&amp;page=' . ($p+1);
								} else {
									$subPage = trailingslashit($permalink) . user_trailingslashit($p+1, 'single_paged');
								}

								$this->AddUrl($subPage,$this->GetTimestampFromMySql(($post->post_modified_gmt && $post->post_modified_gmt!='0000-00-00 00:00:00'?$post->post_modified_gmt:$post->post_date_gmt)),($isPage?$cf_pages:$cf_posts),$prio);
							}
						}

						// Multilingual Support with qTranslate, thanks to Qian Qin
						if($useQTransLate) {
							global $q_config;
							foreach(qtrans_getEnabledLanguages($post->post_content) as $language) {
								if($language!=$q_config['default_language']) {
									$this->AddUrl(qtrans_convertURL($permalink,$language),$this->GetTimestampFromMySql(($post->post_modified_gmt && $post->post_modified_gmt!='0000-00-00 00:00:00'?$post->post_modified_gmt:$post->post_date_gmt)),($isPage?$cf_pages:$cf_posts),$prio);
								}
							}
						}
					}

					//Update the status every 100 posts and at the end.
					//If the script breaks because of memory or time limit,
					//we have a "last reponded" value which can be compared to the server settings
					if($zz==100 || $z == $postCount) {
						$status->SaveStep($z);
						$zz=0;
					} else $zz++;

					$z++;

					//Clean cache because it's incomplete
					if(version_compare($wp_version,"2.5",">=")) {
						//WP 2.5 makes a mysql query for every clean_post_cache to clear the child cache
						//so I've copied the function here until a patch arrives...
						wp_cache_delete($post->ID, 'posts');
						wp_cache_delete($post->ID, 'post_meta');
						clean_object_term_cache($post->ID, 'post');
					} else {
						clean_post_cache($post->ID);
					}
				}
				unset($postRes);
				unset($prioProvider);

				if($this->GetOption("b_safemode")!==true && $con) mysql_close($con);
			}
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Postings"));
		}

		//Add the cats
		if($this->GetOption("in_cats")) {
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Cats"));

			$exclCats = $this->GetOption("b_exclude_cats"); // Excluded cats
			if($exclCats == null) $exclCats=array();

			if(!$this->IsTaxonomySupported()) {

				$catsRes=$wpdb->get_results("
							SELECT
								c.cat_ID AS ID,
								MAX(p.post_modified_gmt) AS last_mod
							FROM
								`" . $wpdb->categories . "` c,
								`" . $wpdb->post2cat . "` pc,
								`" . $wpdb->posts . "` p
							WHERE
								pc.category_id = c.cat_ID
								AND p.ID = pc.post_id
								AND p.post_status = 'publish'
								AND p.post_type='post'
							GROUP
								BY c.cat_id
							");
				if($catsRes) {
					foreach($catsRes as $cat) {
						if($cat && $cat->ID && $cat->ID>0 && !in_array($cat->ID, $exclCats)) {
							if($debug) if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Cat-ID:" . $cat->ID));
							$this->AddUrl(get_category_link($cat->ID),$this->GetTimestampFromMySql($cat->last_mod),$this->GetOption("cf_cats"),$this->GetOption("pr_cats"));
						}
					}
				}
			} else {
				$cats = get_terms("category",array("hide_empty"=>true,"hierarchical"=>false));
				if($cats && is_array($cats) && count($cats)>0) {
					foreach($cats AS $cat) {
						if(!in_array($cat->term_id, $exclCats)) $this->AddUrl(get_category_link($cat->term_id),0,$this->GetOption("cf_cats"),$this->GetOption("pr_cats"));
					}
				}
			}
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Cats"));
		}

		//Add the archives
		if($this->GetOption("in_arch")) {
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Archive"));
			$now = current_time('mysql');

			//WP2.1 introduced post_status='future', for earlier WP versions we need to check the post_date_gmt
			$arcresults = $wpdb->get_results("
						SELECT DISTINCT
							YEAR(post_date_gmt) AS `year`,
							MONTH(post_date_gmt) AS `month`,
							MAX(post_date_gmt) as last_mod,
							count(ID) as posts
						FROM
							$wpdb->posts
						WHERE
							post_date < '$now'
							AND post_status = 'publish'
							AND post_type = 'post'
							" . (floatval($wp_version) < 2.1?"AND {$wpdb->posts}.post_date_gmt <= '" . gmdate('Y-m-d H:i:59') . "'":"") . "
						GROUP BY
							YEAR(post_date_gmt),
							MONTH(post_date_gmt)
						ORDER BY
							post_date_gmt DESC");
			if ($arcresults) {
				foreach ($arcresults as $arcresult) {

					$url  = get_month_link($arcresult->year,   $arcresult->month);
					$changeFreq="";

					//Archive is the current one
					if($arcresult->month==date("n") && $arcresult->year==date("Y")) {
						$changeFreq=$this->GetOption("cf_arch_curr");
					} else { // Archive is older
						$changeFreq=$this->GetOption("cf_arch_old");
					}

					$this->AddUrl($url,$this->GetTimestampFromMySql($arcresult->last_mod),$changeFreq,$this->GetOption("pr_arch"));
				}
			}
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Archive"));
		}

		//Add the author pages
		if($this->GetOption("in_auth")) {
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Author pages"));

			$linkFunc = null;

			//get_author_link is deprecated in WP 2.1, try to use get_author_posts_url first.
			if(function_exists('get_author_posts_url')) {
				$linkFunc = 'get_author_posts_url';
			} else if(function_exists('get_author_link')) {
				$linkFunc = 'get_author_link';
			}

			//Who knows what happens in later WP versions, so check again if it worked
			if($linkFunc !== null) {
			    //Unfortunately there is no API function to get all authors, so we have to do it the dirty way...
				//We retrieve only users with published and not password protected posts (and not pages)
				//WP2.1 introduced post_status='future', for earlier WP versions we need to check the post_date_gmt
				$sql = "SELECT DISTINCT
							u.ID,
							u.user_nicename,
							MAX(p.post_modified_gmt) AS last_post
						FROM
							{$wpdb->users} u,
							{$wpdb->posts} p
						WHERE
							p.post_author = u.ID
							AND p.post_status = 'publish'
							AND p.post_type = 'post'
							AND p.post_password = ''
							" . (floatval($wp_version) < 2.1?"AND p.post_date_gmt <= '" . gmdate('Y-m-d H:i:59') . "'":"") . "
						GROUP BY
							u.ID,
							u.user_nicename";

				$authors = $wpdb->get_results($sql);

				if($authors && is_array($authors)) {
					foreach($authors as $author) {
						if($debug) if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Author-ID:" . $author->ID));
						$url = ($linkFunc=='get_author_posts_url'?get_author_posts_url($author->ID,$author->user_nicename):get_author_link(false,$author->ID,$author->user_nicename));
						$this->AddUrl($url,$this->GetTimestampFromMySql($author->last_post),$this->GetOption("cf_auth"),$this->GetOption("pr_auth"));
					}
				}
			} else {
				//Too bad, no author pages for you :(
				if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: No valid author link function found"));
			}

			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Author pages"));
		}

		//Add tag pages
		if($this->GetOption("in_tags") && $this->IsTaxonomySupported()) {
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Tags"));
			$tags = get_terms("post_tag",array("hide_empty"=>true,"hierarchical"=>false));
			if($tags && is_array($tags) && count($tags)>0) {
				foreach($tags AS $tag) {
					$this->AddUrl(get_tag_link($tag->term_id),0,$this->GetOption("cf_tags"),$this->GetOption("pr_tags"));
				}
			}
			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Tags"));
		}

		//Add custom taxonomy pages
		if($this->GetOption("in_tax") && $this->IsTaxonomySupported()) {

			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start custom taxonomies"));

			$enabledTaxonomies = $this->GetOption("in_tax");

			$taxList = array();

			foreach ($enabledTaxonomies as $taxName) {
				$taxonomy = get_taxonomy($taxName);
				if($taxonomy) $taxList[] = $wpdb->escape($taxonomy->name);
			}

			if(count($taxList)>0) {
				//We're selecting all term information (t.*) plus some additional fields
				//like the last mod date and the taxonomy name, so WP doesnt need to make
				//additional queries to build the permalink structure.
				//This does NOT work for categories and tags yet, because WP uses get_category_link
				//and get_tag_link internally and that would cause one additional query per term!
				$sql="
					SELECT
						t.*,
						tt.taxonomy AS _taxonomy,
						UNIX_TIMESTAMP(MAX(post_date_gmt)) as _mod_date
					FROM
						{$wpdb->posts} p ,
						{$wpdb->term_relationships} r,
						{$wpdb->terms} t,
						{$wpdb->term_taxonomy} tt
					WHERE
						p.ID = r.object_id
						AND p.post_status = 'publish'
						AND p.post_type = 'post'
						AND p.post_password = ''
						AND r.term_taxonomy_id = t.term_id
						AND t.term_id = tt.term_id
						AND tt.count > 0
						AND tt.taxonomy IN ('" . implode("','",$taxList) . "')
					GROUP BY
						t.term_id";

				$termInfo = $wpdb->get_results($sql);

				foreach($termInfo AS $term) {
					$this->AddUrl(get_term_link($term->slug,$term->_taxonomy),$term->_mod_date ,$this->GetOption("cf_tags"),$this->GetOption("pr_tags"));
				}
			}

			if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End custom taxonomies"));
		}

		//Add the custom pages
		if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start Custom Pages"));
		if($this->_pages && is_array($this->_pages) && count($this->_pages)>0) {
			//#type $page GoogleSitemapGeneratorPage
			foreach($this->_pages AS $page) {
				$this->AddUrl($page->GetUrl(),$page->getLastMod(),$page->getChangeFreq(),$page->getPriority());
			}
		}

		if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End Custom Pages"));

		if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: Start additional URLs"));

		do_action('sm_buildmap');

		if($debug) $this->AddElement(new GoogleSitemapGeneratorDebugEntry("Debug: End additional URLs"));

		$this->AddElement(new GoogleSitemapGeneratorXmlEntry("</urlset>"));


		$pingUrl='';

		if($this->GetOption("b_xml")) {
			if($this->_fileHandle && fclose($this->_fileHandle)) {
				$this->_fileHandle = null;
				$status->EndXml(true);
				$pingUrl=$this->GetXmlUrl();
			} else $status->EndXml(false,"Could not close the sitemap file.");
		}

		if($this->IsGzipEnabled()) {
			if($this->_fileZipHandle && fclose($this->_fileZipHandle)) {
				$this->_fileZipHandle = null;
				$status->EndZip(true);
				$pingUrl=$this->GetZipUrl();
			} else $status->EndZip(false,"Could not close the zipped sitemap file");
		}

		//Ping Google
		if($this->GetOption("b_ping") && !empty($pingUrl)) {
			$sPingUrl="http://www.google.com/webmasters/sitemaps/ping?sitemap=" . urlencode($pingUrl);
			$status->StartGooglePing($sPingUrl);
			$pingres=$this->RemoteOpen($sPingUrl);

			if($pingres==NULL || $pingres===false) {
				$status->EndGooglePing(false,$this->_lastError);
				trigger_error("Failed to ping Google: " . htmlspecialchars(strip_tags($pingres)),E_USER_NOTICE);
			} else {
				$status->EndGooglePing(true);
			}
		}


		//Ping Bing
		if($this->GetOption("b_pingmsn") && !empty($pingUrl)) {
			$sPingUrl="http://www.bing.com/webmaster/ping.aspx?siteMap=" . urlencode($pingUrl);
			$status->StartMsnPing($sPingUrl);
			$pingres=$this->RemoteOpen($sPingUrl);
			//Bing returns ip/country-based success messages, so there is no way to check the content. Rely on HTTP 500 only then...
			if($pingres==NULL || $pingres===false || strpos($pingres," ")===false) {
				trigger_error("Failed to ping Bing: " . htmlspecialchars(strip_tags($pingres)),E_USER_NOTICE);
				$status->EndMsnPing(false,$this->_lastError);
			} else {
				$status->EndMsnPing(true);
			}
		}

		$status->End();


		$this->_isActive = false;

		//done...
		return $status;
	}

	/**
	 * Tries to ping a specific service showing as much as debug output as possible
	 * @since 3.1.9
	 * @return null
	 */
	function ShowPingResult() {

		check_admin_referer('sitemap');

		if(!current_user_can("administrator")) {
			echo '<p>Please log in as admin</p>';
			return;
		}

		$service = !empty($_GET["sm_ping_service"])?$_GET["sm_ping_service"]:null;

		$status = &GoogleSitemapGeneratorStatus::Load();

		if(!$status) die("No build status yet. Build the sitemap first.");

		$url = null;

		switch($service) {
			case "google":
				$url = $status->_googleUrl;
				break;
			case "msn":
				$url = $status->_msnUrl;
				break;
		}

		if(empty($url)) die("Invalid ping url");

		echo '<html><head><title>Ping Test</title>';
		if(function_exists('wp_admin_css')) wp_admin_css('css/global',true);
		echo '</head><body><h1>Ping Test</h1>';

		echo '<p>Trying to ping: <a href="' . $url . '">' . $url . '</a>. The sections below should give you an idea whats going on.</p>';

		//Try to get as much as debug / error output as possible
		$errLevel = error_reporting(E_ALL);
		$errDisplay = ini_set("display_errors",1);
		if(!defined('WP_DEBUG')) define('WP_DEBUG',true);

		echo '<h2>Errors, Warnings, Notices:</h2>';

		if(WP_DEBUG == false) echo "<i>WP_DEBUG was set to false somewhere before. You might not see all debug information until you remove this declaration!</i><br />";
		if(ini_get("display_errors")!=1) echo "<i>Your display_errors setting currently prevents the plugin from showing errors here. Please check your webserver logfile instead.</i><br />";

		$res = $this->RemoteOpen($url);

		echo '<h2>Result (text only):</h2>';

		echo wp_kses($res,array('a' => array('href' => array()),'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array()));

		echo '<h2>Result (HTML):</h2>';

		echo htmlspecialchars($res);

		//Revert back old values
		error_reporting($errLevel);
		ini_set("display_errors",$errDisplay);
		echo '</body></html>';
		exit;
	}

	/**
	 * Opens a remote file using the WordPress API or Snoopy
	 * @since 3.0
	 * @param $url The URL to open
	 * @param $method get or post
	 * @param $postData An array with key=>value paris
	 * @param $timeout Timeout for the request, by default 10
	 * @return mixed False on error, the body of the response on success
	 */
	function RemoteOpen($url,$method = 'get', $postData = null, $timeout = 10) {
		global $wp_version;

		//Before WP 2.7, wp_remote_fopen was quite crappy so Snoopy was favoured.
		if(floatval($wp_version) < 2.7) {
			if(!file_exists(ABSPATH . 'wp-includes/class-snoopy.php')) {
				trigger_error('Snoopy Web Request failed: Snoopy not found.',E_USER_NOTICE);
				return false; //Hoah?
			}

			require_once( ABSPATH . 'wp-includes/class-snoopy.php');

			$s = new Snoopy();

			$s->read_timeout = $timeout;

			if($method == 'get') {
				$s->fetch($url);
			} else {
				$s->submit($url,$postData);
			}

			if($s->status != "200") {
				trigger_error('Snoopy Web Request failed: Status: ' . $s->status . "; Content: " . htmlspecialchars($s->results),E_USER_NOTICE);
			}

			return $s->results;

		} else {

			$options = array();
			$options['timeout'] = $timeout;

			if($method == 'get') {
				$response = wp_remote_get( $url, $options );
			} else {
				$response = wp_remote_post($url, array_merge($options,array('body'=>$postData)));
			}

			if ( is_wp_error( $response ) ) {
				$errs = $response->get_error_messages();
				$errs = htmlspecialchars(implode('; ', $errs));
				trigger_error('WP HTTP API Web Request failed: ' . $errs,E_USER_NOTICE);
				return false;
			}

			return $response['body'];
		}

		return false;
	}

	/**
	 * Echos option fields for an select field containing the valid change frequencies
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $currentVal The value which should be selected
	 * @return all valid change frequencies as html option fields
	 */
	function HtmlGetFreqNames($currentVal) {

		foreach($this->_freqNames AS $k=>$v) {
			echo "<option value=\"$k\" " . $this->HtmlGetSelected($k,$currentVal) .">" . $v . "</option>";
		}
	}

	/**
	 * Echos option fields for an select field containing the valid priorities (0- 1.0)
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $currentVal string The value which should be selected
	 * @return 0.0 - 1.0 as html option fields
	 */
	function HtmlGetPriorityValues($currentVal) {
		$currentVal=(float) $currentVal;
		for($i=0.0; $i<=1.0; $i+=0.1) {
			$v = number_format($i,1,".","");
			//number_format_i18n is there since WP 2.3
			$t = function_exists('number_format_i18n')?number_format_i18n($i,1):number_format($i,1);
			echo "<option value=\"" . $v . "\" " . $this->HtmlGetSelected("$i","$currentVal") .">";
			echo $t;
			echo "</option>";
		}
	}

	/**
	 * Returns the checked attribute if the given values match
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $val string The current value
	 * @param $equals string The value to match
	 * @return The checked attribute if the given values match, an empty string if not
	 */
	function HtmlGetChecked($val,$equals) {
		if($val==$equals) return $this->HtmlGetAttribute("checked");
		else return "";
	}

	/**
	 * Returns the selected attribute if the given values match
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $val string The current value
	 * @param $equals string The value to match
	 * @return The selected attribute if the given values match, an empty string if not
	 */
	function HtmlGetSelected($val,$equals) {
		if($val==$equals) return $this->HtmlGetAttribute("selected");
		else return "";
	}

	/**
	 * Returns an formatted attribute. If the value is NULL, the name will be used.
	 *
	 * @since 3.0
	 * @access private
	 * @author Arne Brachhold
	 * @param $attr string The attribute name
	 * @param $value string The attribute value
	 * @return The formatted attribute
	 */
	function HtmlGetAttribute($attr,$value=NULL) {
		if($value==NULL) $value=$attr;
		return " " . $attr . "=\"" . $value . "\" ";
	}

	/**
	 * Returns an array with GoogleSitemapGeneratorPage objects which is generated from POST values
	 *
	 * @since 3.0
	 * @see GoogleSitemapGeneratorPage
	 * @access private
	 * @author Arne Brachhold
	 * @return array An array with GoogleSitemapGeneratorPage objects
	 */
	function HtmlApplyPages() {
		// Array with all page URLs
		$pages_ur=(!isset($_POST["sm_pages_ur"]) || !is_array($_POST["sm_pages_ur"])?array():$_POST["sm_pages_ur"]);

		//Array with all priorities
		$pages_pr=(!isset($_POST["sm_pages_pr"]) || !is_array($_POST["sm_pages_pr"])?array():$_POST["sm_pages_pr"]);

		//Array with all change frequencies
		$pages_cf=(!isset($_POST["sm_pages_cf"]) || !is_array($_POST["sm_pages_cf"])?array():$_POST["sm_pages_cf"]);

		//Array with all lastmods
		$pages_lm=(!isset($_POST["sm_pages_lm"]) || !is_array($_POST["sm_pages_lm"])?array():$_POST["sm_pages_lm"]);

		//Array where the new pages are stored
		$pages=array();
		//Loop through all defined pages and set their properties into an object
		if(isset($_POST["sm_pages_mark"]) && is_array($_POST["sm_pages_mark"])) {
			for($i=0; $i<count($_POST["sm_pages_mark"]); $i++) {
				//Create new object
				$p=new GoogleSitemapGeneratorPage();
				if(substr($pages_ur[$i],0,4)=="www.") $pages_ur[$i]="http://" . $pages_ur[$i];
				$p->SetUrl($pages_ur[$i]);
				$p->SetProprity($pages_pr[$i]);
				$p->SetChangeFreq($pages_cf[$i]);
				//Try to parse last modified, if -1 (note ===) automatic will be used (0)
				$lm=(!empty($pages_lm[$i])?strtotime($pages_lm[$i],time()):-1);
				if($lm===-1) $p->setLastMod(0);
				else $p->setLastMod($lm);
				//Add it to the array
				array_push($pages,$p);
			}
		}

		return $pages;
	}

	/**
	 * Converts a mysql datetime value into a unix timestamp
	 *
	 * @param The value in the mysql datetime format
	 * @return int The time in seconds
	 */
	function GetTimestampFromMySql($mysqlDateTime) {
		list($date, $hours) = explode(' ', $mysqlDateTime);
		list($year,$month,$day) = explode('-',$date);
		list($hour,$min,$sec) = explode(':',$hours);
		return mktime(intval($hour), intval($min), intval($sec), intval($month), intval($day), intval($year));
	}

	/**
	 * Returns a link pointing to a spcific page of the authors website
	 *
	 * @since 3.0
	 * @param The page to link to
	 * @return string The full url
	 */
	function GetRedirectLink($redir) {
		return trailingslashit("http://www.arnebrachhold.de/redir/" . $redir);
	}

	/**
	 * Returns a link pointing back to the plugin page in WordPress
	 *
	 * @since 3.0
	 * @return string The full url
	 */
	function GetBackLink() {
		global $wp_version;
		$url = '';
		//admin_url was added in WP 2.6.0
		if(function_exists("admin_url")) $url = admin_url("options-general.php?page=" .  GoogleSitemapGeneratorLoader::GetBaseName());
		else $url = $_SERVER['PHP_SELF'] . "?page=" .  GoogleSitemapGeneratorLoader::GetBaseName();

		//Some browser cache the page... great! So lets add some no caching params depending on the WP and plugin version
		$url.='&sm_wpv=' . $wp_version . '&sm_pv=' . GoogleSitemapGeneratorLoader::GetVersion();

		return $url;
	}

	/**
	 * Shows the option page of the plugin. Before 3.1.1, this function was basically the UI, afterwards the UI was outsourced to another class
	 *
	 * @see GoogleSitemapGeneratorUI
	 * @since 3.0
	 * @return bool
	 */
	function HtmlShowOptionsPage() {

		$ui = $this->GetUI();
		if($ui) {
			$ui->HtmlShowOptionsPage();
			return true;
		}

		return false;
	}

	/**
	 * Includes the user interface class and intializes it
	 *
	 * @since 3.1.1
	 * @see GoogleSitemapGeneratorUI
	 * @return GoogleSitemapGeneratorUI
	 */
	function GetUI() {

		global $wp_version;

		if($this->_ui === null) {

			$className='GoogleSitemapGeneratorUI';
			$fileName='sitemap-ui.php';

			if(!class_exists($className)) {

				$path = trailingslashit(dirname(__FILE__));

				if(!file_exists( $path . $fileName)) return false;
				require_once($path. $fileName);
			}

			$this->_ui = new $className($this);

		}

		return $this->_ui;
	}

	function HtmlShowHelp() {


	}
}