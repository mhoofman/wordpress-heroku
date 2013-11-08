<?php
	
	function ga_dash_classic_tracking(){
		$tracking_events="";
		$ga_root_domain=get_option('ga_root_domain');
		$tracking_0="<script type=\"text/javascript\">
	var _gaq = _gaq || [];";		
		$tracking_2="\n	(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>\n";
		$profiles=get_option('ga_dash_profile_list');
		if (is_array($profiles)){		
			foreach ($profiles as $items) {
				if ((get_option('ga_dash_default_ua')==$items[2])){
					$ga_root_domain=ga_dash_get_main_domain($items[3]);
					update_option('ga_root_domain',$ga_root_domain);
					update_option('ga_default_domain',$items[3]);					
				} 
			}
		}
		switch ( get_option('ga_dash_tracking') ){
			case 2 	: $tracking_push="['_setAccount', '".get_option('ga_dash_default_ua')."'], ['_setDomainName', '".$ga_root_domain."']"; break;
			case 3 : $tracking_push="['_setAccount', '".get_option('ga_dash_default_ua')."'], ['_setDomainName', '".$ga_root_domain."'], ['_setAllowLinker', true]"; break;
			default : $tracking_push="['_setAccount', '".get_option('ga_dash_default_ua')."']"; break;				
		}

		if (get_option('ga_dash_anonim')){
			$tracking_push.=", ['_gat._anonymizeIp']";
		}	
		
		$tracking=$tracking_events.$tracking_0."\n	_gaq.push(".$tracking_push.", ['_trackPageview']);".$tracking_2;	
		
		return $tracking;	

	}

	function ga_dash_universal_tracking(){
		$tracking_events="";
		$ga_root_domain=get_option('ga_root_domain');
		$tracking_0="<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');";		
		$tracking_2="\n</script>\n";
		$profiles=get_option('ga_dash_profile_list');
		if (is_array($profiles)){
			foreach ($profiles as $items) {
					if ((get_option('ga_dash_default_ua')==$items[2])){
						$ga_root_domain=ga_dash_get_main_domain($items[3]);
						update_option('ga_root_domain',$ga_root_domain);
						update_option('ga_default_domain',$items[3]);
					} 
			}
		}
		switch ( get_option('ga_dash_tracking') ){
			case 2 	: $tracking_push="\n	ga('create', '".get_option('ga_dash_default_ua')."', {'cookieDomain': '".$ga_root_domain."'});"; break;
			case 3 : $tracking_push="\n	ga('create', '".get_option('ga_dash_default_ua')."');"; break;
			default : $tracking_push="\n	ga('create', '".get_option('ga_dash_default_ua')."');";
		}

		if (get_option('ga_dash_anonim')){
		
			$tracking_push.="\n	ga('send', 'pageview', {'anonymizeIp': true});";
		
		} else{
			
			$tracking_push.="\n	ga('send', 'pageview');";
			
		}	
		
		$tracking=$tracking_events.$tracking_0.$tracking_push.$tracking_2;	
		
		return $tracking;	

	}

	
	function ga_dash_get_main_domain($subdomain){
		$domain=parse_url($subdomain,PHP_URL_HOST);
		return $domain;
	}
	
	function ga_dash_get_profile_domain($domain){
	
		return str_replace(array("https://","http://"," "),"",$domain);
	
	}
	
	function ga_dash_pretty_error($e){
		return "<center><table><tr><td colspan='2' style='word-break:break-all;'>".$e->getMessage()."<br /><br /></td></tr><tr><td width='50%'><a href='http://wordpress.org/support/plugin/google-analytics-dashboard-for-wp' target='_blank'>".__("Help on Wordpress Forum",'ga-dash')."</a><td width='50%'><a href='http://forum.deconf.com/wordpress-plugins-f182/' target='_blank'>".__("Support on Deconf Forum",'ga-dash')."</a></td></tr></table></center>";	
	}

	function ga_dash_clear_cache(){
		global $wpdb;
		$sqlquery=$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_gadash%%'");
		$sqlquery=$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_gadash%%'");
	}
	
	function ga_dash_safe_get($key) {
		if (array_key_exists($key, $_POST)) {
			return $_POST[$key];
		}
		return false;
	}
	
	function ga_dash_store_token ($token){
		update_option('ga_dash_token', $token);
	}		

	function ga_dash_store_refreshtoken ($refresh_token){
		update_option('ga_dash_refresh_token', $refresh_token);
	}		
	
	function ga_dash_get_refreshtoken (){
		if (get_option('ga_dash_refresh_token')){
			return get_option('ga_dash_refresh_token');
		}
		else{
			return;
		}
	}

	function ga_dash_get_token (){
		if (get_option('ga_dash_token')){
			return get_option('ga_dash_token');
		}
		else{
			return;
		}
	}
	
	function ga_dash_refresh_token ($client){
		$transient = get_transient("ga_dash_token_expire");
		if ( empty( $transient ) ){
			
			if (!ga_dash_get_refreshtoken()){
				$google_token = json_decode(ga_dash_get_token());
				ga_dash_store_refreshtoken ($google_token->refresh_token);
				$client->refreshToken($google_token->refresh_token);
			}else{
				$client->refreshToken(ga_dash_get_refreshtoken());
			}
			
			$token=$client->getAccessToken();
			$google_token = json_decode($token);
			set_transient( "ga_dash_token_refresh", $token, $google_token->expires_in);
			ga_dash_store_token($token);
			return $token;
		}else{
			return $token;
		}	
	}
	
	function ga_dash_reset_token (){
		update_option('ga_dash_token', "");
		update_option('ga_dash_tableid', "");
		update_option('ga_dash_tableid_jail', "");
		update_option('ga_dash_profile_list', "");
		update_option('ga_dash_access', ""); 		
	}

