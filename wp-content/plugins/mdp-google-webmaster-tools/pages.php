<?php include('mdp_css.php'); ?>
<?php
$mdpWebmasterTools = new mdpWebmasterTools();
if ($_POST['get']) {

	$mdpWebmasterTools->mdp_gwt_query_function();

}
?>
<div class="wrap">
	<h2>Top Pages</h2>

	<p>It is not necessary to always update your queries. It will update automatically update every hour by
		the wp_cron().</p>

	<form action="" method="post"><input type="submit" value="Update Top Pages" name="get"
	                                     class="button"></form>
	<table style="width:100%; margin:20px 0 20px 0; border:1px solid #ddd;">
		<tr style="background:#EFEFEF;">
			<td style="width:40%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $_SERVER['REQUEST_URI']; ?>&s=query">Query</a></td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=impressions">Impressions</a>
			</td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=impressions_change">Impressions
					Change</a></td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=clicks">Clicks</a>
			</td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=clicks_change">Clicks
					Change</a></td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=ctr">CTR</a>
			</td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=ctr_change">CTR
					Change</a></td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=avg_position">Avg.
					Position</a></td>
			<td style="width:5%;font-size:13px; padding:5px 2px 5px 2px;"><a
					href="<?php echo $mdpWebmasterTools->full_url($_SERVER['REQUEST_URI']); ?>&s=avg_position_change">Avg.
					Position Change</a></td>
		</tr>
		<?php
		global $wpdb;

		if ($s = $_GET['s']) {

			if ($s == 'avg_position') {
				$selection = 'ORDER BY length(avg_position), CAST(avg_position AS DECIMAL) ASC, avg_position + 0 ASC';
			} elseif ($s == 'impressions') {
				$selection = 'ORDER BY impressions + 0 DESC';
			} elseif ($s == 'query') {
				$selection = 'ORDER BY ' . $s . ' ASC';
			} else {
				$selection = 'ORDER BY ' . $s . ' DESC';
			}

			$sql = "SELECT * FROM wp_mdp_gwt_pages $selection";
		} else {

			$sql = "SELECT * FROM wp_mdp_gwt_pages
                                            ORDER BY impressions + 0 DESC
                                        ";
		}

		if ($row_results = $wpdb->get_results($sql)):
			foreach ($row_results as $row_result):?>
				<tr>
					<td style=";padding:5px 5px 5px 5px;"><?php if ($row_result->query) {
							echo $row_result->query;
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->impressions) {
							echo str_replace('-', '<', $row_result->impressions);
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->impressions_change) {
							echo $row_result->impressions_change . '%';
						} ?></td>
					<td style=";padding:5px 5px 5px 5px;"><?php if ($row_result->clicks) {
							echo str_replace('-', '<', $row_result->clicks);
						} ?></td>
					<td style=";padding:5px 5px 5px 5px;"><?php if ($row_result->clicks_change) {
							echo $row_result->clicks_change . '%';
						} ?></td>
					<td style=";padding:5px 5px 5px 5px;"><?php if ($row_result->ctr) {
							echo str_replace('-', '', $row_result->ctr) . '%';
						} ?></td>
					<td style=";padding:5px 5px 5px 5px;"><?php if ($row_result->ctr_change) {
							echo $row_result->ctr_change . '%';
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->avg_position) {
							echo str_replace('-', '<', $row_result->avg_position);
						} ?></td>
					<td style="padding:5px 5px 5px 5px;"><?php if ($row_result->avg_position_change) {
							echo $row_result->avg_position_change . '%';
						} ?></td>
				</tr>
			<?php endforeach; endif; ?>
	</table>
</div>