<?php

require_once 'functions.php';

ga_maintain_compatibility();

if ( !current_user_can( 'manage_options' ) ) {
	return;
}

if (isset($_REQUEST['Clear'])){
	ga_dash_clear_cache();
	?><div class="updated"><p><strong><?php _e('Cleared Cache.', 'ga-dash' ); ?></strong></p></div>  
	<?php
}

if (isset($_REQUEST['Reset'])){

	ga_dash_reset_token();
	?><div class="updated"><p><strong><?php _e('Token Reseted.', 'ga-dash'); ?></strong></p></div>  
	<?php
}else if(ga_dash_safe_get('ga_dash_hidden') == 'Y') {  
        //Form data sent  
		
        $apikey = ga_dash_safe_get('ga_dash_apikey');  
		update_option('ga_dash_apikey', sanitize_text_field($apikey));  
		
        $clientid = ga_dash_safe_get('ga_dash_clientid');
		update_option('ga_dash_clientid', sanitize_text_field($clientid));  
		
        $clientsecret = ga_dash_safe_get('ga_dash_clientsecret');  
		update_option('ga_dash_clientsecret', sanitize_text_field($clientsecret));  
		
        $ga_dash_access = ga_dash_safe_get('ga_dash_access');  
        update_option('ga_dash_access', $ga_dash_access);

        $ga_dash_access_front = ga_dash_safe_get('ga_dash_access_front');  
        update_option('ga_dash_access_front', $ga_dash_access_front);

        $ga_dash_access_back = ga_dash_safe_get('ga_dash_access_back');  
        update_option('ga_dash_access_back', $ga_dash_access_back);		
		
		$ga_dash_tableid_jail = ga_dash_safe_get('ga_dash_tableid_jail');  
        update_option('ga_dash_tableid_jail', $ga_dash_tableid_jail); 
		
		$ga_dash_pgd = ga_dash_safe_get('ga_dash_pgd');
		update_option('ga_dash_pgd', $ga_dash_pgd);

		$ga_dash_rd = ga_dash_safe_get('ga_dash_rd');
		update_option('ga_dash_rd', $ga_dash_rd);

		$ga_dash_sd = ga_dash_safe_get('ga_dash_sd');
		update_option('ga_dash_sd', $ga_dash_sd);		
		
		$ga_dash_map = ga_dash_safe_get('ga_dash_map');
		update_option('ga_dash_map', $ga_dash_map);
		
		$ga_dash_traffic = ga_dash_safe_get('ga_dash_traffic');
		update_option('ga_dash_traffic', $ga_dash_traffic);		

		$ga_dash_frontend = ga_dash_safe_get('ga_dash_frontend');
		update_option('ga_dash_frontend', $ga_dash_frontend);		
		
		$ga_dash_style = ga_dash_safe_get('ga_dash_style');
		update_option('ga_dash_style', $ga_dash_style);
		
		$ga_dash_jailadmins = ga_dash_safe_get('ga_dash_jailadmins');
		update_option('ga_dash_jailadmins', $ga_dash_jailadmins);
		
		$ga_dash_cachetime = ga_dash_safe_get('ga_dash_cachetime');
		update_option('ga_dash_cachetime', $ga_dash_cachetime);
		
		$ga_dash_tracking = ga_dash_safe_get('ga_dash_tracking');
		update_option('ga_dash_tracking', $ga_dash_tracking);		

		$ga_dash_tracking_type = ga_dash_safe_get('ga_dash_tracking_type');
		update_option('ga_dash_tracking_type', $ga_dash_tracking_type);			
		
		$ga_dash_default_ua = ga_dash_safe_get('ga_dash_default_ua');
		update_option('ga_dash_default_ua', $ga_dash_default_ua);

		$ga_dash_anonim = ga_dash_safe_get('ga_dash_anonim');
		update_option('ga_dash_anonim', $ga_dash_anonim);

		$ga_dash_userapi = ga_dash_safe_get('ga_dash_userapi');
		update_option('ga_dash_userapi', $ga_dash_userapi);			

		$ga_event_tracking = ga_dash_safe_get('ga_event_tracking');
		update_option('ga_event_tracking', $ga_event_tracking);	

		$ga_event_downloads = ga_dash_safe_get('ga_event_downloads');
		update_option('ga_event_downloads', $ga_event_downloads);	

		$ga_track_exclude = ga_dash_safe_get('ga_track_exclude');
		update_option('ga_track_exclude', $ga_track_exclude);		

		$ga_target_geomap =  strtoupper(ga_dash_safe_get('ga_target_geomap'));
		update_option('ga_target_geomap',  strtoupper($ga_target_geomap));

		$ga_target_number = ga_dash_safe_get('ga_target_number');
		update_option('ga_target_number', $ga_target_number);

		$ga_realtime_pages = ga_dash_safe_get('ga_realtime_pages');
		update_option('ga_realtime_pages', $ga_realtime_pages);		
				
		if (!isset($_REQUEST['Clear']) AND !isset($_REQUEST['Reset'])){
			?>  
			<div class="updated"><p><strong><?php _e('Options saved.', 'ga-dash'); ?></strong></p></div>  
			<?php
		}
		
		ga_dash_clear_cache();
		
		ga_maintain_compatibility();
		
    }else if(ga_dash_safe_get('ga_dash_hidden') == 'A') {
        $apikey = ga_dash_safe_get('ga_dash_apikey');  
		update_option('ga_dash_apikey', sanitize_text_field($apikey));  
		
        $clientid = ga_dash_safe_get('ga_dash_clientid');
		update_option('ga_dash_clientid', sanitize_text_field($clientid));  
		
        $clientsecret = ga_dash_safe_get('ga_dash_clientsecret');  
		update_option('ga_dash_clientsecret', sanitize_text_field($clientsecret));  

		$ga_dash_userapi = ga_dash_safe_get('ga_dash_userapi');
		update_option('ga_dash_userapi', $ga_dash_userapi);			
	}
	