// Get Top Pages
	function ga_dash_top_pages($service, $projectId, $from, $to){

		$metrics = 'ga:pageviews'; 
		$dimensions = 'ga:pageTitle';
		try{
			$serial='gadash_qr4'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions, 'sort' => '-ga:pageviews', 'max-results' => '24', 'filters' => 'ga:pagePath!=/'));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;	
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}	
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		$i=0;
		while (isset($data['rows'][$i][0])){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
			$i++;
		}

		return rtrim($ga_dash_data,',');
	}
	
// Get Top referrers
	function ga_dash_top_referrers($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:source,ga:medium';
		try{
			$serial='gadash_qr5'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions, 'sort' => '-ga:visits', 'max-results' => '24', 'filters' => 'ga:medium==referral'));	
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		$i=0;
		while (isset($data['rows'][$i][0])){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][2]."],";
			$i++;
		}

		return rtrim($ga_dash_data,',');
	}

// Get Top searches
	function ga_dash_top_searches($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:keyword';
		try{
			$serial='gadash_qr6'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions, 'sort' => '-ga:visits', 'max-results' => '24', 'filters' => 'ga:keyword!=(not provided);ga:keyword!=(not set)'));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}	
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		$i=0;
		while (isset($data['rows'][$i][0])){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
			$i++;
		}

		return rtrim($ga_dash_data,',');
	}
// Get Visits by Country
	function ga_dash_visits_country($service, $projectId, $from, $to){

		$metrics = 'ga:visits';
		$options="";
		if (get_option('ga_target_geomap')){
			$dimensions = 'ga:city';
			require 'constants.php';
			$filters = 'ga:country=='.($country_codes[get_option('ga_target_geomap')]);
		}else{	
			$dimensions = 'ga:country';
			$filters = "";
		}	
		try{
			$serial='gadash_qr7'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
			$transient = get_transient($serial);
			if ( empty( $transient ) ){
				if ($filters)
					$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions, 'filters' => $filters, 'sort' => '-ga:visits', 'max-results' => get_option('ga_target_number')));
				else	
					$data = $service->data_ga->get('ga:'.$projectId, $from, $to, $metrics, array('dimensions' => $dimensions));
				set_transient( $serial, $data, get_option('ga_dash_cachetime') );
			}else{
				$data = $transient;		
			}			
		} catch (Google_ServiceException $e) {
			echo ga_dash_pretty_error($e);
			return;
		}
		if (!isset($data['rows'])){
			return 0;
		}

		$ga_dash_data="";
		$i=0;
		while (isset($data['rows'][$i][1])){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
			$i++;	
		}
				
		return rtrim($ga_dash_data,',');

	}	
