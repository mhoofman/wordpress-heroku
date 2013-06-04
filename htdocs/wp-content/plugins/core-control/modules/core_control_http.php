<?php
/*
Plugin Name: HTTP Access Module
Version: 1.0
Description: Core Control HTTP module, This allows you to Enable/Disable the different HTTP Access methods which WordPress 2.7+ supports
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/

class core_control_http {

	function __construct() {
		add_action('core_control-http', array(&$this, 'the_page'));
		
		$this->settings = array('curl' => array('enabled' => true), 'streams' => array('enabled' => true), 'fopen' => array('enabled' => true), 'fsockopen' => array('enabled' => true), 'exthttp' => array('enabled' => true));

		$this->settings = get_option('core_control-http', $this->settings);

		foreach ( $this->settings as $transport => $opts )
			$this->settings[$transport]['filter'] = "use_{$transport}_transport";
		$this->settings['exthttp']['filter'] = 'use_http_extension_transport';

		add_action('admin_post_core_control-http', array(&$this, 'handle_posts'));

		foreach ( $this->settings as $transport => $options )
			if ( $options['enabled'] === false )
				add_filter($options['filter'], '__return_false' );
	}

	function has_page() {
		return true;
	}

	function menu() {
		return array('http', 'External HTTP Access');
	}
	
	function handle_posts() {
		$option =& $this->settings;
		
		$module_action = isset($_REQUEST['module_action']) ? $_REQUEST['module_action'] : '';
		$transport = isset($_REQUEST['transport']) ? $_REQUEST['transport'] : '';
		switch ( $module_action ) {
			case 'disabletransport':
				$option[ $transport ]['enabled'] = false;
				break;
			case 'enabletransport':
				$option[ $transport ]['enabled'] = true;
				break;
		}
		
		update_option('core_control-http', $option);
		wp_redirect(admin_url('tools.php?page=core-control&module=http'));
	}
	
	function the_page() {
		echo '<h3>Manage Transports</h3>';
		echo '<div style="margin-left: 2em; margin-bottom: 3em;">';
			
		echo '<table class="widefat">';
		echo '<col style="text-align: left" width="20%" />
			  <col width="10%" />
			  <col style="text-align:left" />
			  <thead>
			  <tr>
			  	<th>Transport</th>
				<th>Status</th>
				<th>Actions</th>
				<th></th>
			  </tr>
			  </thead>
			  <tbody>
			  ';
		
		foreach ( array('exthttp' => 'PHP HTTP Extension', 'curl' => 'cURL', 'streams' => 'PHP Streams', 'fopen' => 'PHP fopen()', 'fsockopen' => 'PHP fsockopen()' ) as $transport => $text ) {
			$class = "WP_Http_$transport";
			if ( ! class_exists($class) )
				continue;
			$class = new $class;
			
			//Before we test, we need to remove any filters we've loaded.
			$filtered = has_filter($this->settings[$transport]['filter'], '__return_false' );
			if ( $filtered )
				remove_filter($this->settings[$transport]['filter'], '__return_false' );
			$useable = $class->test();
			if ( $filtered )
				add_filter($this->settings[$transport]['filter'], '__return_false' );
			$disabled = $this->settings[$transport]['enabled'] === false;
			$colour = $useable ? '#e7f7d3' : '#ee4546';
			if ( $useable && $disabled )
				$colour = '#e7804c';
			
			$status = $disabled ? 'Disabled' : ($useable ? 'Available' : 'Not Available');
			
			$extra = '';

			echo '<tr style="background-color: ' . $colour . ';">';
				echo '<th style="text-shadow: none !important;">' . $text . '</th>';
				echo '<td>' . $status . '</td>';
				echo '<td>';
				if ( $useable ) {
					$actions = array();
					if ( $disabled )
						$actions[] = '<a href="admin-post.php?action=core_control-http&module_action=enabletransport&transport=' . $transport . '">Enable Transport</a>';
					else
						$actions[] = '<a href="admin-post.php?action=core_control-http&module_action=disabletransport&transport=' . $transport . '">Disable Transport</a>';
					$actions[] = '<a href="' . add_query_arg(array('module_action' => 'testtransport', 'transport' => $transport)) . '">Test Transport</a>';
					
					echo implode(' | ', $actions);
				}
				echo '</td>';
				echo '<td>' . $extra . '</td>';
			echo '</tr>';
			//Do the testing.
			if ( isset($_GET['module_action']) && 'testtransport' == $_GET['module_action'] && $transport == $_GET['transport'] ) {
				echo '<tr><td colspan="4" style="background-color: #fffeeb;">';
					echo "<p>Please wait...</p>";
					$url = 'http://tools.dd32.id.au/wordpress/core-control.php';
					$args = array(
						'method' => 'GET',
						'timeout' => max(10, apply_filters( 'http_request_timeout', 5)),
						'redirection' => apply_filters( 'http_request_redirection_count', 5),
						'_redirection' => apply_filters( 'http_request_redirection_count', 5),
						'httpversion' => apply_filters( 'http_request_version', '1.0'),
						'user-agent' => apply_filters( 'http_headers_useragent', 'WordPress/' . $GLOBALS['wp_version'] . '; ' . get_bloginfo( 'url' )  ),
						'blocking' => true,
						'headers' => array(),
						'cookies' => array(),
						'body' => null,
						'compress' => false,
						'decompress' => true,
						'sslverify' => true,
						'filename' => '',
						'stream' => false,
					);
					$result = $class->request($url, $args);
					if ( is_wp_error($result) ) {
						echo '<p><strong>An Error has occured:</strong> ' . $result->get_error_message() . '</p>';
					} elseif ( '1563' === $result['body'] ) { //1563 is just a random number which was chosen to indicate successful retrieval
						printf('<p>Successfully retrieved &amp; verified document from %s</p>', $url);
					} else {
						printf('<p>Whilst an error was not returned, The server returned an unexpected result: <em>%s</em>, HTTP result: %s %s', htmlentities($result['body']), $result['response']['code'], $result['response']['message']);
					}
				echo '</td></tr>';
			}
		}
		echo '</tbody></table>';
		
		echo '<h3>HTTP Related Constants</h3>';
		$constants = array('WP_HTTP_BLOCK_EXTERNAL', 'WP_ACCESSIBLE_HOSTS', 'WP_PROXY_HOST', 'WP_PROXY_PORT', 'WP_PROXY_USERNAME', 'WP_PROXY_PASSWORD', 'WP_PROXY_BYPASS_HOSTS');
		
		echo '<table class="widefat">
			<col style="text-align: left" width="20%" />
			  <col />
		';
		echo '<thead>
			  <tr>
			  	<th>Constant</th>
				<th>Value</th>
			  </tr>
			  </thead>
			  <tbody>
			  ';
		foreach ( $constants as $constant ) {
			if ( defined($constant) )
				echo '<tr style="background-color: #e7804c">';
			else
				echo '<tr style="background-color: #e7f7d3">';
			echo "<td>$constant</td>";
			if ( defined($constant) )
				echo '<td>' . var_export(constant($constant), true) . '</td>';
			else
				echo '<td><em>Undefined</em></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

		echo '<h3>HTTP Related Filters</h3>';
		echo '<p><strong>Note:</strong> Some of these can be affected by plugins, and may cause unexpected side effects. The use_* set of filters is affected by disabling transports above for example.</p>';
		$filters = array(
						'http_request_timeout' => 5,
						'http_request_redirection_count' => 5,
						'http_request_version' => '1.0',
						'http_headers_useragent' => 'WordPress/' . $GLOBALS['wp_version'] . '; ' . get_bloginfo( 'url' ),
						'block_local_requests' => false,
						
						'use_fsockopen_transport' => true,
						'use_streams_transport' => true,
						'use_curl_transport' => true,
						
						'https_local_ssl_verify' => true,
						'https_ssl_verify' => true,
						 );
		
		echo '<table class="widefat">
			<col style="text-align: left" width="20%" />
			<col width="20%" />
			<col />
		';
		echo '<thead>
			  <tr>
			  	<th>Filter</th>
				<th>Default Value</th>
				<th>Current Value</th>
			  </tr>
			  </thead>
			  <tbody>
			  ';
		foreach ( $filters as $filter => $default ) {
			$current = apply_filters($filter, $default);
			if ( $current != $default )
				echo '<tr style="background-color: #e7804c">';
			else
				echo '<tr style="background-color: #e7f7d3">';
			echo "<td>$filter</td>";
			echo '<td>' . var_export($default, true) . '</td>';
			echo '<td>';
				var_export( $current );
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

		echo '</div>';
	}
}
?>