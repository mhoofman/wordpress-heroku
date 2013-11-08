<?php
/* 
Plugin Name: Google Analytics Dashboard for WP
Plugin URI: http://deconf.com
Description: This plugin will display Google Analytics data and statistics into Admin Dashboard. 
Author: Alin Marcu
Version: 4.2.2
Author URI: http://deconf.com
*/  

$plugin = plugin_basename(__FILE__);
add_filter('the_content', 'ga_dash_front_content');  
add_action('wp_dashboard_setup', 'ga_dash_setup');
add_action('admin_menu', 'ga_dash_admin_actions'); 
add_action('admin_enqueue_scripts', 'ga_dash_admin_enqueue_scripts');
add_action('plugins_loaded', 'ga_dash_init');
add_action('wp_head', 'ga_dash_tracking');
add_filter("plugin_action_links_$plugin", 'ga_dash_settings_link' );
add_action('wp_enqueue_scripts', 'ga_dash_enqueue_scripts');

function ga_dash_admin() {  
    include('ga_dash_admin.php');  
} 
	
function ga_dash_admin_actions() {
	if (current_user_can('manage_options')) {  
		add_options_page(__("Google Analytics Dashboard",'ga-dash'), __("GA Dashboard",'ga-dash'), "manage_options", "Google_Analytics_Dashboard", "ga_dash_admin");
	}
}  

