<?php
/*
Plugin Name: Filesystem Module
Version: 1.1
Description: Core Control Filesystem module, This allows you to Enable/Disable the different Filesystem access methods which WordPress supports for upgrades
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/
class core_control_filesystem {

	function __construct() {
		add_action('core_control-fs', array(&$this, 'the_page'));
		
		$this->settings = array('direct' => array('enabled' => true, 'forced' => false), 'ssh' => array('enabled' => true, 'forced' => false), 'ftpext' => array('enabled' => true, 'forced' => false), 'ftpsockets' => array('enabled' => true, 'forced' => false));

		$this->settings = get_option('core_control-fs', $this->settings);

		add_action('admin_post_core_control-fs', array(&$this, 'handle_posts'));

		foreach ( $this->settings as $transport => $options )
			if ( $options['enabled'] === false )
				add_filter('filesystem_method', array(&$this, 'handle_transport'), 20);
	}


	function has_page() {
		return true;
	}
	function menu() {
		return array('fs', 'Filesystem Access');
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
		
		update_option('core_control-fs', $option);
		wp_redirect(admin_url('tools.php?page=core-control&module=fs'));
	}
	
	function handle_transport($transport) {
		if ( isset($this->settings[$transport]['enabled']) && $this->settings[$transport]['enabled'] === false ) {
			foreach ( array( 'direct', 'ssh', 'ftpext', 'ftpsockets' ) as $a_transport ) {
				if ( !isset($this->settings[$a_transport]['enabled']) || $this->settings[$a_transport]['enabled'] === false )
					continue;
				if ( $this->is_available($a_transport) )
					return $a_transport;
			}
			return false;
		}
		return $transport;
	}
	
	function the_page() {
		echo '<h3>Manage Transports</h3>';
		echo '<div style="margin-left: 2em; margin-bottom: 3em;">';
			
		echo '<table class="widefat">';
		echo '<col style="text-align: left" width="20%" />
			  <col width="10%" />
			  <col style="text-align:left" />
			  <col />
			  <thead>
			  <tr>
			  	<th>Transport</th>
				<th>Status</th>
				<th>Actions</th>
				<th></th>
			  </tr>
			  </thead>
			  ';

		$prefered = get_filesystem_method( get_option('ftp_credentials') );

		foreach ( array( 'direct' => 'Direct', 'ssh' => 'SSH2', 'ftpext' => 'PHP FTP Extension', 'ftpsockets' => 'PHP FTP Sockets' ) as $transport => $text ) {

			$useable = $this->is_available($transport);
			$disabled = $this->settings[$transport]['enabled'] == false;
			$colour = $useable ? '#e7f7d3' : '#ee4546';
			if ( $useable && $disabled ) {
				$colour = '#e7804c';
			}
			
			$status = $disabled ? 'Disabled' : ($useable ? 'Available' : 'Not Available');
			
			$extra = '';
			if ( $prefered == $transport )
				$extra .= 'Current Transport<br />';
			
			if ( 'ftpsockets' == $transport )
				$extra .= 'via ' . ( extension_loaded('sockets') ? 'Sockets Library' : 'fsockopen() / fread() / fwrite()' );

			echo '<tr style="background-color: ' . $colour . ';">';
				echo '<th style="text-shadow: none !important;">' . $text . '</th>';
				echo '<td>' . $status . '</td>';
				echo '<td>';
				if ( $useable ) {
					$actions = array();
					if ( $disabled )
						$actions[] = '<a href="admin-post.php?action=core_control-fs&module_action=enabletransport&transport=' . $transport . '">Enable Transport</a>';
					else
						$actions[] = '<a href="admin-post.php?action=core_control-fs&module_action=disabletransport&transport=' . $transport . '">Disable Transport</a>';
					//$actions[] = '<a href="' . add_query_arg(array('module_action' => 'testtransport', 'transport' => $transport)) . '">Test Transport</a>';
					
					echo implode(' | ', $actions);
				}
				echo '</td>';
				echo '<td>' . $extra . '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';

		echo '<h3>Filesystem Paths</h3>';
		echo '<div style="margin-left: 2em; margin-bottom: 3em;">';
			
		echo '<table class="widefat">';
		echo '<col style="text-align: left" width="20%" />
			  <col style="text-align:left" />
			  <col style="text-align:left" />
			  <col />
			  <thead>
			  <tr>
			  	<th>Constant</th>
				<th>Path</th>
				<th>Description</th>
				<th></th>
			  </tr>
			  </thead>
			  <tbody>
			  ';
		
		foreach ( array('ABSPATH' => 'Absolute path to WordPress', 'WP_CONTENT_DIR' => 'Absolute path to your Content Directory', 'WP_PLUGIN_DIR' => 'Absolute path to your Plugins Directory') as $constant => $desc ) {
			echo '<tr>';
				echo '<th>' . $constant . '</th>';
				echo '<td>' . constant($constant) . '</td>';
				echo '<td>' . $desc . '</td>';
				echo '<td></td>';
			echo '</tr>';
		}
		
		echo '</tbody></table>';
		echo '</div>';

	}
	
	function is_available($transport) {
		//Basically duplicates get_filesystem_method() in the way it probably should've been written
		$available = false;
		switch ( $transport ) {
			case 'direct':
				if( function_exists('getmyuid') && function_exists('fileowner') ){
					$temp_file = wp_tempnam();
					$available = getmyuid() == fileowner($temp_file);
					unlink($temp_file);
				}
				break;
			case 'ssh':
				$available = extension_loaded('ssh2');
				break;
			case 'ftpext':
				$available = extension_loaded('ftp');
				break;
			case 'ftpsockets':
				$available = extension_loaded('sockets') || function_exists('fsockopen');
				break;
		}
		return $available;
	}
}
?>