if (isset($_REQUEST['Authorize'])){
	$adminurl = admin_url("#ga-dash-widget");
	echo '<script> window.location="'.$adminurl.'"; </script> ';
}
	
$apikey = get_option('ga_dash_apikey');  
$clientid = get_option('ga_dash_clientid');  
$clientsecret = get_option('ga_dash_clientsecret');  
$ga_dash_access = get_option('ga_dash_access'); 
$ga_dash_tableid_jail = get_option('ga_dash_tableid_jail');
$ga_dash_pgd = get_option('ga_dash_pgd');
$ga_dash_rd = get_option('ga_dash_rd');
$ga_dash_sd = get_option('ga_dash_sd');
$ga_dash_map = get_option('ga_dash_map');
$ga_dash_traffic = get_option('ga_dash_traffic');
$ga_dash_frontend = get_option('ga_dash_frontend');
$ga_dash_style = get_option('ga_dash_style');
$ga_dash_cachetime = get_option('ga_dash_cachetime');
$ga_dash_jailadmins = get_option('ga_dash_jailadmins');
$ga_dash_tracking = get_option('ga_dash_tracking');
$ga_dash_tracking_type = get_option('ga_dash_tracking_type');
$ga_dash_default_ua = get_option('ga_dash_default_ua');
$ga_dash_anonim = get_option('ga_dash_anonim');
$ga_dash_userapi = get_option('ga_dash_userapi');
$ga_event_tracking = get_option('ga_event_tracking');
$ga_event_downloads = get_option('ga_event_downloads');
$ga_track_exclude = get_option('ga_track_exclude');
$ga_dash_access_front = get_option('ga_dash_access_front');
$ga_dash_access_back = get_option('ga_dash_access_back');
$ga_target_geomap = get_option('ga_target_geomap');
$ga_target_number = get_option('ga_target_number');
$ga_realtime_pages = get_option('ga_realtime_pages');

if ( is_rtl() ) {
	$float_main="right";
	$float_note="left";
}else{
	$float_main="left";
	$float_note="right";	
}