function ga_dash_init() {
  	load_plugin_textdomain( 'ga-dash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

function ga_dash_admin_enqueue_scripts() {
	if (get_option('ga_dash_style')=="blue"){
		wp_register_style( 'ga_dash', plugins_url('ga_dash.css', __FILE__) );
	} else{
		wp_register_style( 'ga_dash', plugins_url('ga_dash_light.css', __FILE__) );
	}
	wp_enqueue_style( 'ga_dash' );	
	wp_register_style( 'jquery-ui-tooltip-1.9.2', plugins_url('jquery/jquery.ui.tooltip.min.1.9.2.css', __FILE__) );
	wp_register_script("jquery-ui-tooltip-1.9.2",plugins_url('jquery/jquery.ui.tooltip.min.1.9.2.js', __FILE__));
}

function ga_dash_setup() {
	if (current_user_can(get_option('ga_dash_access'))) {
		wp_add_dashboard_widget(
			'ga-dash-widget',
			__("Google Analytics Dashboard",'ga-dash'),
			'ga_dash_content',
			$control_callback = null
		);
	}
}

function ga_dash_enqueue_scripts() {
	if (get_option('ga_event_tracking') AND !wp_script_is('jquery')) {
		wp_enqueue_script('jquery');
	}	
}

function ga_dash_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=Google_Analytics_Dashboard">'.__("Settings",'ga-dash').'</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

function ga_dash_tracking($head) {

	$traking_mode=get_option('ga_dash_tracking');
	$traking_type=get_option('ga_dash_tracking_type');
	if ($traking_mode<>4){
		require_once 'functions.php';
		if ($traking_type=="universal"){

			if (current_user_can(get_option('ga_track_exclude'))) {
				return;
			}
		
			if (get_option('ga_event_tracking')){
				require_once 'events/events-universal.php';
			}
			
			echo ga_dash_universal_tracking();
			
		} else{

			if (current_user_can(get_option('ga_track_exclude'))) {
				return;
			}		
		
			if (get_option('ga_event_tracking')){
				require_once 'events/events-classic.php';
			}
			
			echo ga_dash_classic_tracking();
			
		}
	}
}

function ga_dash_front_content($content) {
	global $post;
	if (!current_user_can(get_option('ga_dash_access_front')) OR !get_option('ga_dash_frontend')) {
		return $content;
	}

	if(!is_feed() && !is_home() && !is_front_page()) {

		require_once 'functions.php';
		
		ga_maintain_compatibility();

		if (!class_exists('Google_Exception')) {
			require_once 'src/Google_Client.php';
		}
			
		require_once 'src/contrib/Google_AnalyticsService.php';

		$client = new Google_Client();
		$client->setAccessType('offline');
		$client->setApplicationName('Google Analytics Dashboard for WordPress');
		$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
		
		if (get_option('ga_dash_userapi')){	
			$client->setClientId(get_option('ga_dash_clientid'));
			$client->setClientSecret(get_option('ga_dash_clientsecret'));
			$client->setDeveloperKey(get_option('ga_dash_apikey'));
		}else{
			$client->setClientId('65556128781.apps.googleusercontent.com');
			$client->setClientSecret('Kc7888wgbc_JbeCpbFjnYpwE');
			$client->setDeveloperKey('AIzaSyBG7LlUoHc29ZeC_dsShVaBEX15SfRl_WY');
		}
	
		$service = new Google_AnalyticsService($client);

		if (ga_dash_get_token()) { 
			$token = ga_dash_get_token();
			$client->setAccessToken($token);
		}else{
			return $content;
		}		
		
		$from = date('Y-m-d', time()-30*24*60*60);
		$to = date('Y-m-d');		
		$metrics = 'ga:pageviews,ga:uniquePageviews';
		$dimensions = 'ga:year,ga:month,ga:day';
		$page_url = $_SERVER["REQUEST_URI"];
		$post_id = $post->ID;
		$title = __("Views vs UniqueViews", 'ga-dash');
		if (get_option('ga_dash_style')=="light"){ 
			$css="colors:['gray','darkgray'],";
			$colors="gray";
		} else{
			$css="colors:['#3366CC','#3366CC'],";
			$colors="blue";
		}		

		if (get_option('ga_dash_tableid_jail')) {
			$projectId = get_option('ga_dash_tableid_jail');
		} else{
			return $content;
		}	
		
		try{
			$serial='gadash_qr21'.$post_id.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to.$metrics);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions,'filters' => 'ga:pagePath=='.$page_url));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}	
		}  
			catch(exception $e) {
			return $content;
		}
		if (!$data['rows']){
			return $content;
		}
		
		$ga_dash_statsdata="";
		for ($i=0;$i<$data['totalResults'];$i++){
			$ga_dash_statsdata.="['".$data['rows'][$i][0]."-".$data['rows'][$i][1]."-".$data['rows'][$i][2]."',".round($data['rows'][$i][3],2).",".round($data['rows'][$i][4],2)."],";
		}
		$ga_dash_statsdata=rtrim($ga_dash_statsdata,',');
		$metrics = 'ga:visits'; 
		$dimensions = 'ga:keyword';
		try{
			$serial='gadash_qr22'.$post_id.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions, 'sort' => '-ga:visits', 'max-results' => '24', 'filters' => 'ga:keyword!=(not provided);ga:keyword!=(not set);ga:pagePath=='.$page_url));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		}  
			catch(exception $e) {
			return $content; 
		}	

		$ga_dash_organicdata="";
		if (isset($data['rows'])){
			$i=0;
			while (isset($data['rows'][$i][0])){
				$ga_dash_organicdata.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
				$i++;
			}
			$ga_dash_organicdata=rtrim($ga_dash_organicdata,',');			
		}	

		$content.='<style>
		#ga_dash_sdata td{
			line-height:1.5em;
			padding:2px;
			font-size:1em;
		}
		#ga_dash_sdata{
			line-height:10px;
		}
		</style>';
		
		$content.='<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
		  google.load("visualization", "1", {packages:["corechart"]});
		  google.setOnLoadCallback(ga_dash_callback);

		  function ga_dash_callback(){
				ga_dash_drawstats();
				if(typeof ga_dash_drawsd == "function"){
					ga_dash_drawsd();
				}				
		  }	

		  function ga_dash_drawstats() {
			var data = google.visualization.arrayToDataTable(['."
			  ['".__("Date", 'ga-dash')."', '".__("Views", 'ga-dash')."', '".__("UniqueViews", 'ga-dash')."'],"
			  .$ga_dash_statsdata.
			"  
			]);

			var options = {
			  legend: {position: 'none'},	
			  pointSize: 3,".$css."
			  title: '".$title."',
			  chartArea: {width: '85%'},
			  hAxis: { showTextEvery: 5}
			};

			var chart = new google.visualization.AreaChart(document.getElementById('ga_dash_statsdata'));
			chart.draw(data, options);
			
			}";
		if ($ga_dash_organicdata){
			$content.='
					google.load("visualization", "1", {packages:["table"]})
					function ga_dash_drawsd() {
					
					var datas = google.visualization.arrayToDataTable(['."
					  ['".__("Top Searches",'ga-dash')."', '".__("Visits",'ga-dash')."'],"
					  .$ga_dash_organicdata.
					"  
					]);
					
					var options = {
						page: 'enable',
						pageSize: 6,
						width: '100%',
					};        
					
					var chart = new google.visualization.Table(document.getElementById('ga_dash_sdata'));
					chart.draw(datas, options);
					
				  }";
		}
		  $content.="</script>";		
		
		
		$content .= '<div id="ga_dash_statsdata"></div><div id="ga_dash_sdata" ></div>';
	}
	return $content;
}

