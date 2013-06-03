<?php
/*
Plugin Name: Cron Module
Version: 1.1
Description: Core Control Cron module, This allows you to manually run WordPress Cron Jobs and to diagnose Cron issues.
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/

class core_control_cron {

	function __construct() {
		add_action('core_control-cron', array(&$this, 'the_page'));

		$this->settings = array();

		$this->settings = get_option('core_control-cron', $this->settings);

		add_action('admin_post_core_control-cron', array(&$this, 'handle_posts'));
		add_action('admin_post_core_control-cron_cancel', array(&$this, 'handle_post_cancel'));
		add_action('admin_post_core_control-cron_run', array(&$this, 'handle_post_run'));

	}

	function has_page() {
		return true;
	}

	function menu() {
		return array('cron', 'Cron Tasks');
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
		
		update_option('core_control-cron', $option);
		wp_redirect(admin_url('tools.php?page=core-control&module=cron'));
	}
	
	function handle_post_cancel() {
		$url = admin_url('tools.php?page=core-control&module=cron&task=error');
		wp_redirect($url);
		$crons = get_option('cron', array());
		if ( isset($_GET['task']) ) {
			list($time, $hook, $id) = explode(':', urldecode(stripslashes($_GET['task'])));

			wp_unschedule_event($time, $hook, $crons[$time][$hook][$id]['args']);
			
			$url = add_query_arg('task', 'cancel', $url);
		}	
		wp_redirect($url);
	}
	function handle_post_run() {
		$url = admin_url('tools.php?page=core-control&module=cron&task=error');
		$crons = get_option('cron', array());
		wp_redirect($url);
		if ( isset($_GET['task']) ) {
			list($time, $hook, $id) = explode(':', urldecode(stripslashes($_GET['task'])));

			do_action_ref_array($hook, $crons[$time][$hook][$id]['args']);

			$url = add_query_arg('task', 'run', $url);
		}	
		wp_redirect($url);
	}
	
	function the_page() {
		echo '<h3>WordPress Cron Tasks</h3>';
		echo '<div style="margin-left: 2em; margin-bottom: 3em;">';

		$crons = get_option('cron', array());
		$schedules = wp_get_schedules();
		
		if ( isset($_GET['task']) ) {
			$task = $_GET['task'];
			if ( 'error' == $task ) {
				echo '<div class="updated fade"><p>' . __('An error occured', 'core-control') . '</p></div>';
			} else if ( 'cancel' == $task ) {
				echo '<div class="updated fade"><p>' . __('The selected task has been canceled', 'core-control') . '</p></div>';
			} else if ( 'run' == $task ) {
				echo '<div class="updated fade"><p>' . __('The selected task has been run', 'core-control') . '</p></div>';
			}
		}

		echo '<table class="widefat">';
		echo '<col style="text-align: left"/>
			  <col width="10%" />
			  <col style="text-align:left" />
			  <col />
			  <col />
			  <col />
			  <thead>
			  <tr>
			  	<th>Task Type</th>
			  	<th>Due Time</th>
				<th>Hook to run</th>
				<th>Arguements</th>
				<th>Actions</th>
				<th></th>
			  </tr>
			  </thead>
			  <tbody>
			  ';

		foreach ( (array)$crons as $time => $cron ) {
			if ( 'version' == $time ) continue;
			foreach ( (array)$cron as $hook => $task ) {
				foreach ( (array)$task as $id => $details ) {
					$once = false === $details['schedule'];
					
					echo '<tr>';
						echo '<th style="text-shadow: none !important;">',
							$once ? 'Once Off' : 'Reoccurring Task<br/> ' . (isset($schedules[$details['schedule']]) ? $schedules[$details['schedule']]['display'] : '<em><small>' . $details['schedule'] . '</small></em>'),
							'</th>';
						echo '<td>';
						//Ugly i know, I'll replace it at some point when i work out what i've done to deserve this..
						echo gmdate( 'Y-m-d H:i:s', $time + get_option( 'gmt_offset' ) * 3600 );
						echo ' ';
						echo get_option( 'gmt_offset' ) > 0 ? '+' : '-';
						if ( $pos = strpos(get_option( 'gmt_offset' ), '.') )
							echo (int)get_option( 'gmt_offset' ) . 60 * (float)( '0.' . substr(get_option( 'gmt_offset' ), $pos+1) );
						else
							echo get_option( 'gmt_offset' ) * 100;
						echo '</td>';
						echo '<td>' . $hook;
						if ( isset($GLOBALS['wp_filter'][$hook]) ) {
							$functions = array();
							foreach ( (array)$GLOBALS['wp_filter'][$hook] as $priority => $function ) {
								foreach ( $function as $hook_details )
									$functions[] = (isset($hook_details['class']) ? $hook_details['class'] . '::' : '') . $hook_details['function'] . '()';
							}
							echo '<br/><strong>Hooked functions:</strong> ' . implode(', ', $functions);
						}
						echo '</td>';
						echo '<td>';
						if ( !empty($details['args']) )
							echo implode(', ', $details['args']);
						else
							echo '<em>No Args</em>';
						echo '</td>';
						echo '<td>';
						$actions = array();
						if ( $once )
							$actions[] = '<a onclick="return confirm(\'Are you sure you wish to cancel this cron task?\')" href="admin-post.php?action=core_control-cron_cancel&task=' . urlencode("$time:$hook:$id") . '">Cancel Task</a>';
						$actions[] = '<a href="admin-post.php?action=core_control-cron_run&task=' . urlencode("$time:$hook:$id") . '">Run Now</a>';
						echo implode(' | ', $actions);
						echo '</td>';
						echo '<td></td>';
					echo '</tr>';
					} //end task
			} //end cron
		} //end crons
		echo '</tbody></table>';
		
		echo '</div>';
	}
}