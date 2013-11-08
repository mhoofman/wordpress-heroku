<?php include('mdp_css.php'); ?>
<?php screen_icon(); ?>
<div class="wrap">
	<div class="right_block">
		<?php include('mdp_contact.php'); ?>
	</div>
	<div class="left_block">
		<h2>Webmaster Tools</h2>

		<form action="options.php" method="post" class="mdp_form">
			<?php settings_fields($plugin_id . '_options'); ?>
			<table class="mdp_table">
				<tr>
					<td>Google Webmaster Email:</td>
					<td><input type="text" name="mdp_gwt_email"
					           value="<?php echo get_option('mdp_gwt_email'); ?>"/>
					</td>
					<td></td>
				</tr>
				<tr>
					<td>Google Webmaster Password:</td>
					<td><input type="password" name="mdp_gwt_password"
					           value="<?php echo get_option('mdp_gwt_password'); ?>"
					</td>
					<td></td>
				</tr>
				<tr>
					<td colspan="3"><?php submit_button(); ?></td>
				</tr>
			</table>
		</form>
	</div>
</div>