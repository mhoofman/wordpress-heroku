<?php include('mdp_css.php'); ?>
<?php
$mdpWebmasterTools = new mdpWebmasterTools();
if ($_POST['get']) {

	$mdpWebmasterTools->mdp_gwt_query_function();

}

?>
<div class="wrap">
	<h2>Keywords</h2>

	<p>It is not necessary to always update your keywords. It will update automatically update every hour by
		the wp_cron().</p>

	<form action="" method="post"><input type="submit" value="Update Keywords" name="get"
	                                     class="button"></form>
	<table style="width:100%; margin:20px 0 20px 0; border:1px solid #ddd;">
		<tr style="background:#EFEFEF;">
			<td style="width:15%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=query">Keyword</a>
			</td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=occurrences">Impressions</a>
			</td>
			<td style="width:10%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=variants_encountered">Variants
					Encountered</a></td>
			<td style="width:50%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=top_urls">Top
					Urls</a></td>
		</tr>
		<?php
		global $wpdb;
		if ($s = $_GET['s']) {

			if ($s == 'occurrences') {
				$selection = 'ORDER BY occurrences + 0 DESC';
			} else {
				$selection = 'ORDER BY ' . $s . ' ASC';
			}

			$sql = "SELECT * FROM wp_mdp_gwt_keywords $selection";
		} else {

			$sql = "SELECT * FROM wp_mdp_gwt_keywords
                                            ORDER BY occurrences + 0 DESC
                                        ";
		}
		if ($row_results = $wpdb->get_results($sql)):
			foreach ($row_results as $row_result):?>
				<tr>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->query) {
							echo $row_result->query;
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->occurrences) {
							echo str_replace('-', '<', $row_result->occurrences);
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->variants_encountered) {
							echo $row_result->variants_encountered;
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->top_urls) {
							echo $mdpWebmasterTools->listUrls($row_result->top_urls);
						} ?></td>
				</tr>
			<?php endforeach; endif; ?>
	</table>
</div>