?>  
<div class="wrap">
<div style="width:70%;float:<?php echo $float_main; ?>;">  
    <?php echo "<h2>" . __( 'Google Analytics Dashboard Settings', 'ga-dash' ) . "</h2>"; ?>  
        <form name="ga_dash_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
		<?php echo "<h3>". __( 'Google Analytics API', 'ga-dash' )."</h3>"; ?>  
        <?php echo "<i>".__("You should watch this", 'ga-dash')." <a href='http://deconf.com/google-analytics-dashboard-wordpress/' target='_blank'>". __("Step by step video tutorial")."</a> ".__("before proceeding to authorization", 'ga-dash').". ".__("To authorize this application using our API Project, press the", 'ga_dash')." <b>".__("Authorize Application", 'ga-dash')."</b> ".__(" button. If you want to authorize it using your own API Project, check the option bellow and enter your project credentials before pressing the", 'ga-dash')." <b>".__("Authorize Application", 'ga-dash')."</b> ".__("button.", 'ga-dash')."</i>";?>
		<p><input name="ga_dash_userapi" type="checkbox" id="ga_dash_userapi" onchange="this.form.submit()" value="1"<?php if (get_option('ga_dash_userapi')) echo " checked='checked'"; ?>  /><?php echo "<b>".__(" use your own API Project credentials", 'ga-dash' )."</b>"; ?></p>
		<?php
		if (get_option('ga_dash_userapi')){?>
			<p><?php echo "<b>".__("API Key:", 'ga-dash')." </b>"; ?><input type="text" name="ga_dash_apikey" value="<?php echo $apikey; ?>" size="61"></p>  
			<p><?php echo "<b>".__("Client ID:", 'ga-dash')." </b>"; ?><input type="text" name="ga_dash_clientid" value="<?php echo $clientid; ?>" size="60"></p>  
			<p><?php echo "<b>".__("Client Secret:", 'ga-dash')." </b>"; ?><input type="text" name="ga_dash_clientsecret" value="<?php echo $clientsecret; ?>" size="55"></p>  
		<?php }?>
		<p><?php 
			if (get_option('ga_dash_token')){
				echo "<input type=\"submit\" name=\"Reset\" class=\"button button-primary\" value=\"".__("Clear Authorization", 'ga-dash')."\" />";
				?> <input type="submit" name="Clear" class="button button-primary" value="<?php _e('Clear Cache', 'ga-dash' ) ?>" /><?php		
				echo '<input type="hidden" name="ga_dash_hidden" value="Y">';  
			} else{
				echo "<input type=\"submit\" name=\"Authorize\" class=\"button button-primary\" value=\"".__("Authorize Application", 'ga-dash')."\" />";
				?> <input type="submit" name="Clear" class="button button-primary" value="<?php _e('Clear Cache', 'ga-dash' ) ?>" /><?php
				echo '<input type="hidden" name="ga_dash_hidden" value="A">';
				echo "</form>";
				_e("(the rest of the settings will show up after completing the authorization process)", 'ga-dash' );
				echo "</div>";
				?>
				<div class="ga-note" style="float:<?php echo $float_note; ?>;text-align:<?php echo $float_main; ?>;"> 
						<center>
							<h3><?php _e("Setup Tutorial",'ga-dash') ?></h3>
							<a href="http://deconf.com/google-analytics-dashboard-wordpress/" target="_blank"><img src="../wp-content/plugins/google-analytics-dashboard-for-wp/img/video-tutorial.png" width="95%" /></a>
						</center>
						<center>
							<br /><h3><?php _e("Support Links",'ga-dash') ?></h3>
						</center>			
						<ul>
							<li><a href="http://deconf.com/google-analytics-dashboard-wordpress/" target="_blank"><?php _e("Google Analytics Dashboard Official Page",'ga-dash') ?></a></li>
							<li><a href="http://wordpress.org/support/plugin/google-analytics-dashboard-for-wp" target="_blank"><?php _e("Google Analytics Dashboard Wordpress Support",'ga-dash') ?></a></li>
							<li><a href="http://forum.deconf.com/wordpress-plugins-f182/" target="_blank"><?php _e("Google Analytics Dashboard on Deconf Forum",'ga-dash') ?></a></li>			
						</ul>
						<center>
							<br /><h3><?php _e("Useful Plugins",'ga-dash') ?></h3>
						</center>			
						<ul>
							<li><a href="http://deconf.com/youtube-analytics-dashboard-wordpress/" target="_blank"><?php _e("YouTube Analytics Dashboard",'ga-dash') ?></a></li>
							<li><a href="http://deconf.com/earnings-dashboard-google-adsense-wordpress/" target="_blank"><?php _e("Earnings Dashboard for Google Adsense™",'ga-dash') ?></a></li>
							<li><a href="http://deconf.com/clicky-analytics-dashboard-wordpress/" target="_blank"><?php _e("Clicky Analytics",'ga-dash') ?></a></li>						
							<li><a href="http://wordpress.org/extend/plugins/follow-us-box/" target="_blank"><?php _e("Follow Us Box",'ga-dash') ?></a></li>			
						</ul>			
				</div></div><?php				
				return;
			} ?>
		</p>  
		<?php echo "<h3>" . __( 'Main Dashboard Settings', 'ga-dash' ). "</h3>";?>
		<p><?php _e("Access Level: ", 'ga-dash' ); ?>
		<select id="ga_dash_access" name="ga_dash_access">
			<option value="manage_options" <?php if (($ga_dash_access=="manage_options") OR (!$ga_dash_access)) echo "selected='yes'"; echo ">".__("Administrators", 'ga-dash');?></option>
			<option value="edit_pages" <?php if ($ga_dash_access=="edit_pages") echo "selected='yes'"; echo ">".__("Editors", 'ga-dash');?></option>
			<option value="publish_posts" <?php if ($ga_dash_access=="publish_posts") echo "selected='yes'"; echo ">".__("Authors", 'ga-dash');?></option>
			<option value="edit_posts" <?php if ($ga_dash_access=="edit_posts") echo "selected='yes'"; echo ">".__("Contributors", 'ga-dash');?></option>
		</select></p>

		<p><?php

			_e("Lock selected access level to this profile: ", 'ga-dash' );
			$profiles=get_option('ga_dash_profile_list');
			$not_ready=false;
			
			if (!is_array($profiles)){
				$not_ready=true;
			}			
			
			echo '<select id="ga_dash_tableid_jail" name="ga_dash_tableid_jail">';
			if (!$not_ready) {			
				foreach ($profiles as $items) {
					if ($items[3]){
						if (!get_option('ga_dash_tableid_jail')) {
							update_option('ga_dash_tableid_jail',$items[1]);
						}
						echo '<option value="'.$items[1].'"'; 
						if ((get_option('ga_dash_tableid_jail')==$items[1])) echo "selected='yes'";
						echo '>'.ga_dash_get_profile_domain($items[3]).'</option>';
					} else {
						$not_ready=true;
						ga_dash_clear_cache();
					}
				}
			}	
			echo '</select>';
			if ($not_ready){
				echo '<font color="red"> &#9668;-- '.__("your profile list needs an update:",'ga-dash').'</font>';
				$adminurl = admin_url("#ga-dash-widget");
				echo ' <a href="'.$adminurl.'">'.__("Click here",'ga-dash').'</a>';
			}			
		?></p>
		
		<p><input name="ga_dash_jailadmins" type="checkbox" id="ga_dash_jailadmins" value="1"<?php if (get_option('ga_dash_jailadmins')) echo " checked='checked'"; ?>  /><?php _e(" disable dashboard's Switch Profile functionality", 'ga-dash' ); ?></p>
		<?php echo "<h3><div style='float:left;'>" . __( 'Real-Time Settings', 'ga-dash' ). "</div><div style='font-style:italic;color:red;font-size:0.7em;vertical-align:top;margin-top:-3px;float:left;clear:right;'>&nbsp;Beta Feature</div></h3><br />";?>
		<p><?php echo __("Maximum number of pages to display on real-time tab:", 'ga-dash'); ?> <input type="text" style="text-align:center;" name="ga_realtime_pages" value="<?php echo $ga_realtime_pages; ?>" size="3"> <?php _e("(find out more", 'ga-dash') ?> <a href="http://deconf.com/google-analytics-dashboard-real-time-reports/" target="_blank"><?php _e("about this feature", 'ga-dash') ?></a><?php _e(")", 'ga-dash') ?></p>
		<?php echo "<h3>" . __( 'Additional Frontend Settings', 'ga-dash' ). "</h3>";?>
		<p><input name="ga_dash_frontend" type="checkbox" id="ga_dash_frontend" value="1"<?php if (get_option('ga_dash_frontend')) echo " checked='checked'"; ?>  /><?php _e(" show page visits and top searches in frontend (after each article)", 'ga-dash' ); ?></p>
		<p><?php _e("Access Level: ", 'ga-dash' ); ?>
		<select id="ga_dash_access_front" name="ga_dash_access_front">
			<option value="manage_options" <?php if (($ga_dash_access_front=="manage_options") OR (!$ga_dash_access_front)) echo "selected='yes'"; echo ">".__("Administrators", 'ga-dash');?></option>
			<option value="edit_pages" <?php if ($ga_dash_access_front=="edit_pages") echo "selected='yes'"; echo ">".__("Editors", 'ga-dash');?></option>
			<option value="publish_posts" <?php if ($ga_dash_access_front=="publish_posts") echo "selected='yes'"; echo ">".__("Authors", 'ga-dash');?></option>
			<option value="edit_posts" <?php if ($ga_dash_access_front=="edit_posts") echo "selected='yes'"; echo ">".__("Contributors", 'ga-dash');?></option>
		</select></p>		
		<?php echo "<h3>" . __( 'Additional Backend Settings', 'ga-dash' ). "</h3>";?>
		<p><input name="ga_dash_map" type="checkbox" id="ga_dash_map" value="1"<?php if (get_option('ga_dash_map')) echo " checked='checked'"; ?>  /><?php _e(" show Geo Map for visits", 'ga-dash' ); ?></p>
		<p><?php echo __("Target Geo Map to region:", 'ga-dash'); ?> <input type="text" style="text-align:center;" name="ga_target_geomap" value="<?php echo $ga_target_geomap; ?>" size="3"> <?php _e("and render top",'ga-dash'); ?> <input type="text" style="text-align:center;" name="ga_target_number" value="<?php echo $ga_target_number; ?>" size="3"> <?php _e("cities (find out more", 'ga-dash') ?> <a href="http://deconf.com/country-codes-for-google-analytics-dashboard/" target="_blank"><?php _e("about this feature", 'ga-dash') ?></a><?php _e(")", 'ga-dash') ?></p>
		<p><input name="ga_dash_traffic" type="checkbox" id="ga_dash_traffic" value="1"<?php if (get_option('ga_dash_traffic')) echo " checked='checked'"; ?>  /><?php _e(" show traffic overview", 'ga-dash' ); ?></p>
		<p><input name="ga_dash_pgd" type="checkbox" id="ga_dash_pgd" value="1"<?php if (get_option('ga_dash_pgd')) echo " checked='checked'"; ?>  /><?php _e(" show top pages", 'ga-dash' ); ?></p>
		<p><input name="ga_dash_rd" type="checkbox" id="ga_dash_rd" value="1"<?php if (get_option('ga_dash_rd')) echo " checked='checked'"; ?>  /><?php _e(" show top referrers", 'ga-dash' ); ?></p>		
		<p><input name="ga_dash_sd" type="checkbox" id="ga_dash_sd" value="1"<?php if (get_option('ga_dash_sd')) echo " checked='checked'"; ?>  /><?php _e(" show top searches", 'ga-dash' ); ?></p>
		<p><?php _e("Access Level: ", 'ga-dash' ); ?>		
		<select id="ga_dash_access_back" name="ga_dash_access_back">
			<option value="manage_options" <?php if (($ga_dash_access_back=="manage_options") OR (!$ga_dash_access_back)) echo "selected='yes'"; echo ">".__("Administrators", 'ga-dash');?></option>
			<option value="edit_pages" <?php if ($ga_dash_access_back=="edit_pages") echo "selected='yes'"; echo ">".__("Editors", 'ga-dash');?></option>
			<option value="publish_posts" <?php if ($ga_dash_access_back=="publish_posts") echo "selected='yes'"; echo ">".__("Authors", 'ga-dash');?></option>
			<option value="edit_posts" <?php if ($ga_dash_access_back=="edit_posts") echo "selected='yes'"; echo ">".__("Contributors", 'ga-dash');?></option>
		</select></p>
		<?php echo "<h3>" . __( 'CSS Settings', 'ga-dash' ). "</h3>";?>		
		<p><?php _e("CSS Look: ", 'ga-dash' ); ?>
		<select id="ga_dash_style" name="ga_dash_style">
			<option value="blue" <?php if (($ga_dash_style=="blue") OR (!$ga_dash_style)) echo "selected='yes'"; echo ">".__("Blue Theme", 'ga-dash');?></option>
			<option value="light" <?php if ($ga_dash_style=="light") echo "selected='yes'"; echo ">".__("Light Theme", 'ga-dash');?></option>
		</select></p>
		<?php echo "<h3>" . __( 'Cache Settings', 'ga-dash' ). "</h3>";?>
		<p><?php _e("Cache Time: ", 'ga-dash' ); ?>
		<select id="ga_dash_cachetime" name="ga_dash_cachetime">
			<option value="900" <?php if ($ga_dash_cachetime=="900") echo "selected='yes'"; echo ">".__("15 minutes", 'ga-dash');?></option>
			<option value="1800" <?php if ($ga_dash_cachetime=="1800") echo "selected='yes'"; echo ">".__("30 minutes", 'ga-dash');?></option>
			<option value="3600" <?php if (($ga_dash_cachetime=="3600") OR (!$ga_dash_cachetime)) echo "selected='yes'"; echo ">".__("1 hour", 'ga-dash');?></option>
			<option value="7200" <?php if ($ga_dash_cachetime=="7200") echo "selected='yes'"; echo ">".__("2 hours", 'ga-dash');?></option>
		</select></p>

		<?php echo "<h3>" . __( 'Google Analytics Tracking', 'ga-dash' ). "</h3>";?>

		<p><?php _e("Enable Tracking: ", 'ga-dash' ); ?>
		<select id="ga_dash_tracking" name="ga_dash_tracking">
			<option value="4" <?php if ($ga_dash_tracking=="4") echo "selected='yes'"; echo ">".__("Disabled", 'ga-dash');?></option>
			<option value="1" <?php if (($ga_dash_tracking=="1") OR (!$ga_dash_tracking)) echo "selected='yes'"; echo ">".__("Single Domain", 'ga-dash');?></option>
			<option value="2" <?php if ($ga_dash_tracking=="2") echo "selected='yes'"; echo ">".__("Domain and Subdomains", 'ga-dash');?></option>
			<option value="3" <?php if ($ga_dash_tracking=="3") echo "selected='yes'"; echo ">".__("Multiple TLD Domains", 'ga-dash');?></option>			
		</select>
		<?php	if ($ga_dash_tracking==4){
				echo ' <font color="red"> &#9668;-- '.__("the tracking feature is currently disabled!",'ga-dash').'</font>';
			}			
		?>
		</p>

		<p><?php _e("Tracking Type: ", 'ga-dash' ); ?>
		<select id="ga_dash_tracking_type" name="ga_dash_tracking_type">
			<option value="classic" <?php if (($ga_dash_tracking_type=="classic") OR (!$ga_dash_tracking_type)) echo "selected='yes'"; echo ">".__("Classic Analytics", 'ga-dash');?></option>
			<option value="universal" <?php if ($ga_dash_tracking_type=="universal") echo "selected='yes'"; echo ">".__("Universal Analytics", 'ga-dash');?></option>
		</select></p>
		<p><?php
			_e("Default Tracking Domain: ", 'ga-dash' );
			$profiles=get_option('ga_dash_profile_list');
			$not_ready=false;
			
			if (!is_array($profiles)){
				$not_ready=true;
			}	
			
			echo '<select id="ga_dash_default_ua" name="ga_dash_default_ua">';
			if (!$not_ready) {
				foreach ($profiles as $items) {
					if (isset($items[2])){
						if (!get_option('ga_dash_default_ua')) {
							update_option('ga_dash_default_ua',$items[2]);
							ga_dash_clear_cache();
						}
						echo '<option value="'.$items[2].'"'; 
						if ((get_option('ga_dash_default_ua')==$items[2])) echo "selected='yes'";
						echo '>'.ga_dash_get_profile_domain($items[3]).'</option>';
					} else {
					
						$not_ready=true;
					
					}	
				}
			}	
			echo '</select>';
			if ($not_ready){
				echo '<font color="red"> &#9668;-- '.__("your profile list needs an update:",'ga-dash').'</font>';
				$adminurl = admin_url("#ga-dash-widget");
				echo ' <a href="'.$adminurl.'">'.__("Click here",'ga-dash').'</a>';
			}	
		?></p>		
		<p><input name="ga_dash_anonim" type="checkbox" id="ga_dash_anonim" value="1"<?php if (get_option('ga_dash_anonim')) echo " checked='checked'"; ?>  /><?php _e(" anonymize IPs while tracking", 'ga-dash' ); ?></p>				
		<p><input name="ga_event_tracking" type="checkbox" id="ga_event_tracking" value="1"<?php if (get_option('ga_event_tracking')) echo " checked='checked'"; ?>  /><?php _e(" track downloads, mailto and outbound links", 'ga-dash' ); ?></p>
		<p><?php echo __("Download Filters:", 'ga-dash'); ?><input type="text" name="ga_event_downloads" value="<?php echo $ga_event_downloads; ?>" size="60"></p>		
		<p><?php _e("Exclude tracking for: ", 'ga-dash' ); ?>
		<select id="ga_track_exclude" name="ga_track_exclude">
			<option value="disabled" <?php if (($ga_track_exclude=="disabled") OR (!$ga_track_exclude)) echo "selected='yes'"; echo ">".__("Disabled", 'ga-dash');?></option>
			<option value="manage_options" <?php if ($ga_track_exclude=="manage_options") echo "selected='yes'"; echo ">".__("Administrators", 'ga-dash');?></option>
			<option value="edit_pages" <?php if ($ga_track_exclude=="edit_pages") echo "selected='yes'"; echo ">".__("Editors", 'ga-dash');?></option>
			<option value="publish_posts" <?php if ($ga_track_exclude=="publish_posts") echo "selected='yes'"; echo ">".__("Authors", 'ga-dash');?></option>
			<option value="edit_posts" <?php if ($ga_track_exclude=="edit_posts") echo "selected='yes'"; echo ">".__("Contributors", 'ga-dash');?></option>
		</select></p>
		<p class="submit">  
        <input type="submit" name="Submit" class="button button-primary" value="<?php _e('Update Options', 'ga-dash' ) ?>" />
        </p>
    </form>  
