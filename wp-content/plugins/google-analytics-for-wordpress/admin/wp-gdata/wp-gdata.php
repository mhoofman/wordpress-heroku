<?php
/**
 * WP_GData - WordPress Google Data API Library
 *
 * @author Pete Mall
 */

// Load the OAuth library.
if ( ! class_exists( 'Yoast_OAuthConsumer' ) )
	require( 'OAuth.php' );

class WP_GData {
	/* Contains the last HTTP status code returned. */
	public $http_code;

	const request_token_url = 'https://www.google.com/accounts/OAuthGetRequestToken';
	const authorize_url     = 'https://www.google.com/accounts/OAuthAuthorizeToken';
	const access_token_url  = 'https://www.google.com/accounts/OAuthGetAccessToken';

	function __construct( $parameters = array(), $oauth_token = null, $oauth_token_secret = null ) {
		$this->parameters = $parameters;
		$this->signature_method = new Yoast_OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new Yoast_OAuthConsumer( 'anonymous', 'anonymous' );

		if ( !empty( $oauth_token ) && !empty( $oauth_token_secret ) )
			$this->token = new Yoast_OAuthConsumer( $oauth_token, $oauth_token_secret );
		else
			$this->token = null;
	}

	function get_request_token( $oauth_callback = null ) {
		$parameters = $this->parameters;
		if ( !empty( $oauth_callback ) )
			$parameters['oauth_callback'] = $oauth_callback;

		$request = $this->oauth_request( self::request_token_url, 'GET', $parameters );
		$token = Yoast_OAuthUtil::parse_parameters( wp_remote_retrieve_body( $request ) );
		$this->token = new Yoast_OAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );
		return $token;
	}

	/**
	 * Format and sign an OAuth / API request
	 */
	private function oauth_request( $url, $method, $parameters ) {
		$request = Yoast_OAuthRequest::from_consumer_and_token( $this->consumer, $this->token, $method, $url, $parameters );
		$request->sign_request( $this->signature_method, $this->consumer, $this->token );

		if ( 'GET' == $method )
			return wp_remote_get( $request->to_url() );
		else
			return wp_remote_post( $request->to_url(), $request->to_postdata() );
	  }

	function get_authorize_url( $token ) {
		if ( is_array( $token ) )
			$token = $token['oauth_token'];

		return self::authorize_url . "?oauth_token={$token}";
	}

	/**
	 * Exchange request token and secret for an access token and
	 * secret, to sign API calls.
	 *
	 * @returns array( 'oauth_token' => 'the-access-token',
	 *                 'oauth_token_secret' => 'the-access-secret' )
	 */
	function get_access_token( $oauth_verifier = '' ) {
		$parameters = array();
		if ( !empty( $oauth_verifier ) )
			$parameters['oauth_verifier'] = $oauth_verifier;

		$request = $this->oauth_request( self::access_token_url, 'GET', $parameters );
		$token = Yoast_OAuthUtil::parse_parameters( wp_remote_retrieve_body( $request ) );
		$this->token = new Yoast_OAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );
		return $token;
	}

	/**
 	 * GET wrapper for oAuthRequest.
 	 */
	public function get( $url, $parameters = array() ) {
		return $this->oauth_request( $url, 'GET', $parameters );
	}

	/**
 	 * POST wrapper for oAuthRequest.
 	 */
	public function post( $url, $parameters = array() ) {
		$defaults = array(
			'method'  => 'POST',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'body' => array(),
			'headers' => array(),
			'cookies' => array()
		);
		$parameters = array_merge( $defaults, $parameters );

		return $this->oauth_request( $url, 'POST', $parameters );
	}	
}