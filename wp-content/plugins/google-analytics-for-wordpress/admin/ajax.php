<?php

function yoast_ga_store_tracking_response() {
	if ( !wp_verify_nonce( $_POST['nonce'], 'wpga_activate_tracking' ) )
		die();

	$options = get_option( 'Yoast_Google_Analytics' );

	$options['tracking_popup'] = 'done';

	if ( $_POST['allow_tracking'] == 'yes' )
		$options['yoast_tracking'] = true;
	else
		$options['yoast_tracking'] = false;

	update_option( 'Yoast_Google_Analytics', $options );
}

add_action( 'wp_ajax_wpga_tracking_data', 'yoast_ga_store_tracking_response' );