// Get Traffic Sources
	function ga_dash_traffic_sources($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:medium';
		try{
			$serial='gadash_qr8'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
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
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		for ($i=0;$i<$data['totalResults'];$i++){
			$ga_dash_data.="['".str_replace("(none)","direct",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
		}

		return rtrim($ga_dash_data,',');

	}

// Get New vs. Returning
	function ga_dash_new_return($service, $projectId, $from, $to){

		$metrics = 'ga:visits'; 
		$dimensions = 'ga:visitorType';
		try{
			$serial='gadash_qr9'.str_replace(array('ga:',',','-',date('Y')),"",$projectId.$from.$to);
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
		if (!isset($data['rows'])){
			return 0;
		}
		
		$ga_dash_data="";
		for ($i=0;$i<$data['totalResults'];$i++){
			$ga_dash_data.="['".str_replace(array("'","\\")," ",$data['rows'][$i][0])."',".$data['rows'][$i][1]."],";
		}

		return rtrim($ga_dash_data,',');

	}

	function ga_maintain_compatibility(){
		if(!get_option('ga_dash_cachetime') OR get_option('ga_dash_cachetime')==10){
			update_option('ga_dash_cachetime', "3600");	
		}
		if(!get_option('ga_dash_access')){
			update_option('ga_dash_access', "manage_options");	
		}

		if(!get_option('ga_dash_style')){
			update_option('ga_dash_style', "blue");	
		}
		if (!get_option('ga_event_downloads')){
			update_option('ga_event_downloads', "zip|mp3|mpeg|pdf|doc*|ppt*|xls*|jpeg|png|gif|tiff");
		}
		if (!get_option('ga_dash_access_front')){
			update_option('ga_dash_access_front', get_option('ga_dash_access'));
		}
		if (!get_option('ga_dash_access_back')){
			update_option('ga_dash_access_back', get_option('ga_dash_access'));
		}
		if (!get_option('ga_target_number') AND get_option('ga_target_geomap')){
			update_option('ga_target_number', "10");
		}
		if (!get_option('ga_realtime_pages')){
			update_option('ga_realtime_pages', "10");
		}
		
	}
	function ga_realtime($devkey,$path){
		$token=json_decode(get_option('ga_dash_token'));
		$url=$path."?access_token=".($token->access_token)."&key=$devkey";

		$code='

		<script type="text/javascript">

		var focusFlag = 1;

		jQuery(document).ready(function(){
			jQuery(window).bind("focus",function(event){
				focusFlag = 1;
			}).bind("blur", function(event){
				focusFlag = 0;
			});
		});
		
		jQuery(function() {
			jQuery( document ).tooltip();
		});
		
		function onlyUniqueValues(value, index, self) { 
			return self.indexOf(value) === index;
		 }

		function countvisits(data, searchvalue) { 
			var count = 0;
			for ( var i = 0; i < data["rows"].length; i = i + 1 ) {
				if (jQuery.inArray(searchvalue, data["rows"][ i ])>-1){
					count += parseInt(data["rows"][ i ][6]);
				}
 			}
			return count;
		 }
		
		function gadash_generatetooltip(data) {
			var count = 0;
			var table = "";
			for ( var i = 0; i < data.length; i = i + 1 ) {
					count += parseInt(data[ i ].count);
					table += "<tr><td class=\'gadash-pgdetailsl\'>"+data[i].value+"</td><td class=\'gadash-pgdetailsr\'>"+data[ i ].count+"</td></tr>";
			};
			if (count){
				return("<table>"+table+"</table>");
			}else{
				return("");
			}	
		}
		
		function gadash_pagedetails(data, searchvalue) { 
			var newdata = [];
			for ( var i = 0; i < data["rows"].length; i = i + 1 ){
				var sant=1;
				for ( var j = 0; j < newdata.length; j = j + 1 ){
					if (data["rows"][i][0]+data["rows"][i][1]+data["rows"][i][2]+data["rows"][i][3]==newdata[j][0]+newdata[j][1]+newdata[j][2]+newdata[j][3]){
						newdata[j][6] = parseInt(newdata[j][6]) + parseInt(data["rows"][i][6]);
						sant = 0;
					}
				}
				if (sant){
					newdata.push(data["rows"][i].slice());
				}
			}

			var countrfr = 0;
			var countkwd = 0;
			var countdrt = 0;
			var countscl = 0;
			var tablerfr = "";
			var tablekwd = "";
			var tablescl = "";
			var tabledrt = "";
			for ( var i = 0; i < newdata.length; i = i + 1 ) {
				if (newdata[i][0] == searchvalue){
					var pagetitle = newdata[i][5];
					switch (newdata[i][3]){
						case "REFERRAL": 	countrfr += parseInt(newdata[ i ][6]);
											tablerfr +=	"<tr><td class=\'gadash-pgdetailsl\'>"+newdata[i][1]+"</td><td class=\'gadash-pgdetailsr\'>"+newdata[ i ][6]+"</td></tr>";
											break;
						case "ORGANIC": 	countkwd += parseInt(newdata[ i ][6]);
											tablekwd +=	"<tr><td class=\'gadash-pgdetailsl\'>"+newdata[i][2]+"</td><td class=\'gadash-pgdetailsr\'>"+newdata[ i ][6]+"</td></tr>";
											break;
						case "SOCIAL": 		countscl += parseInt(newdata[ i ][6]);
											tablescl +=	"<tr><td class=\'gadash-pgdetailsl\'>"+newdata[i][1]+"</td><td class=\'gadash-pgdetailsr\'>"+newdata[ i ][6]+"</td></tr>";
											break;											
						case "DIRECT": 		countdrt += parseInt(newdata[ i ][6]);
											break;											
					};
				};
 			};
			if (countrfr){
				tablerfr = "<table><tr><td>'.__("REFERRALS",'ga-dash').' ("+countrfr+")</td></tr>"+tablerfr+"</table><br />";
			}
			if (countkwd){	
				tablekwd = "<table><tr><td>'.__("KEYWORDS",'ga-dash').' ("+countkwd+")</td></tr>"+tablekwd+"</table><br />";
			}
			if (countscl){
				tablescl = "<table><tr><td>'.__("SOCIAL",'ga-dash').' ("+countscl+")</td></tr>"+tablescl+"</table><br />";			
			}	
			if (countdrt){
				tabledrt = "<table><tr><td>'.__("DIRECT",'ga-dash').' ("+countdrt+")</td></tr></table><br />";				
			}	
			return ("<p><center><strong>"+pagetitle+"</strong></center></p>"+tablerfr+tablekwd+tablescl+tabledrt);
		 }		 
		 
		 function online_refresh(){
			if (focusFlag){
			jQuery.getJSON("'.$url.'", function(data){
				if (data["totalsForAllResults"]["ga:activeVisitors"]!==document.getElementById("gadash-online").innerHTML){
					jQuery("#gadash-online").fadeOut("slow");
					jQuery("#gadash-online").fadeOut(500);
					jQuery("#gadash-online").fadeOut("slow", function() {
						if ((parseInt(data["totalsForAllResults"]["ga:activeVisitors"]))<(parseInt(document.getElementById("gadash-online").innerHTML))){
							jQuery("#gadash-online").css({\'background-color\' : \'#FFE8E8\'});
						}else{
							jQuery("#gadash-online").css({\'background-color\' : \'#E0FFEC\'});
						}	
						document.getElementById("gadash-online").innerHTML = data["totalsForAllResults"]["ga:activeVisitors"];
					});
					jQuery("#gadash-online").fadeIn("slow");
					jQuery("#gadash-online").fadeIn(500);
					jQuery("#gadash-online").fadeIn("slow", function() {
						jQuery("#gadash-online").css({\'background-color\' : \'#F8F8F8\'});
					});
				};
				
				var pagepath = [];
				var referrals = [];
				var keywords = [];
				var social = [];
				var visittype = [];
				for ( var i = 0; i < data["rows"].length; i = i + 1 ) {
					pagepath.push( data["rows"][ i ][0] );
					if (data["rows"][i][3]=="REFERRAL"){
						referrals.push( data["rows"][ i ][1] );
					}
					if (data["rows"][i][3]=="ORGANIC"){					
						keywords.push( data["rows"][ i ][2] );
					}
					if (data["rows"][i][3]=="SOCIAL"){					
						social.push( data["rows"][ i ][1] );
					}						
					visittype.push( data["rows"][ i ][3] );
 				}

				var upagepath = pagepath.filter(onlyUniqueValues);
				var upagepathstats = [];
				for ( var i = 0; i < upagepath.length; i = i + 1 ) {
					upagepathstats[i]={"pagepath":upagepath[i],"count":countvisits(data,upagepath[i])};
 				}
				upagepathstats.sort( function(a,b){ return b.count - a.count } );				
				
				var pgstatstable = "";
				for ( var i = 0; i < upagepathstats.length; i = i + 1 ) {
					if (i < '.get_option('ga_realtime_pages').'){
						pgstatstable += "<tr class=\"gadash-pline\"><td class=\"gadash-pleft\"><a href=\"#\" title=\""+gadash_pagedetails(data, upagepathstats[i].pagepath)+"\">"+upagepathstats[i].pagepath.substring(0,70)+"</a></td><td class=\"gadash-pright\">"+upagepathstats[i].count+"</td></tr>";
					}	
 				}
				document.getElementById("gadash-pages").innerHTML="<br /><table class=\"gadash-pg\">"+pgstatstable+"</table>";
				
				var ureferralsstats = [];
				var ureferrals = referrals.filter(onlyUniqueValues);
				for ( var i = 0; i < ureferrals.length; i = i + 1 ) {
					ureferralsstats[i]={"value":ureferrals[i],"count":countvisits(data,ureferrals[i])};
 				}
				ureferralsstats.sort( function(a,b){ return b.count - a.count } );

				var ukeywordsstats = [];
				var ukeywords = keywords.filter(onlyUniqueValues);
				for ( var i = 0; i < ukeywords.length; i = i + 1 ) {
					ukeywordsstats[i]={"value":ukeywords[i],"count":countvisits(data,ukeywords[i])};
 				}
				ukeywordsstats.sort( function(a,b){ return b.count - a.count } );				

				var usocialstats = [];
				var usocial = social.filter(onlyUniqueValues);
				for ( var i = 0; i < usocial.length; i = i + 1 ) {
					usocialstats[i]={"value":usocial[i],"count":countvisits(data,usocial[i])};
 				}
				usocialstats.sort( function(a,b){ return b.count - a.count } );
				
				var uvisittype = ["REFERRAL","ORGANIC","SOCIAL"];
				document.getElementById("gadash-tdo-right").innerHTML = "<span class=\"gadash-bigtext\"><a href=\"#\" title=\""+gadash_generatetooltip(ureferralsstats)+"\">"+\''.__("REFERRAL",'ga-dash').'\'+"</a>: "+countvisits(data,uvisittype[0])+"</span><br /><br />";
				document.getElementById("gadash-tdo-right").innerHTML += "<span class=\"gadash-bigtext\"><a href=\"#\" title=\""+gadash_generatetooltip(ukeywordsstats)+"\">"+\''.__("ORGANIC",'ga-dash').'\'+"</a>: "+countvisits(data,uvisittype[1])+"</span><br /><br />";
				document.getElementById("gadash-tdo-right").innerHTML += "<span class=\"gadash-bigtext\"><a href=\"#\" title=\""+gadash_generatetooltip(usocialstats)+"\">"+\''.__("SOCIAL",'ga-dash').'\'+"</a>: "+countvisits(data,uvisittype[2])+"</span><br /><br />";

				var uvisitortype = ["DIRECT","NEW","RETURNING"];
				document.getElementById("gadash-tdo-rights").innerHTML = "<span class=\"gadash-bigtext\">"+\''.__("DIRECT",'ga-dash').'\'+": "+countvisits(data,uvisitortype[0])+"</span><br /><br />";
				document.getElementById("gadash-tdo-rights").innerHTML += "<span class=\"gadash-bigtext\">"+\''.__("NEW",'ga-dash').'\'+": "+countvisits(data,uvisitortype[1])+"</span><br /><br />";
				document.getElementById("gadash-tdo-rights").innerHTML += "<span class=\"gadash-bigtext\">"+\''.__("RETURNING",'ga-dash').'\'+": "+countvisits(data,uvisitortype[2])+"</span><br /><br />";
				
				if (!data["totalsForAllResults"]["ga:activeVisitors"]){
					location.reload();
				}

			});
	   };
	   };	   
	   online_refresh();
	   setInterval(online_refresh, 20000);
	   </script>';
	return $code;
}	
?>