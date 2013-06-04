<?php
// shortcode handler for [bandcamp], which inserts a bandcamp.com
// music player (embedded flash object)
//
// [bandcamp album=119385304]
// [bandcamp album=3462839126  bgcol=FFFFFF linkcol=4285BB size=venti]
// [bandcamp track=2446959313]
//
function shortcode_handler_bandcamp( $atts ) {
	// there are no default values, but specify here anyway
	// to explicitly list supported atts
	$attributes = shortcode_atts( array(
		'album'			=> null,		// integer album id
		'track'			=> null,		// integer track id
		'size'			=> 'venti',		// one of the supported sizes
		'bgcol'			=> 'FFFFFF',	// hex, no '#' prefix
		'linkcol'		=> null,		// hex, no '#' prefix
		'layout'		=> null,		// encoded layout url
		'width'			=> null,		// integer
		'height'		=> null,		// integer
		'notracklist'	=> null,		// may be string "true"
		'package'		=> null			// integer package id
	), $atts );

	$sizes = array(
		'venti'			=> array( 'width' => 400, 'height' => 100 ),
		'grande'		=> array( 'width' => 300, 'height' => 100 ),
		'grande2'		=> array( 'width' => 300, 'height' => 355 ),
		'grande3'		=> array( 'width' => 300, 'height' => 415 ),
		'tall_album'	=> array( 'width' => 150, 'height' => 295 ),
		'tall_track'	=> array( 'width' => 150, 'height' => 270 ),
		'tall2'			=> array( 'width' => 150, 'height' => 450 ),
		'short'			=> array( 'width' => 46, 'height' => 23 ),
		'biggie'		=> array( 'width' => 350, 'height' => 600 ),
		'minimal'		=> array( 'width' => 350, 'height' => 350 ),
		'artonly'		=> array( 'width' => 350, 'height' => 350 )
	);

	$sizekey = $attributes['size'];

	// Build iframe url.  Args are appended as
	// extra path segments for historical reasons having to
	// do with an IE-only flash bug which required this URL
	// to contain no querystring

	$url = "http://bandcamp.com/EmbeddedPlayer/v=2/";
	if ( isset( $attributes['track'] ) ) {
		$track = (int) $attributes['track'];
		$url .= "track={$track}";

		if ( $sizekey == 'tall' ) {
			$sizekey .= '_track';
		}
	} elseif ( isset( $attributes['album'] ) ) {
		$album = (int) $attributes['album'];
		$url .= "album={$album}";
		$type = 'album';

		if ( $sizekey == 'tall' ) {
			$sizekey .= '_album';
		}
	} else {
		return "[bandcamp: shortcode must include track or album id]";
	}

	// if size specified that we don't recognize, fall back on venti
	if ( empty( $sizes[$sizekey] ) ) {
		$sizekey = 'venti';
		$attributes['size'] = 'venti';
	}

	$height = absint( $attributes['height'] ); //|| $sizes[$sizekey]['height'];
	$width = absint( $attributes['width'] ); //|| $sizes[$sizekey]['width'];

	if ( $height ) {
		$url .= "/height={$height}";
	} else {
		$height = $sizes[$sizekey]['height'];
	}

	if ( $width ) {
		$url .= "/width={$width}";
	} else {
		$width = $sizes[$sizekey]['width'];
	}

	if ( isset( $attributes['layout'] ) ) {
		$url .= "/layout={$attributes['layout']}";
	} elseif ( isset( $attributes['size'] ) && preg_match( "|[a-zA-Z]+|", $attributes['size'] ) ) {
		$url .= "/size={$attributes['size']}";
	}

	if ( isset( $attributes['bgcol'] ) && preg_match( "|[0-9A-Fa-f]+|", $attributes['bgcol'] ) ) {
		$url .= "/bgcol={$attributes['bgcol']}";
	}

	if ( isset( $attributes['linkcol'] ) && preg_match( "|[0-9A-Fa-f]+|", $attributes['linkcol'] ) ) {
		$url .= "/linkcol={$attributes['linkcol']}";
	}

	if ( isset( $attributes['package'] ) && preg_match( "|[0-9]+|", $attributes['package'] ) ) {
		$url .= "/package={$attributes['package']}";
	}

	if ( $attributes['notracklist'] == "true" ) {
		$url .= "/notracklist=true";
	}

	$url .= '/';

	return "<iframe width='" . esc_attr( $width ) . "' height='" . esc_attr( $height ) . "' style='position: relative; display: block; width: " . esc_attr( $width ) . "px; height: " . esc_attr( $height ) . "px;' src='" . esc_url( $url ) . "' allowtransparency='true' frameborder='0'></iframe>";
}

add_shortcode( 'bandcamp', 'shortcode_handler_bandcamp' );
