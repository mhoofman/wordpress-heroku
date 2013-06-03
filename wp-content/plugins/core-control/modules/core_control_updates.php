<?php
/*
Plugin Name: Updates Module
Version: 1.1
Description: Core Control Updates module, This allows you to Disable Plugin/Theme/Core update checking, or to force a check to take place.
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/
class core_control_updates {

	function __construct() {
		add_action('core_control-updates', array(&$this, 'the_page'));
		
		$this->settings = array('plugins' => array('enabled' => true), 'themes' => array('enabled' => true), 'core' => array('enabled' => true));
		$this->settings = get_option('core_control-updates', $this->settings);

		add_action('admin_post_core_control-updates', array(&$this, 'handle_posts'));

		if ( $this->settings['plugins']['enabled'] === false ) {
			remove_action( 'load-plugins.php', 'wp_update_plugins' );
			remove_action( 'load-update.php', 'wp_update_plugins' );
			remove_action( 'load-update-core.php', 'wp_update_plugins' );
			remove_action( 'admin_init', '_maybe_update_plugins' );
			remove_action( 'wp_update_plugins', 'wp_update_plugins' );
						
			add_action('pre_site_transient_update_plugins', array(&$this, 'handle_option_disable'));
			add_action('load-plugins.php', create_function('', 'add_action("admin_notices", array(&$GLOBALS["core-control"]->modules["core_control_updates"], "update_disabled_warning"));'));
			add_action('load-update-core.php', create_function('', 'add_action("admin_notices", array(&$GLOBALS["core-control"]->modules["core_control_updates"], "update_disabled_warning"));'));
		}
		if ( $this->settings['themes']['enabled'] === false ) { 
			remove_action( 'load-themes.php', 'wp_update_themes' );
			remove_action( 'load-update.php', 'wp_update_themes' );
			remove_action( 'load-update-core.php', 'wp_update_themes' );
			remove_action( 'admin_init', '_maybe_update_themes' );
			remove_action( 'wp_update_themes', 'wp_update_themes' );

			add_action('pre_site_transient_update_themes', array(&$this, 'handle_option_disable'));
			add_action('load-themes.php', create_function('', 'add_action("admin_notices", array(&$GLOBALS["core-control"]->modules["core_control_updates"], "update_disabled_warning"));'));
			add_action('load-update-core.php', create_function('', 'add_action("admin_notices", array(&$GLOBALS["core-control"]->modules["core_control_updates"], "update_disabled_warning"));'));

		}
		
		if ( $this->settings['core']['enabled'] === false ) {
			remove_action( 'admin_init', '_maybe_update_core' );
			remove_action( 'wp_version_check', 'wp_version_check' );
			
			add_action('pre_site_transient_update_core', array(&$this, 'handle_option_disable'));
			add_action('load-update-core.php', create_function('', 'add_action("admin_notices", array(&$GLOBALS["core-control"]->modules["core_control_updates"], "update_disabled_warning"));'));
		}
	
		//add_action('all', create_function('', 'var_dump(current_filter());'));
	}

	function has_page() {
		return true;
	}

	function menu() {
		return array('updates', 'Plugin, Theme, and Core Updates');
	}

	function handle_option_disable($val) {
		return (object)array('last_checked' => time()+3600, 'response' => null);
	}
	
	function update_disabled_warning() {
		echo '<div class="updated fade"><p><strong>Warning:</strong> Some Update checks are currently disabled, To enable, Visit <a href="tools.php?page=core-control&amp;module=updates">Core Control\'s options page</a>.</p></div>';
	}
	
	function handle_posts() {
		$option =& $this->settings;
		
		if ( isset($_POST['enable-plugin-check']) )
			$option['plugins']['enabled'] = true;
		elseif( isset($_POST['disable-plugin-check']) )
			$option['plugins']['enabled'] = false;

		elseif ( isset($_POST['enable-theme-check']) )
			$option['themes']['enabled'] = true;
		elseif( isset($_POST['disable-theme-check']) )
			$option['themes']['enabled'] = false;

		elseif ( isset($_POST['enable-core-check']) )
			$option['core']['enabled'] = true;
		elseif( isset($_POST['disable-core-check']) )
			$option['core']['enabled'] = false;
		
		update_option('core_control-updates', $option);
		wp_redirect(admin_url('tools.php?page=core-control&module=updates'));
	}
	
	function the_page() {
		$this->plugins();
		
		$this->themes();
		
		$this->core();
	}
	
	function plugins() {
		echo '<h3>Plugins</h3>';
		echo '<div style="margin-left: 2em; margin-bottom: 3em;">';
	
		if ( $this->settings['plugins']['enabled'] == false ) {
			echo '<p>Plugin update checking is currently disabled</p>';
		} else {
			if ( ! $plugins = get_site_transient('update_plugins') )
				$plugins = (object)array( 'last_checked' => 0, 'checked' => array(), 'response' => array());

			if ( !empty($plugins->last_checked) )
				printf('<p>Last updated: %s (<strong>%s ago</strong>)</p>', date('r', $plugins->last_checked), human_time_diff($plugins->last_checked, time()));
			if ( isset($plugins->checked) && isset($plugins->response) )
				printf('<p><strong>%s</strong> plugins checked, there are updates available for <strong>%s</strong> plugin(s)</p>', count($plugins->checked), count($plugins->response));
			
			echo '<p><a href="?page=core-control&module=updates&plugins_update=1">Check for plugin updates Now</a></p>';
			if ( !empty($_GET['plugins_update']) ) {
				echo '<div style="margin-left: 2em">';
				echo '<p>Checking for updates...</p>';
				delete_site_transient('update_plugins');
				$result = wp_update_plugins();
				if ( false === $result ) {
					echo '<p>An Error occured during the update check</p>';
				} else {
					$new = get_site_transient('update_plugins');
					if ( !empty($new->response) && !empty($plugins->response) ) {
						if ( count($new->response) > count($plugins->response) )
							echo '<p>New updates were found</p>';
						elseif ( count($new->response) == count($plugins->response) )
							echo '<p>No new updates were found</p>';
						elseif ( count($new->response) < count($plugins->response) )
							echo '<p>The available updates list has been updated</p>';
					} else {
						echo '<p>There are no plugin updates avaialble at this time</p>';
					}
				}
				
				echo '</div>';
			}
		} //end if disabled.
		echo '<hr />';
		echo '<form class="form-table" method="post" action="admin-post.php?action=core_control-updates">';
		if ( $this->settings['plugins']['enabled'] == false )
			echo '<input type="submit" name="enable-plugin-check" class="button-primary" value="Enable Plugin update checks" />';
		else
			echo '<input type="submit" name="disable-plugin-check" class="button-primary" value="Disable Plugin update checks" />';
		echo '</form>';
		echo '</div>';
	}
	
	function themes() {
		echo '<h3>Themes</h3>';
		echo '<div style="margin-left: 2em; margin-bottom: 3em;">';
		
		if ( $this->settings['themes']['enabled'] == false ) {
			echo '<p>Theme update checking is currently disabled.</p>';
		} else {
			$themes = get_site_transient('update_themes');
			if ( ! is_object($themes) )
				$themes = (object)array();
			if ( ! isset($themes->response) )
				$themes->response = array();
			if ( ! isset($themes->last_checked) )
				$themes->last_checked = 0;
			printf('<p>Last updated: %s (<strong>%s ago</strong>)</p>', date('r', $themes->last_checked), human_time_diff($themes->last_checked, time()));
			printf('<p>there are updates available for <strong>%s</strong> theme(s)</p>', count($themes->response));
			
			echo '<p><a href="?page=core-control&module=updates&themes_update=1">Check for theme updates Now</a></p>';
			if ( !empty($_GET['themes_update']) ) {
				echo '<div style="margin-left: 2em">';
				echo '<p>Checking for updates...</p>';
				delete_site_transient('update_themes');
				$result = wp_update_themes();
				if ( false === $result ) {
					echo '<p>An Error occured during the update check</p>';
				} else {
					$new = get_site_transient('update_themes');
					if ( isset($new->response) && isset($themes->response) ) {
						if ( count($new->response) > count($themes->response) )
							echo '<p>New updates were found</p>';
						elseif ( count($new->response) == count($themes->response) )
							echo '<p>No new updates were found</p>';
						elseif ( count($new->response) < count($themes->response) )
							echo '<p>The available updates list has been updated</p>';
					} else {
						echo '<p>There are no theme updates avaialble at this time</p>';
					}
				}
				
				echo '</div>';
			}
		} //end if disabled
		echo '<hr />';
		echo '<form class="form-table" method="post" action="admin-post.php?action=core_control-updates">';
		if ( $this->settings['themes']['enabled'] == false )
			echo '<input type="submit" name="enable-theme-check" class="button-primary" value="Enable Theme update checks" />';
		else
			echo '<input type="submit" name="disable-theme-check" class="button-primary" value="Disable Theme update checks" />';
		echo '</form>';
		echo '</div>';
	}
	
	function core() {
		echo '<h3>Core</h3>';
		echo '<div style="margin-left: 2em; margin-bottom: 3em;">';
		
		if ( $this->settings['core']['enabled'] == false ) {
			echo '<p>Core update checking is currently disabled.</p>';
		} else {
			$core = get_site_transient( 'update_core' );
			printf('<p>Last updated: %s (<strong>%s ago</strong>)</p>', date('r', $core->last_checked), human_time_diff($core->last_checked, time()));
			if ( 'development' == $core->updates[0]->response ) {
				$rev = '';
				if ( is_readable(ABSPATH . '/.svn/entries') ) {
					$revision = file(ABSPATH . '/.svn/entries');
					if ( isset($revision[3]) && $revision[3] = trim($revision[3]) )
						$rev = '(<a href="http://trac.wordpress.org/changeset/' . $revision[3] . '" title="The current Revision">r' . $revision[3] . '</a>)';
				}
				printf('<p>You are currently using development version <strong>%s</strong>%s, The latest available <em>stable</em> version is <strong>%s</strong></p>', $GLOBALS['wp_version'], $rev, $core->updates[0]->current);
			} else {
				printf('<p>You are currently using <strong>%s</strong>, The latest available version is <strong>%s</strong></p>', $GLOBALS['wp_version'], $core->updates[0]->current);
			}
			if ( 'en_US' != $core->updates[0]->locale )
				printf('<p>You are currently using an internationalised version of WordPress: <strong>%s</strong>', $core->updates[0]->locale);
			echo '<p><a href="?page=core-control&module=updates&core_update=1">Check for core updates Now</a></p>';
			if ( !empty($_GET['core_update']) ) {
				echo '<div style="margin-left: 2em">';
				echo '<p>Checking for updates...</p>';
				delete_site_transient('update_core');
				$result = wp_version_check();
				if ( false === $result ) {
					echo '<p>An Error occured during the update check</p>';
				} else {
					$new = get_site_transient('update_core');
					if ( is_object($new) && $core->updates[0]->current != $new->updates[0]->current )
						printf('<p>A new upgrade is available: <strong>%s</strong></p>',  $new->updates[0]->current);
					elseif ( is_object($new) && $core->updates[0]->current == $new->updates[0]->current )
						echo '<p>No new updates were found</p>';
					else
						echo '<p>An unexpected condition was hit</p>';
				}
				
				echo '</div>';
			}
		} //end if disabled
		echo '<hr />';
		echo '<form class="form-table" method="post" action="admin-post.php?action=core_control-updates">';
		if ( $this->settings['core']['enabled'] == false )
			echo '<input type="submit" name="enable-core-check" class="button-primary" value="Enable Core update checks" />';
		else
			echo '<input type="submit" name="disable-core-check" class="button-primary" value="Disable Core update checks" />';
		echo '</form>';
		echo '</div>';
	}

}