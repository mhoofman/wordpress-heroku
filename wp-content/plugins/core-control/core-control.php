<?php
/*
Plugin Name: Core Control
Version: 1.1
Plugin URI: http://dd32.id.au/wordpress-plugins/core-control/
Description: Core Control is a set of plugin modules which can be used to control certain aspects of the WordPress control.
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/

$GLOBALS['core-control'] = new core_control();
class core_control {
	var $basename = '';
	var $folder = '';
	var $version = '1.1';
	
	var $modules = array();
	
	function __construct() {
		//Set the directory of the plugin:
		$this->basename = plugin_basename(__FILE__);
		$this->folder = dirname($this->basename);

		// Load modules ASAP
		add_action('plugins_loaded', array(&$this, 'load_modules'), 1);

		//Register general hooks.
		add_action('admin_menu', array(&$this, 'admin_menu'));
		register_activation_hook(__FILE__, array(&$this, 'activate'));

		//Add actions/filters
		add_action('admin_post_core_control-modules', array(&$this, 'handle_posts'));

		//Add page
		add_action('core_control-default', array(&$this, 'default_page'));

	}
	
	function admin_menu() {
		add_submenu_page('tools.php', __('Core Control', 'core-control'), __('Core Control', 'core-control'), 'manage_options', 'core-control', array(&$this, 'main_page'));
	}

	function activate() {
		global $wp_version;
		if ( ! version_compare( $wp_version, '3.2', '>=') ) {
			if ( function_exists('deactivate_plugins') )
				deactivate_plugins(__FILE__);
			die(__('<strong>Core Control:</strong> Sorry, This plugin requires WordPress 3.2+', 'core-control'));
		}
	}

	function load_modules() {
		$modules = get_option('core_control-active_modules', array());
		foreach ( (array) $modules as $module ) {
			if ( 0 !== validate_file($module) )
				continue;
			if ( ! file_exists(WP_PLUGIN_DIR . '/' . $this->folder . '/modules/' . $module) )
				continue;
			include_once WP_PLUGIN_DIR . '/' . $this->folder . '/modules/' . $module;
			$class = basename($module, '.php');
			$this->modules[ $class ] = new $class;
		}
	}

	function is_module_active($module) {
		return in_array( $module, get_option('core_control-active_modules', array()) );
	}
	
	function handle_posts() {
		$checked = isset($_POST['checked']) ? stripslashes_deep( (array)$_POST['checked'] ) : array();

		foreach ( $checked as $index => $module ) {
			if ( 0 !== validate_file($module) ||
				! file_exists(WP_PLUGIN_DIR . '/' . $this->folder . '/modules/' . $module) )
					unset($checked[$index]);
		}

		update_option('core_control-active_modules', $checked);
		wp_redirect( admin_url('tools.php?page=core-control') );
	}
	
	function main_page() {
		echo '<div class="wrap">';
		screen_icon('tools');
		echo '<h2>Core Control</h2>';
	
		if ( version_compare(PHP_VERSION, '5.2.0', '<') )
			printf(__('<p><strong>Core Control:</strong> WARNING!! Your server is currently running PHP %s, Please bug your host to upgrade to a recent version of PHP which is less bug-prone. At last count, <strong>over 80%% of WordPress installs are using PHP 5.2+</strong>, WordPress will require PHP 5.2+ some day soon, Prepare while your have time time.</p>', 'core-control'), PHP_VERSION);
		
		$module = !empty($_GET['module']) ? $_GET['module'] : 'default';
		
		$menus = array( array('default', 'Main Page') );
		foreach ( $this->modules as $a_module ) {
			if ( ! $a_module->has_page() )
				continue;
			$menus[] = $a_module->menu();
		}
		echo '<ul class="subsubsub">';
		foreach ( $menus as $menu ) {
			$url = 'tools.php?page=core-control';
			if ( 'default' != $menu[0] )
				$url .= '&module=' . $menu[0];
			$title = $menu[1];
			$sep = $menu == end($menus) ? '' : ' | ';
			$current = $module == $menu[0] ? ' class="current"' : '';
			echo "<li><a href='$url'$current>$title</a>$sep</li>";
		}
		echo '</ul>';
		echo '<br class="clear" />';

		do_action('core_control-' . $module);

		echo '</div>';
	}

	function default_page() {
		$files = $this->find_files( WP_PLUGIN_DIR . '/' . $this->folder . '/modules/', array('pattern' => '*.php', 'levels' => 1, 'relative' => true) );
?>
<p>Welcome to Core Control, Please select the subsection from the above menu which you would like to modify</p>
<p>You may Enable/Disable which modules are loaded by checking them in the following list:
<form method="post" action="admin-post.php?action=core_control-modules">
<table class="widefat">
	<thead>
	<tr>
		<th scope="col" class="check-column"><input type="checkbox" name="check-all" /></th>
		<th scope="col">Module Name</th>
		<th scope="col">Description</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach ( $files as $module ) {
			$details = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->folder . '/modules/' . $module, true, false);
			$active = $this->is_module_active($module);
			$style = $active ? ' style="background-color: #e7f7d3"' : '';
	?>
	<tr<?php echo $style ?>>
		<th scope="row" class="check-column"><input type="checkbox" name="checked[]" value="<?php echo esc_attr($module) ?>" <?php checked($active); ?> /></th>
		<td><?php echo $details['Title'] . ' ' . $details['Version'] ?></td>
		<td><?php echo $details['Description'] ?></td>
	</tr>
	<?php
		} //end foreach;
	?>
	</tbody>
</table>
<input type="submit" class="button-secondary" value="Save Module Choices" />
</p>
</form>
<?php
	}

	//HELPERS
	function find_files( $folder, $args = array() ) {
	
		$folder = untrailingslashit($folder);
	
		$defaults = array( 'pattern' => '', 'levels' => 100, 'relative' => false );
		$r = wp_parse_args($args, $defaults);

		extract($r, EXTR_SKIP);
		
		//Now for recursive calls, clear relative, we'll handle it, and decrease the levels.
		unset($r['relative']);
		--$r['levels'];
	
		if ( ! $levels )
			return array();
		
		if ( ! is_readable($folder) )
			return false;

		if ( true === $relative )
			$relative = $folder;
	
		$files = array();
		if ( $dir = @opendir( $folder ) ) {
			while ( ( $file = readdir($dir) ) !== false ) {
				if ( in_array($file, array('.', '..') ) )
					continue;
				if ( is_dir( $folder . '/' . $file ) ) {
					$files2 = $this->find_files( $folder . '/' . $file, $r );
					if( $files2 )
						$files = array_merge($files, $files2 );
					else if ( empty($pattern) || preg_match('|^' . str_replace('\*', '\w+', preg_quote($pattern)) . '$|i', $file) )
						$files[] = $folder . '/' . $file . '/';
				} else {
					if ( empty($pattern) || preg_match('|^' . str_replace('\*', '\w+', preg_quote($pattern)) . '$|i', $file) )
						$files[] = $folder . '/' . $file;
				}
			}
		}
		@closedir( $dir );
	
		if ( ! empty($relative) ) {
			$relative = trailingslashit($relative);
			foreach ( $files as $key => $file )
				$files[$key] = preg_replace('!^' . preg_quote($relative) . '!', '', $file);
		}
	
		return $files;
	}

}//end class

?>