function ga_dash_content() {
	
	require_once 'functions.php';
	
	ga_maintain_compatibility();
	
	if (!class_exists('Google_Exception')) {
		require_once 'src/Google_Client.php';
	}
		
	require_once 'src/contrib/Google_AnalyticsService.php';
	
	$client = new Google_Client();
	$client->setAccessType('offline');
	$client->setApplicationName('Google Analytics Dashboard');
	$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
	
	if (get_option('ga_dash_userapi')){		
			$client->setClientId(get_option('ga_dash_clientid'));
			$client->setClientSecret(get_option('ga_dash_clientsecret'));
			$client->setDeveloperKey(get_option('ga_dash_apikey'));
	}else{
			$client->setClientId('65556128781.apps.googleusercontent.com');
			$client->setClientSecret('Kc7888wgbc_JbeCpbFjnYpwE');
			$client->setDeveloperKey('AIzaSyBG7LlUoHc29ZeC_dsShVaBEX15SfRl_WY');
		}
	
	$service = new Google_AnalyticsService($client);

	if (ga_dash_get_token()) { 
		$token = ga_dash_get_token();
		$token= ga_dash_refresh_token($client);
		$client->setAccessToken($token);
	}

	if (!$client->getAccessToken()) {
		
		$authUrl = $client->createAuthUrl();
		
		if (!isset($_REQUEST['ga_dash_authorize'])){
			if (!current_user_can('manage_options')){
				_e("Ask an admin to authorize this Application", 'ga-dash');
				return;
			}
			echo '<div style="padding:20px;">'.__("Use this link to get your access code:", 'ga-dash').' <a href="'.$authUrl.'" target="_blank">'.__("Get Access Code", 'ga-dash').'</a>';
			echo '<form name="input" action="#" method="get">
						<p><b>'.__("Access Code:", 'ga-dash').' </b><input type="text" name="ga_dash_code" value="" size="61"></p>
						<input type="submit" class="button button-primary" name="ga_dash_authorize" value="'.__("Save Access Code", 'ga-dash').'"/>
					</form>
				</div>';
			return;
		}		
		else{
			if ($_REQUEST['ga_dash_code']){
				$client->authenticate($_REQUEST['ga_dash_code']);
				ga_dash_store_token($client->getAccessToken());
				$google_token= json_decode($client->getAccessToken());
				ga_dash_store_refreshtoken($google_token->refresh_token);
			} else{
			
				$adminurl = admin_url("#ga-dash-widget");
				echo '<script> window.location="'.$adminurl.'"; </script> ';
			
			}	
		}

	}
	
	if (current_user_can('manage_options')) {
	
		if (isset($_REQUEST['ga_dash_profiles'])){ 
			update_option('ga_dash_tableid',$_REQUEST['ga_dash_profiles']);
		}	

		try {
			$client->setUseObjects(true);
			$profile_switch="";
			$serial='gadash_qr1';
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$profiles = $service->management_profiles->listManagementProfiles('~all','~all');
				set_transient( $serial, $profiles, 60*60*24);
			}else{
				$profiles = $transient;		
			}
			
			$items = $profiles->getItems();
			$profile_switch.= '<form><select id="ga_dash_profiles" name="ga_dash_profiles" onchange="this.form.submit()">';
			
			if (count($items) != 0) {
				$ga_dash_profile_list="";
				foreach ($items as &$profile) {
					if (!get_option('ga_dash_tableid')) {
						update_option('ga_dash_tableid',$profile->getId());
					}
					$profile_switch.= '<option value="'.$profile->getId().'"'; 
					if ((get_option('ga_dash_tableid')==$profile->getId())) $profile_switch.= "selected='yes'";
					$profile_switch.= '>'.ga_dash_get_profile_domain($profile->getwebsiteUrl()).'</option>';
					$ga_dash_profile_list[]=array($profile->getName(),$profile->getId(),$profile->getwebPropertyId(), $profile->getwebsiteUrl());
				}
				update_option('ga_dash_profile_list',$ga_dash_profile_list);
			}
			$profile_switch.= "</select></form><br />";
			$client->setUseObjects(false);
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}
	}
	if (current_user_can('manage_options')) { 
		if (get_option('ga_dash_jailadmins')){
			if (get_option('ga_dash_tableid_jail')){
				$projectId = get_option('ga_dash_tableid_jail');
			}else{
				_e("Ask an admin to asign a Google Analytics Profile", 'ga-dash');
				return;
			}
		}else{
			echo $profile_switch;
			$projectId = get_option('ga_dash_tableid');
		}	
	} else{
		if (get_option('ga_dash_tableid_jail')){
			$projectId = get_option('ga_dash_tableid_jail');
		}else{
			_e("Ask an admin to asign a Google Analytics Profile", 'ga-dash');
			return;
		}	
	}
	
	ga_dash_store_token($client->getAccessToken());	
	
	if(isset($_REQUEST['query']))
		$query = $_REQUEST['query'];
	else	
		$query = "visits";

	if(isset($_REQUEST['period']))	
		$period = $_REQUEST['period'];
	else
		$period = "last30days"; 	

	if(isset($_REQUEST['realtime']))
		$realtime = $_REQUEST['realtime'];
	else	
		$realtime = "";
		
	switch ($period){

		case 'today'	:	$from = date('Y-m-d'); 
							$to = date('Y-m-d');
							break;

		case 'yesterday'	:	$from = date('Y-m-d', time()-24*60*60);
								$to = date('Y-m-d', time()-24*60*60);
								break;
		
		case 'last30days'	:	$from = date('Y-m-d', time()-30*24*60*60);
							$to = date('Y-m-d');
							break;	
							
		default	:	$from = date('Y-m-d', time()-90*24*60*60);
					$to = date('Y-m-d');
					break;

	}

	if ($realtime=="realtime") {
		wp_enqueue_style( 'jquery-ui-tooltip-1.9.2');
		wp_enqueue_script("jquery-ui-tooltip-1.9.2");

		if (!wp_script_is('jquery')) {
			wp_enqueue_script('jquery');
		}
		if (!wp_script_is('jquery-ui-core')) {		
			wp_enqueue_script("jquery-ui-core");
		}
		if (!wp_script_is('jquery-ui-position')) {		
			wp_enqueue_script("jquery-ui-position");
		}
		if (!wp_script_is('jquery-ui-position')) {			
			wp_enqueue_script("jquery-ui-position");
		}

	} else{	
	
	switch ($query){

		case 'visitors'	:	$title=__("Visitors",'ga-dash'); break;

		case 'pageviews'	:	$title=__("Page Views",'ga-dash'); break;
		
		case 'visitBounceRate'	:	$title=__("Bounce Rate",'ga-dash'); break;	

		case 'organicSearches'	:	$title=__("Organic Searches",'ga-dash'); break;
		
		default	:	$title=__("Visits",'ga-dash');

	}

	$metrics = 'ga:'.$query;
	$dimensions = 'ga:year,ga:month,ga:day';

	try{
		$serial='gadash_qr2'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to.$metrics);
		$transient = get_transient($serial);
		if ( empty( $transient ) ){
			$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions));
			set_transient( $serial, $data, get_option('ga_dash_cachetime') );
		}else{
			$data = $transient;		
		}	
	} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
	}
	$ga_dash_statsdata="";
	for ($i=0;$i<$data['totalResults'];$i++){
		$ga_dash_statsdata.="['".$data['rows'][$i][0]."-".$data['rows'][$i][1]."-".$data['rows'][$i][2]."',".round($data['rows'][$i][3],2)."],";
	}
	$ga_dash_statsdata=rtrim($ga_dash_statsdata,',');
	$metrics = 'ga:visits,ga:visitors,ga:pageviews,ga:visitBounceRate,ga:organicSearches,ga:timeOnSite';
	$dimensions = 'ga:year';
	try{
		$serial='gadash_qr3'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
		$transient = get_transient($serial);
		if ( empty( $transient ) ){
			$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions));
			set_transient( $serial, $data, get_option('ga_dash_cachetime') );
		}else{
			$data = $transient;		
		}	
	} catch (Google_ServiceException $e) {
		echo ga_dash_pretty_error($e);
		return;
	}
	
}	

	if (get_option('ga_dash_style')=="light"){ 
		$css="colors:['gray','darkgray'],";
		$colors="gray";
	} else{
		$css="";
		$colors="blue";
	}
	
    $code='<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(ga_dash_callback);

	  function ga_dash_callback(){
			if(typeof ga_dash_drawstats == "function"){
				ga_dash_drawstats();
			}	
			if(typeof ga_dash_drawmap == "function"){
				ga_dash_drawmap();
			}
			if(typeof ga_dash_drawpgd == "function"){
				ga_dash_drawpgd();
			}			
			if(typeof ga_dash_drawrd == "function"){
				ga_dash_drawrd();
			}
			if(typeof ga_dash_drawsd == "function"){
				ga_dash_drawsd();
			}
			if(typeof ga_dash_drawtraffic == "function"){
				ga_dash_drawtraffic();
			}			
	  }';

	if ($realtime!="realtime"){	  

      $code.='function ga_dash_drawstats() {
        var data = google.visualization.arrayToDataTable(['."
          ['".__("Date", 'ga-dash')."', '".$title."'],"
		  .$ga_dash_statsdata.
		"  
        ]);

        var options = {
		  legend: {position: 'none'},	
		  pointSize: 3,".$css."
          title: '".$title."',
		  chartArea: {width: '85%'},
          hAxis: { title: '".__("Date",'ga-dash')."',  titleTextStyle: {color: '".$colors."'}, showTextEvery: 5}
		};

        var chart = new google.visualization.AreaChart(document.getElementById('ga_dash_statsdata'));
		chart.draw(data, options);
		
      }";
	}  

	if (get_option('ga_dash_map') AND current_user_can(get_option('ga_dash_access_back'))){
		$ga_dash_visits_country=ga_dash_visits_country($service, $projectId, $from, $to);
		if ($ga_dash_visits_country){

		 $code.='
			google.load("visualization", "1", {packages:["geochart"]})
			function ga_dash_drawmap() {
			var data = google.visualization.arrayToDataTable(['."
			  ['".__("Country/City",'ga-dash')."', '".__("Visits",'ga-dash')."'],"
			  .$ga_dash_visits_country.
			"  
			]);
			
			var options = {";
			
				$code.="colors: ['light".$colors."', '".$colors."'],";
					
				if (get_option('ga_target_geomap')){
				 $code.="\nregion : '".get_option('ga_target_geomap')."',";
				 $code.="\ndisplayMode : 'markers',"; 
				 $code.="\ndatalessRegionColor : 'EFEFEF'";
				}					
			
			$code.="\n};\nvar chart = new google.visualization.GeoChart(document.getElementById('ga_dash_mapdata'));
			chart.draw(data, options);
			
		  }";
		}
	}
	if (get_option('ga_dash_traffic') AND current_user_can(get_option('ga_dash_access_back'))){
		$ga_dash_traffic_sources=ga_dash_traffic_sources($service, $projectId, $from, $to);
		$ga_dash_new_return=ga_dash_new_return($service, $projectId, $from, $to);
		if ($ga_dash_traffic_sources AND $ga_dash_new_return){
		 $code.='
			google.load("visualization", "1", {packages:["corechart"]})
			function ga_dash_drawtraffic() {
			var data = google.visualization.arrayToDataTable(['."
			  ['".__("Source",'ga-dash')."', '".__("Visits",'ga-dash')."'],"
			  .$ga_dash_traffic_sources.
			'  
			]);

			var datanvr = google.visualization.arrayToDataTable(['."
			  ['".__("Type",'ga-dash')."', '".__("Visits",'ga-dash')."'],"
			  .$ga_dash_new_return.
			"  
			]);
			
			var chart = new google.visualization.PieChart(document.getElementById('ga_dash_trafficdata'));
			chart.draw(data, {
				is3D: true,
				tooltipText: 'percentage',
				legend: 'none',
				title: '".__("Traffic Sources",'ga-dash')."'
			});
			
			var chart1 = new google.visualization.PieChart(document.getElementById('ga_dash_nvrdata'));
			chart1.draw(datanvr,  {
				is3D: true,
				tooltipText: 'percentage',
				legend: 'none',
				title: '".__("New vs. Returning",'ga-dash')."'
			});
			
		  }";
		}
	}	
	if (get_option('ga_dash_pgd') AND current_user_can(get_option('ga_dash_access_back'))){
		$ga_dash_top_pages=ga_dash_top_pages($service, $projectId, $from, $to);
		if ($ga_dash_top_pages){
		 $code.='
			google.load("visualization", "1", {packages:["table"]})
			function ga_dash_drawpgd() {
			var data = google.visualization.arrayToDataTable(['."
			  ['".__("Top Pages",'ga-dash')."', '".__("Visits",'ga-dash')."'],"
			  .$ga_dash_top_pages.
			"  
			]);
			
			var options = {
				page: 'enable',
				pageSize: 6,
				width: '100%'
			};        
			
			var chart = new google.visualization.Table(document.getElementById('ga_dash_pgddata'));
			chart.draw(data, options);
			
		  }";
		}
	}
	if (get_option('ga_dash_rd') AND current_user_can(get_option('ga_dash_access_back'))){
		$ga_dash_top_referrers=ga_dash_top_referrers($service, $projectId, $from, $to);
		if ($ga_dash_top_referrers){
		 $code.='
			google.load("visualization", "1", {packages:["table"]})
			function ga_dash_drawrd() {
			var datar = google.visualization.arrayToDataTable(['."
			  ['".__("Top Referrers",'ga-dash')."', '".__("Visits",'ga-dash')."'],"
			  .$ga_dash_top_referrers.
			"  
			]);
			
			var options = {
				page: 'enable',
				pageSize: 6,
				width: '100%'
			};        
			
			var chart = new google.visualization.Table(document.getElementById('ga_dash_rdata'));
			chart.draw(datar, options);
			
		  }";
		}
	}
	if (get_option('ga_dash_sd') AND current_user_can(get_option('ga_dash_access_back'))){
		$ga_dash_top_searches=ga_dash_top_searches($service, $projectId, $from, $to);
		if ($ga_dash_top_searches){
		 $code.='
			google.load("visualization", "1", {packages:["table"]})
			function ga_dash_drawsd() {
			
			var datas = google.visualization.arrayToDataTable(['."
			  ['".__("Top Searches",'ga-dash')."', '".__("Visits",'ga-dash')."'],"
			  .$ga_dash_top_searches.
			"  
			]);
			
			var options = {
				page: 'enable',
				pageSize: 6,
				width: '100%'
			};        
			
			var chart = new google.visualization.Table(document.getElementById('ga_dash_sdata'));
			chart.draw(datas, options);
			
		  }";
		}
	}
    $code.="</script>";
    $code.="</script>";
	$ga_button_style=get_option('ga_dash_style')=='light'?'button':'gabutton';
	$code.='<div id="ga-dash">
	<center>
		<div id="buttons_div">
			<input class="'.$ga_button_style.'" type="button" value="'.__("Real-Time",'ga-dash').'" onClick="window.location=\'?realtime=realtime&query='.$query.'\'" />		
			<input class="'.$ga_button_style.'" type="button" value="'.__("Today",'ga-dash').'" onClick="window.location=\'?period=today&query='.$query.'\'" />
			<input class="'.$ga_button_style.'" type="button" value="'.__("Yesterday",'ga-dash').'" onClick="window.location=\'?period=yesterday&query='.$query.'\'" />
			<input class="'.$ga_button_style.'" type="button" value="'.__("Last 30 days",'ga-dash').'" onClick="window.location=\'?period=last30days&query='.$query.'\'" />
			<input class="'.$ga_button_style.'" type="button" value="'.__("Last 90 days",'ga-dash').'" onClick="window.location=\'?period=last90days&query='.$query.'\'" />
		
		</div>';
	if ($realtime!="realtime"){		
		$code.='<div id="ga_dash_statsdata"></div>
		<div id="details_div">
			
			<table class="gatable" cellpadding="4">
			<tr>
			<td width="24%">'.__("Visits:",'ga-dash').'</td>
			<td width="12%" class="gavalue"><a href="?query=visits&period='.$period.'" class="gatable">'.$data['rows'][0][1].'</td>
			<td width="24%">'.__("Visitors:",'ga-dash').'</td>
			<td width="12%" class="gavalue"><a href="?query=visitors&period='.$period.'" class="gatable">'.$data['rows'][0][2].'</a></td>
			<td width="24%">'.__("Page Views:",'ga-dash').'</td>
			<td width="12%" class="gavalue"><a href="?query=pageviews&period='.$period.'" class="gatable">'.$data['rows'][0][3].'</a></td>
			</tr>
			<tr>
			<td>'.__("Bounce Rate:",'ga-dash').'</td>
			<td class="gavalue"><a href="?query=visitBounceRate&period='.$period.'" class="gatable">'.round($data['rows'][0][4],2).'%</a></td>
			<td>'.__("Organic Search:",'ga-dash').'</td>
			<td class="gavalue"><a href="?query=organicSearches&period='.$period.'" class="gatable">'.$data['rows'][0][5].'</a></td>
			<td>'.__("Pages per Visit:",'ga-dash').'</td>
			<td class="gavalue"><a href="#" class="gatable">'.(($data['rows'][0][1]) ? round($data['rows'][0][3]/$data['rows'][0][1],2) : '0').'</a></td>
			</tr>
			</table>
					
		</div>';
	}else{
	
		if (get_option('ga_dash_userapi')){	
			$code.="<p style='padding:100px;line-height:2em;'>".__("This is a beta feature and is only available when using my Developer Key! (",'ga-dash').'<a href="http://deconf.com/google-analytics-dashboard-real-time-reports/" target="_blank">'.__("more about this feature", 'ga-dash').'</a>'.__(")", 'ga-dash')."</p>";
		}else{
		
			$code.="<table width='90%' class='realtime'>
						<tr>
							<td class='gadash-tdo-left'>
								<span class='gadash-online' id='gadash-online'>&nbsp;</span>
							</td>
							<td class='gadash-tdo-right' id='gadash-tdo-right'>
								<span class=\"gadash-bigtext\">".__("REFERRAL",'ga-dash').": 0</span><br /><br />
								<span class=\"gadash-bigtext\">".__("ORGANIC",'ga-dash').": 0</span><br /><br />
								<span class=\"gadash-bigtext\">".__("SOCIAL",'ga-dash').": 0</span><br /><br />
							</td>
							<td class='gadash-tdo-rights' id='gadash-tdo-rights'>
								<span class=\"gadash-bigtext\">".__("DIRECT",'ga-dash').": 0</span><br /><br />
								<span class=\"gadash-bigtext\">".__("NEW",'ga-dash').": 0</span><br /><br />
								<span class=\"gadash-bigtext\">".__("RETURNING",'ga-dash').": 0</span><br /><br />							
							</td>
						</tr>
						<tr>
						<td id='gadash-pages' class='gadash-pages' colspan='3'>&nbsp;</td>
						</tr>
					</table>";
			$code.=ga_realtime('AIzaSyBG7LlUoHc29ZeC_dsShVaBEX15SfRl_WY',plugins_url('/realtime/superproxy.php', __FILE__));		
		}			
	}	
	if (get_option('ga_dash_map') AND current_user_can(get_option('ga_dash_access_back'))){
		$code.='<br /><h3>';
		if (get_option('ga_target_geomap')){
			require 'constants.php';
			$code.=__("Visits from ",'ga-dash').$country_codes[get_option('ga_target_geomap')];
		}else{
			$code.=__("Visits by Country",'ga-dash');
		}	
		$code.='</h3>
		<div id="ga_dash_mapdata"></div>';
	}
	
	if (get_option('ga_dash_traffic') AND current_user_can(get_option('ga_dash_access_back'))){
		$code.='<br /><h3>'.__("Traffic Overview",'ga-dash').'</h3>
		<table width="100%"><tr><td width="50%"><div id="ga_dash_trafficdata"></div></td><td width="50%"><div id="ga_dash_nvrdata"></div></td></tr></table>';
	}
	
	$code.='</center>		
	</div>';
	if (get_option('ga_dash_pgd') AND current_user_can(get_option('ga_dash_access_back')))
		$code .= '<div id="ga_dash_pgddata"></div>';
	if (get_option('ga_dash_rd') AND current_user_can(get_option('ga_dash_access_back')))	
		$code .= '<div id="ga_dash_rdata"></div>';
	if (get_option('ga_dash_sd') AND current_user_can(get_option('ga_dash_access_back')))	
		$code .= '<div id="ga_dash_sdata"></div>';
	
	echo $code; 
   

}	
?>