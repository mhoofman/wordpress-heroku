<?php

function yoast_ga_get_domain( $uri ) {
	$hostPattern     = "/^(http:\/\/)?([^\/]+)/i";
	$domainPatternUS = "/[^\.\/]+\.[^\.\/]+$/";
	$domainPatternUK = "/[^\.\/]+\.[^\.\/]+\.[^\.\/]+$/";

	preg_match( $hostPattern, $uri, $matches );
	$host = $matches[2];
	if ( preg_match( "/.*\..*\..*\..*$/", $host ) )
		preg_match( $domainPatternUK, $host, $matches );
	else
		preg_match( $domainPatternUS, $host, $matches );

	if ( isset( $matches[0] ) ) {
		return array( "domain" => $matches[0], "host" => $host );
	} else {
		return false;
	}
}