</div>
<div class="ga-note" style="float:<?php echo $float_note; ?>;text-align:<?php echo $float_main; ?>;"> 
		<center>
			<h3><?php _e("Setup Tutorial",'ga-dash') ?></h3>
			<a href="http://deconf.com/google-analytics-dashboard-wordpress/" target="_blank"><img src="../wp-content/plugins/google-analytics-dashboard-for-wp/img/video-tutorial.png" width="95%" /></a>
		</center>
		<center>
			<br /><h3><?php _e("Support Links",'ga-dash') ?></h3>
		</center>			
		<ul>
			<li><a href="http://deconf.com/google-analytics-dashboard-wordpress/" target="_blank"><?php _e("Google Analytics Dashboard Official Page",'ga-dash') ?></a></li>
			<li><a href="http://wordpress.org/support/plugin/google-analytics-dashboard-for-wp" target="_blank"><?php _e("Google Analytics Dashboard Wordpress Support",'ga-dash') ?></a></li>
			<li><a href="http://forum.deconf.com/wordpress-plugins-f182/" target="_blank"><?php _e("Google Analytics Dashboard on Deconf Forum",'ga-dash') ?></a></li>			
		</ul>
		<center>
			<br /><h3><?php _e("Useful Plugins",'ga-dash') ?></h3>
		</center>			
		<ul>
			<li><a href="http://deconf.com/youtube-analytics-dashboard-wordpress/" target="_blank"><?php _e("YouTube Analytics Dashboard",'ga-dash') ?></a></li>
			<li><a href="http://deconf.com/earnings-dashboard-google-adsense-wordpress/" target="_blank"><?php _e("Earnings Dashboard for Google Adsense™",'ga-dash') ?></a></li>
			<li><a href="http://deconf.com/clicky-analytics-dashboard-wordpress/" target="_blank"><?php _e("Clicky Analytics",'ga-dash') ?></a></li>						
			<li><a href="http://wordpress.org/extend/plugins/follow-us-box/" target="_blank"><?php _e("Follow Us Box",'ga-dash') ?></a></li>			
		</ul>			
</div>
</div>