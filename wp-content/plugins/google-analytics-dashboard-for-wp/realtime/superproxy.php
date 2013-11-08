<?php
define('WP_USE_THEMES', false);

function ga_dash_search_wpconfig($dirrectory){
	global $ga_dash_wproot;
	foreach(glob($dirrectory."/*") as $file){
		if (basename($file) == 'wp-config.php' ){
			$ga_dash_wproot = str_replace("\\", "/", dirname($file));
			return true;
		}
		if (is_dir($file)){
			$newdir = dirname(dirname($file));
		}
	}
	if (isset($newdir) && $newdir != $dirrectory){
		if (ga_dash_search_wpconfig($newdir)){
			return false;
		}	
	}
	return false;
}

if (!isset($table_prefix)){
	global $ga_dash_wproot;
	ga_dash_search_wpconfig(dirname(dirname(__FILE__)));
	include_once $ga_dash_wproot."/wp-load.php";
}

$token=json_decode(get_option('ga_dash_token'));
if ($_REQUEST['access_token']==$token->access_token AND isset($_REQUEST['key'])){
	$data = get_transient("ga_dash_realtimecache_".get_option('ga_dash_tableid'));
	if ( empty( $data ) ){
		$devkey=$_REQUEST['key'];
		$access_token=$_REQUEST['access_token'];
		$url="https://www.googleapis.com/analytics/v3/data/realtime?ids=ga:".get_option('ga_dash_tableid')."&metrics=ga:activeVisitors&dimensions=ga:pagePath,ga:source,ga:keyword,ga:trafficType,ga:visitorType,ga:pageTitle&access_token=".($access_token)."&key=$devkey";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		$data = curl_exec($ch);
		echo curl_error ( $ch );
		curl_close($ch);
		print_r($data);
		set_transient("ga_dash_realtimecache",$data,20);
	}else{
		print_r($data);
	}
	
} else print_r("Invalid Login");	
?>