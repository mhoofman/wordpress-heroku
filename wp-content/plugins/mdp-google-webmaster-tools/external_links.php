<?php include('mdp_css.php'); ?>
<?php
$mdpWebmasterTools = new mdpWebmasterTools();
if ($_POST['get']) {

	$mdpWebmasterTools->mdp_gwt_query_function();

}

?>
<div class="wrap">
	<h2>Links to your Site</h2>

	<p>It is not necessary to always update your links. It will update automatically update every hour by
		the wp_cron().</p>

	<form action="" method="post"><input type="submit" value="Update Links" name="get"
	                                     class="button"></form>
	<table style="width:100%; margin:20px 0 20px 0; border:1px solid #ddd;">
		<tr style="background:#EFEFEF;">
			<td style="width:40%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=domains">Domains</a>
			</td>
			<td style="width:10%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=links">Links</a>
			</td>
			<td style="width:10%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=linked_pages">Linked
					Pages</a></td>
		</tr>
		<?php
		global $wpdb;
		if ($s = $_GET['s']) {

			if (($s == 'links') || ($s == 'linked_pages')) {
				$selection = 'ORDER BY links + 0 DESC';
			} else {
				$selection = 'ORDER BY ' . $s . ' ASC';
			}

			$sql = "SELECT * FROM wp_mdp_gwt_external_links $selection";
		} else {

			$sql = "SELECT * FROM wp_mdp_gwt_external_links
                                            ORDER BY links + 0 DESC
                                        ";
		}
		if ($row_results = $wpdb->get_results($sql)):
			foreach ($row_results as $row_result):?>
				<tr>
					<td style=";padding:5px 5px 5px 5px;"><?php if ($row_result->domains) {
							echo $row_result->domains;
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->links) {
							echo str_replace('-', '<', $row_result->links);
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->linked_pages) {
							echo str_replace('-', '<', $row_result->linked_pages);
						} ?></td>
				</tr>
			<?php endforeach; endif; ?>
	</table>
</div>