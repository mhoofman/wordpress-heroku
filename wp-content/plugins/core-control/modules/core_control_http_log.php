<?php
/*
Plugin Name: HTTP Access Logger Module
Version: 1.1
Description: Core Control HTTP Logger module, This allows you to Log external connections which WordPress makes.
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/
class core_control_http_log {

	var $request = null;

	function __construct() {
		add_action('core_control-http_log', array(&$this, 'the_page'));
		
		$this->settings = array('logging' => false);

		$this->settings = get_option('core_control-http_log', $this->settings);

		add_action('admin_post_core_control-http_log', array(&$this, 'handle_posts'));
		add_action('admin_post_core_control-http_log-inspect', array(&$this, 'handle_ajax_inspect'));
		
		//Enable Logging if so be it.
		if ( $this->settings['logging'] != false && ( !defined('WP_HTTP_BLOCK_EXTERNAL') || !WP_HTTP_BLOCK_EXTERNAL) ) {
			add_filter('pre_http_request', array(&$this, 'do_log'), 10, 3);
			add_filter('http_request_args', array(&$this, 'do_log'), 10, 2 );
			add_action('http_api_debug', array(&$this, 'do_log'), 10, 3);
			add_action('shutdown', array(&$this, 'end_request'));
			
		}
		register_post_type('http', array(
										'label' => __('Core Control: HTTP Logger data', 'core-control'),
										'public' => false,
										'rewrite' => false,
									));
	}

	function has_page() {
		return true;
	}

	function menu() {
		return array('http_log', 'External HTTP Access Logger');
	}
	
	function timer_start() {
		$mtime = explode(' ', microtime() );
		$mtime = $mtime[1] + $mtime[0];
		return $mtime;
	}
	function timer_stop($start) {
		$mtime = explode(' ',  microtime() );
		$mtime = $mtime[1] + $mtime[0];
		return $mtime-$start;
	}

	function do_log($data = '', $log_type = '', $extra = '') {
		//before:
		if ( 'http_request_args' == current_filter() ) {
			//THIS IS A FILTER PEOPLE!
			if ( is_object($this->request) ) {
				//Previous request must've failed.
				$this->end_request();
			}
			$this->request = new core_control_http_log_item();
			$this->request->args = $data;
			$this->request->url = ('http' == substr($log_type, 0, 4)) ? $log_type : 'unknown';
			//RETURN ON FILTERS!
			return $data;
		//starting..
		} elseif ( 'pre_http_request' == current_filter() ) {
			$transport = WP_HTTP::_get_first_available_transport($log_type /* $args */, $extra /* $url */);

			$this->request->start = $this->timer_start();
			$this->request->realtime = time();
			$this->request->transports = $transport;
			return $data; //FILTER
		//after:
		} elseif ( 'http_api_debug' == current_filter() && 'response' == $log_type ) {
			$result =& $data;
			$class =& $extra;
			
			//if is_wp_error() erc.
			
			$this->request->time = $this->timer_stop($this->request->start);
			$this->request->result = $result;
			
			$this->end_request();
		}
	}
	
	function end_request() {
		if ( empty($this->request) )
			return;
		//A Request has finished, Lets remove useless items:
		unset($this->request->start);
		$post_content_filtered = '';
		if ( !is_wp_error($this->request->result) && isset($this->request->result['body']) ) {
			$post_content_filtered = $this->request->result['body'];
			unset($this->request->result['body']);
		}

		$arr = array(
			'post_type' => 'http',
			'post_status' => is_wp_error($this->request->result) ? 'error' : $this->request->result['response']['code'] . ' ' . $this->request->result['response']['message'],
			'post_title' => $this->request->args['method'] . ' ' . $this->request->url,
			'post_name' => md5($this->request->url . $this->request->realtime),
			'post_content' => addslashes(serialize($this->request)),
			'guid' => md5($this->request->url . $this->request->realtime),
			'post_content_filtered' => $post_content_filtered
		);

		//Disable error reporting for this call, Cron triggers requests before init, which causes $wp_rewrite not to be loaded causing the unique slug handler to fail.
		$err = error_reporting(0);
		wp_insert_post($arr);
		error_reporting($err);
		
		$this->request = null;
	}

	function handle_posts() {
		$option =& $this->settings;
		
		if ( isset($_POST['enable-logging']) )
			$option['logging'] = true;
		elseif( isset($_POST['disable-logging']) )
			$option['logging'] = false;
		
		if ( isset($_POST['delete-selected']) )
			foreach ( (array)$_POST['checked'] as $id )
				wp_delete_post($id);
		if ( isset($_POST['delete-all']) ) {
			set_time_limit(0); // we may need this if someone left it activated..
			// Delete 20 at a time for memory usage reasons.
			while ( $https = get_posts( array('post_type' => 'http', 'post_status' => 'any', 'numberposts' => 20) ) ) {
				foreach ( $https as $post ) {
					wp_delete_post($post->ID);
				}
			}
		}
		
		update_option('core_control-http_log', $option);
		wp_redirect(admin_url('tools.php?page=core-control&module=http_log'));
	}

	function the_page() {
		$module_action = isset($_REQUEST['module_action']) ? $_REQUEST['module_action'] : '';
		if ( 'inspect' == $module_action ) {
			$this->inspect($_REQUEST['ID']);
		} else {
			$this->form();
			$this->table();
		}
	}

	function form() {
		echo '<form method="post" action="admin-post.php?action=core_control-http_log">';
		if ( $this->settings['logging'] == false ) {
			echo '<p>Logging is currently <strong>disabled</strong></p>';
			echo '<input type="submit" name="enable-logging" class="button-primary" value="Enable HTTP Logging" />';
		} else {
			echo '<p>Logging is currently <strong>enabled</strong></p>';
			echo '<input type="submit" name="disable-logging" class="button-primary" value="Disable HTTP Logging" />';
		}
		echo '<p><br class="clear" /></p>';
		echo '</form>';
	}

	function table() {
		$https = get_posts( array('post_type' => 'http', 'post_status' => 'any', 'numberposts' => -1) );
		if ( defined('WP_HTTP_BLOCK_EXTERNAL') && WP_HTTP_BLOCK_EXTERNAL ) {
			echo '<p>Logging is currently disabled, It appears you are blocking outgoing connections in your wp-config.php file through the define <code>WP_HTTP_BLOCK_EXTERNAL</code>.</p>';
			return;
		}
		?>
		<form method="post" action="admin-post.php?action=core_control-http_log">
		<br class="clear" />
		<noscript><div class="message face"><p><strong>Please Note:</strong> This page requires Javascript in order to work.</p></div></noscript>
		<p>Click a row to load the details for that request</p>
		<script type="text/javascript">
		var tab_changer_function = function() {
			var $ = jQuery;
			$(this).parents('ul').find('li.current').removeClass('current');
			$(this).parents('li').addClass('current');
			$(this).parents('td').find('div.tab-content').hide();
			$( $(this).attr('href') ).show(); //href = element id :)
			return false;
		};
		var load_item_function = function() {
			var $ = jQuery;
			var id = 0;
			var _ele = $(this).parents('tr').attr('id').split('-');
			id = _ele[1];

			if ( $('#details-' + id).length > 0 )
				return $('#details-' + id).toggle();
			var div = '<tr><td colspan="5">Loading.. Please wait..</td></tr>';
			div = $(div);
			div.attr('id', 'details-' + id);
			$('#http-' + id).after( div );
			$.post( '<?php echo esc_js(admin_url('admin-post.php')) ?>', {
				'action': 'core_control-http_log-inspect',
				'ID': id
			}, function(data) {
				$('#details-' + id + ' td').html( data );
				$('#details-' + id + ' td ul.tab-listing a').bind('click', tab_changer_function);
				$('#details-' + id + ' td ul.tab-listing a:first').click();
			});
		}
		jQuery(document).ready( function() {
			jQuery('table.requests td:not(.check-column)').bind('click', load_item_function);
		});
		</script>
		<style type="text/css">
			ul.tab-listing {
				margin-bottom: 10px;
			}
			ul.tab-listing li {
				display: inline;
				list-style: none;
				border: thin solid #0066FF;
				margin-right: 10px;
				padding: 5px 0px;
			}
			ul.tab-listing li a {
				padding: 5px;
			}
			ul.tab-listing a:hover, ul.tab-listing li.current, ul.tab-listing li.current a {
				background-color: #0066FF;
				color:#FFFFFF;
			}
			.tab-content table th {
				white-space: nowrap;
			}
		</style>
		<input type="submit" name="delete-selected" class="button-primary" value="Delete Selected Requests" />
		<input type="submit" name="delete-all" class="button-primary" value="Delete All Stored Requests" />
		<table class="widefat requests">
			<thead>
			<tr>
				<th class="check-column"><input type="checkbox" name="check-all" /></th>
				<th>URL</th>
				<th>Status</th>
				<th>Time</th>
				<th>Date</th>
			</tr>
			</thead>
			<tbody>
			<?php
				foreach ( (array)$https as $request ) {
					$the_request = @unserialize($request->post_content);
					if ( !is_wp_error($the_request->result) && isset($the_request->result['response']) && !empty($request->post_content_filtered) ) 
						$the_request->result['body'] = $request->post_content_filtered;
					echo '<tr id="http-' . $request->ID . '">';
					echo '<td class="check-column"><input type="checkbox" name="checked[]" value="' . $request->ID . '" /></td>';
					echo '<td class="title">' . $request->post_title . '</td>';
					echo '<td>' . $request->post_status . '</td>';
					echo '<td>' . round($the_request->time, 3) . 's</td>';
					echo '<td>' . $request->post_date . '</td>';
					echo '</tr>';
				}
			?>
			</tbody>
		</table>
		<input type="submit" name="delete-selected" class="button-primary" value="Delete Selected Requests" />
		<input type="submit" name="delete-all" class="button-primary" value="Delete All Stored Requests" />
		</form>
		<?php
	}
	function handle_ajax_inspect() {
		if ( empty($_REQUEST['ID']) )
			return;
		$id = $_REQUEST['ID'];
		$request = get_post($id);
		$the_request = unserialize($request->post_content);
		if ( !is_wp_error($the_request->result) && isset($the_request->result['response']) && !empty($request->post_content_filtered) ) 
			$the_request->result['body'] = $request->post_content_filtered;
		?>
		<div><ul class="hide-if-no-js tab-listing">
				<li><a href="#request-details-<?php echo $id ?>">Request Details</a></li>
				<?php if ( !is_wp_error($the_request->result) && !empty($the_request->result['headers']) ) : ?><li><a href="#response-headers-<?php echo $id ?>">Response Headers</a></li> <?php endif; ?>
				<?php if ( !is_wp_error($the_request->result) &&!empty($the_request->result['body']) ) : ?><li><a href="#response-body-<?php echo $id ?>">Response Body</a></li> <?php endif; ?>
			</ul>
			<br class="clear" /></div>
		
		<div class="tab-content" id="request-details-<?php echo $id ?>">
			<table>
				<tr>
					<th>URL</th>
					<td><?php echo $the_request->url ?></td>
				</tr>
				<tr>
					<th>Method</th>
					<td><?php echo $the_request->args['method'] ?></td>
				</tr>
				<tr>
					<th>Result</th>
					<td><?php
							if ( is_wp_error($the_request->result) || !isset($the_request->result['response']) )
								echo 'error';
							else
								echo $the_request->result['response']['code'] . ' ' . $the_request->result['response']['message'];
							 ?></td>
				</tr>
<?php if ( is_wp_error($the_request->result) ) : ?>
				<tr>
					<th>Error Details</th>
					<td><table><?php
							foreach ( (array)$the_request->result->errors as $code => $value ) {
								$value = implode(', ', (array)$value);
								echo '<tr>';
									echo '<td valign="top">' . $code . '</td>';
									echo '<td>' . $value . '</td>';
								echo '</tr>';
							}
							?></table></td>
				</tr>
<?php endif; ?>
				<tr>
					<th>Time Taken</th>
					<td><?php echo $the_request->time ?> seconds</td>
				</tr>
				<tr>
					<th>HTTP Args</th>
					<td><table><?php
							foreach ( (array)$the_request->args as $key => $value ) {
								if ( 'method' == $key || 'body' == $key )
									continue;
								$value = $this->pretty_type_var($value);
								echo '<tr>';
									echo '<td valign="top">' . $key . '</td>';
									echo '<td>' . $value . '</td>';
								echo '</tr>';
							}
							?></table></td>
				</tr>
				<?php if ( isset($the_request->args['body']) ) : ?>
				<tr>
					<th>HTTP POST <em>body</em></th>
					<td><?php echo $this->pretty_type_var($the_request->args['body']); ?></td>
				</tr>
				<?php endif; ?>
				<tr>
					<th>HTTP Transports</th>
					<td><?php echo ( is_array($the_request->transports) ? implode(', ', $the_request->transports) : $the_request->transports ); ?></td>
				</tr>
				<tr>
					<th>Request Time</th>
					<td><?php
							//Ugly i know, I'll replace it at some point when i work out what i've done to deserve this..
							echo gmdate( 'Y-m-d H:i:s', ( $the_request->realtime + ( get_option( 'gmt_offset' ) * 3600 ) ) );
							echo ' ';
							echo get_option( 'gmt_offset' ) > 0 ? '+' : '-';
							if ( $pos = strpos(get_option( 'gmt_offset' ), '.') )
								echo (int)get_option( 'gmt_offset' ) . 60 * (float)( '0.' . substr(get_option( 'gmt_offset' ), $pos+1) );
							else
								echo get_option( 'gmt_offset' ) * 100;
					 ?></td>
				</tr>
			</table>
		</div>
		<div class="tab-content" id="response-headers-<?php echo $id ?>">
			<table>
				<?php
				if ( !is_wp_error($the_request->result) && !empty($the_request->result['headers']) ) {
					foreach ( $the_request->result['headers'] as $header => $value ) {
						$header = htmlentities($header);
						$value = htmlentities($value);
						echo '<tr>';
							echo '<th>' . $header . '</th>';
							echo '<td>' . $value . '</td>';
						echo '</tr>';
					}
				}
			?>
			</table>
		</div>
		<div class="tab-content" id="response-body-<?php echo $id ?>">
			<?php
				if ( !is_wp_error($the_request->result) ) {
					$body =& $the_request->result['body'];
					if ( is_serialized($body) ) {
						echo '<p>Content looks to be a Serialized string, Deserializing</p>';
						echo '<pre><code>';
						echo htmlentities(print_r(unserialize($body), true));
						echo "\n</code></pre>";
					} else {
						echo '<pre>' . htmlentities($body) . '</pre>';
					}
				}
			?>
		</div>
		<?php
	}
	function pretty_type_var($value) {
		$type = gettype($value);
		switch ( $type ) {
			case 'resource';
			case 'object':
			case 'array':
				$value = '<pre><code>' . htmlentities(print_r($value, true)) . '</code></pre>';
				break;
			case 'double':
			case 'integer':
				$value = "({$type}) " . ( empty($value) ? '0' : (string)$value );
				break;
			case 'string':
				$value = htmlentities("({$type}) $value");
				break;
			case 'NULL':
				$value = "NULL";
				break;
			case 'boolean':
				$value = "({$type}) " . ($value ? 'true' : 'false');
				break;
			default:
				$value = htmlentities($value);
		}
		return $value;
	}
}

class core_control_http_log_item {}