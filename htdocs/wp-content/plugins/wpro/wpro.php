<?php
/**
Plugin Name: WP Read-Only
Plugin URI: http://wordpress.org/extend/plugins/wpro/
Description: Plugin for running your Wordpress site without Write Access to the web directory. Amazon S3 is used for uploads/binary storage. This plugin was made with cluster/load balancing server setups in mind - where you do not want your WordPress to write anything to the local web directory.
Version: 1.0
Author: alfreddatakillen
Author URI: http://nurd.nu/
License: GPLv2
 */

// define('WPRO_DEBUG', true);

new WordpressReadOnly;

/* * * * * * * * * * * * * * * * * * * * * * *
  GENERIC FUNCTION FOR NORMALIZING URLS:
* * * * * * * * * * * * * * * * * * * * * * */

class WordpressReadOnlyGeneric {

	public $temporaryLocalData = array();

	function debug($msg) {
		if (defined('WPRO_DEBUG') && WPRO_DEBUG) {
			$fh = fopen('/tmp/wpro-debug', 'a');
			fwrite($fh, trim($msg) . "\n");
			fclose($fh);
		}
	}

	function removeTemporaryLocalData($file) {
		$this->debug('WordpressReadOnlyGeneric::removeTemporaryLocalData("' . $file . '");');
		$this->temporaryLocalData[] = $file;
	}

	function url_normalizer($url) {
		if (strpos($url, '%') !== false) return $url;
		$url = explode('/', $url);
		foreach ($url as $key => $val) $url[$key] = urlencode($val);
		return str_replace('%3A', ':', join('/', $url));
	}

}

/* * * * * * * * * * * * * * * * * * * * * * *
  BACKENDS:
* * * * * * * * * * * * * * * * * * * * * * */

class WordpressReadOnlyBackend extends WordpressReadOnlyGeneric {

	function upload($localpath, $url, $mimetype) {
		return false;
	}

	function file_exists($path) {

		$this->debug('WordpressReadOnlyBackend::file_exists("' . $path . '");');

		$path = $this->url_normalizer($path);

		$this->debug('-> testing url: ' . $path);

		// If at this point, the testing url is not a full http url,
		// then there is something wrong in the wp_upload_dir functionality,
		// because of write permission errors to the system tmp or any thing else.

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_URL, $path);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$result = trim(curl_exec($ch));

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->debug('-> http return code: ' . $httpCode);

		if ($httpCode != 200) return false;

		return true;
	}

}

class WordpressReadOnlyKlandestino extends WordpressReadOnlyBackend {

	public $name;
	public $secret;

	function __construct() {
		$this->name = get_option('wpro-klandestino-name');
		$this->secret = get_option('wpro-klandestino-secret');
	}

	function upload($file, $fullurl, $mime) {

		$this->debug('WordpressReadOnlyKlandestino::upload("' . $file . '", "' . $fullurl . '", "' . $mime . '");');

		$fullurl = $this->url_normalizer($fullurl);

		if (!preg_match('/^http:\/\/([^\/]+)\/(.*)$/', $fullurl, $regs)) return false;
		if (substr($regs[2], 0, strlen($this->name) + 1) != $this->name . '/') return false;

		if (!file_exists($file)) return false;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $fullurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'pw' => $this->secret,
			'upload' => '@' . $file
		));
		$result = trim(curl_exec($ch));

		$this->removeTemporaryLocalData($file);
		if ($result == 'OK') return true;

		echo('ERROR! Klandestino CDN Says: ' . $result . '<br />');

		return false;
	}

}

class WordpressReadOnlyS3 extends WordpressReadOnlyBackend {

	public $key;
	public $secret;
	public $bucket;
	public $endpoint;

	function __construct() {
		$this->key = get_option('wpro-aws-key');
		$this->secret = get_option('wpro-aws-secret');
		$this->bucket = get_option('wpro-aws-bucket');
		$this->endpoint = get_option('wpro-aws-endpoint');
	}

	function upload($file, $fullurl, $mime) {
		$this->debug('WordpressReadOnlyS3::upload("' . $file . '", "' . $fullurl . '", "' . $mime . '");');
		$fullurl = $this->url_normalizer($fullurl);
		if (!preg_match('/^http:\/\/([^\/]+)\/(.*)$/', $fullurl, $regs)) return false;
		$url = $regs[2];

		if (!file_exists($file)) return false;
		$this->removeTemporaryLocalData($file);

		$fin = fopen($file, 'r');
		if (!$fin) return false;

		$fout = fsockopen($this->endpoint, 80, $errno, $errstr, 30);
		if (!$fout) return false;
		$datetime = gmdate('r');
		$string2sign = "PUT\n\n" . $mime . "\n" . $datetime . "\nx-amz-acl:public-read\n/" . $this->bucket . "/" . $url;

		$this->debug('STRING TO SIGN:');
		$this->debug($string2sign);
		$debug = '';
		for ($i = 0; $i < strlen($string2sign); $i++) $debug .= dechex(ord(substr($string2sign, $i, 1))) . ' ';
		$this->debug($debug);

		// Todo: Make this work with php cURL instead of fsockopen/etc..

		$query = "PUT /" . $this->bucket . "/" . $url . " HTTP/1.1\n";
		$query .= "Host: " . $this->endpoint . "\n";
		$query .= "x-amz-acl: public-read\n";
		$query .= "Connection: keep-alive\n";
		$query .= "Content-Type: " . $mime . "\n";
		$query .= "Content-Length: " . filesize($file) . "\n";
		$query .= "Date: " . $datetime . "\n";
		$query .= "Authorization: AWS " . $this->key . ":" . $this->amazon_hmac($string2sign) . "\n\n";

		$this->debug('SEND:');
		$this->debug($query);

		fwrite($fout, $query);
		while (feof($fin) === false) fwrite($fout, fread($fin, 8192));
		fclose($fin);

		// Get the amazon response:
		$this->debug('RECEIVE:');
		$response = '';
		while (!feof($fout)) {
			$data = fgets($fout, 256);
			$this->debug($data);
			$response .= $data;
			if (strpos($response, "\r\n\r\n") !== false) { // Header fully returned.
				$this->debug('ALL RESPONSE HEADERS RECEIVED.');
				if (strpos($response, 'Content-Length: 0') !== false) break; // Return if Content-Length: 0 (and header is fully returned)
				if (substr($response, -7) == "\r\n0\r\n\r\n") break; // Keep-alive responses does not return EOF, they end with this string.
			}
		}
	
		fclose($fout);


		if (strpos($response, '<Error>') !== false) return false;

		return true;
	}

	function amazon_hmac($string) {
		return base64_encode(extension_loaded('hash') ?
		hash_hmac('sha1', $string, $this->secret, true) : pack('H*', sha1(
		(str_pad($this->secret, 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))) .
		pack('H*', sha1((str_pad($this->secret, 64, chr(0x00)) ^
		(str_repeat(chr(0x36), 64))) . $string)))));
	}

}

/* * * * * * * * * * * * * * * * * * * * * * *
  THE MAIN PLUGIN CLASS:
* * * * * * * * * * * * * * * * * * * * * * */

class WordpressReadOnly extends WordpressReadOnlyGeneric {

	public $backend = null;
	public $tempdir = '/tmp';

	function __construct() {
		add_action('admin_init', array($this, 'admin_init')); // Register the settings.
		add_action('admin_menu', array($this, 'admin_menu')); // Will add the settings menu.
		add_filter('wp_handle_upload', array($this, 'handle_upload')); // The very filter that takes care of uploads.
		add_filter('upload_dir', array($this, 'upload_dir')); // Sets the paths and urls for uploads.
		add_filter('wp_generate_attachment_metadata', array($this, 'generate_attachment_metadata')); // We use this filter to store resized versions of the images.
		add_filter('load_image_to_edit_path', array($this, 'load_image_to_edit_path')); // This filter downloads the image to our local temporary directory, prior to editing the image.
		add_filter('wp_save_image_file', array($this, 'save_image_file'), 10, 5); // Store image file.
		add_filter('wp_upload_bits', array($this, 'upload_bits')); // On XMLRPC uploads, files arrives as strings, which we are handling in this filter.
		add_filter('wp_handle_upload_prefilter', array($this, 'handle_upload_prefilter')); // This is where we check for filename dupes (and change them to avoid overwrites).
		add_filter('shutdown', array($this, 'shutdown'));

		switch (get_option('wpro-service')) {
		case 'klandestino':
			$this->backend = new WordpressReadOnlyKlandestino();
			break;
		default:
			$this->backend = new WordpressReadOnlyS3();
		}

		$this->tempdir = sys_get_temp_dir();
		if (substr($this->tempdir, -1) != '/') $this->tempdir = $this->tempdir . '/';

	}

	/* * * * * * * * * * * * * * * * * * * * * * *
	  REGISTER THE SETTINGS:
	* * * * * * * * * * * * * * * * * * * * * * */

	function admin_init() {
		register_setting('wpro-settings-group', 'wpro-service');
		register_setting('wpro-settings-group', 'wpro-folder');
		register_setting('wpro-settings-group', 'wpro-aws-key');
		register_setting('wpro-settings-group', 'wpro-aws-secret');
		register_setting('wpro-settings-group', 'wpro-aws-bucket');
		register_setting('wpro-settings-group', 'wpro-aws-virthost');
		register_setting('wpro-settings-group', 'wpro-aws-endpoint');
		register_setting('wpro-settings-group', 'wpro-klandestino-name');
		register_setting('wpro-settings-group', 'wpro-klandestino-secret');
		add_option('wpro-service');
		add_option('wpro-folder');
		add_option('wpro-aws-key');
		add_option('wpro-aws-secret');
		add_option('wpro-aws-bucket');
		add_option('wpro-aws-virthost');
		add_option('wpro-aws-endpoint');
		add_option('wpro-klandestino-name');
		add_option('wpro-klandestino-secret');
	}


	/* * * * * * * * * * * * * * * * * * * * * * *
	  ADMIN MENU:
	* * * * * * * * * * * * * * * * * * * * * * */

	function admin_menu() {
		add_options_page('WPRO Plugin Settings', 'WPRO Settings', 'manage_options', 'wpro', array($this, 'admin_form'));
	}
	function admin_form() {
		if (!current_user_can ('manage_options'))  {
			wp_die ( __ ('You do not have sufficient permissions to access this page.'));
		}

			$wproService = get_option('wpro-service');

		?>
			<script language="JavaScript">
				(function($) {
					$(document).ready(function() {
						$('#wpro-service-s3').change(function() {
							$('.wpro-service-div:visible').slideUp(function() {
								$('#wpro-service-s3-div').slideDown();
							});
						});
						$('#wpro-service-klandestino').change(function() {
							$('.wpro-service-div:visible').slideUp(function() {
								$('#wpro-service-klandestino-div').slideDown();
							});
						});
					});
				})(jQuery);
			</script>
			<div class="wrap">
				<div id="icon-plugins" class="icon32"><br /></div>
				<h2>WP Read-Only (WPRO)</h2>
				<form name="wpro-settings-form" action="options.php" method="post">
					<?php settings_fields ('wpro-settings-group'); ?>
					<h3><?php echo __('Common Settings'); ?></h3>
					<table class="form-table">
						<tr>
							<th><label>Storage Service</label></th>
							<td>
								<input name="wpro-service" id="wpro-service-s3" type="radio" value="s3" <?php if ($wproService != 'klandestino') echo ('checked="checked"'); ?>/> <label for="wpro-service-s3">Amazon S3</label><br />
<!--								<input name="wpro-service" id="wpro-service-klandestino" type="radio" value="klandestino" <?php if ($wproService == 'klandestino') echo ('checked="checked"'); ?>/> <label for="wpro-service-klandestino">Klandestino CDN</label><br />-->
							</td>
						</tr>
						<tr>
							<th><label for="wpro-folder">Prepend all paths with folder</th>
							<td><input name="wpro-folder" id="wpro-folder" type="text" value="<?php echo(get_option('wpro-folder')); ?>" class="regular-text code" /></td>
						</tr>
					</table>
					<div class="wpro-service-div" id="wpro-service-s3-div" <?php if ($wproService == 'klandestino') echo ('style="display:none"'); ?> >
						<h3><?php echo __('Amazon S3 Settings'); ?></h3>
						<table class="form-table">
							<tr>
								<th><label for="wpro-aws-key">AWS Key</label></th> 
								<td><input name="wpro-aws-key" id="wpro-aws-key" type="text" value="<?php echo get_option('wpro-aws-key'); ?>" class="regular-text code" /></td>
							</tr>
							<tr>
								<th><label for="wpro-aws-secret">AWS Secret</label></th> 
								<td><input name="wpro-aws-secret" id="wpro-aws-secret" type="text" value="<?php echo get_option('wpro-aws-secret'); ?>" class="regular-text code" /></td>
							</tr>
							<tr>
								<th><label for="wpro-aws-bucket">S3 Bucket</label></th> 
								<td>
									<input name="wpro-aws-bucket" id="wpro-aws-bucket" type="text" value="<?php echo get_option('wpro-aws-bucket'); ?>" class="regular-text code" /><br />
									<input name="wpro-aws-virthost" id="wpro-aws-virthost" type="checkbox" value="1"  <?php if (get_option('wpro-aws-virthost')) echo('checked="checked"'); ?> /> Virtual hosting is enabled for this bucket.
								</td>
							</tr>
							<tr>
								<th><label for="wpro-aws-endpoint">Bucket AWS Region</label></th> 
								<td>
									<select name="wpro-aws-endpoint" id="wpro-aws-endpoint">
										<?php
											$aws_regions = array(
												's3.amazonaws.com' => 'US East Region (Standard)',
												's3-us-west-2.amazonaws.com' => 'US West (Oregon) Region',
												's3-us-west-1.amazonaws.com' => 'US West (Northern California) Region',
												's3-eu-west-1.amazonaws.com' => 'EU (Ireland) Region',
												's3-ap-southeast-1.amazonaws.com' => 'Asia Pacific (Singapore) Region',
												's3-ap-northeast-1.amazonaws.com' => 'Asia Pacific (Tokyo) Region',
												's3-sa-east-1.amazonaws.com' => 'South America (Sao Paulo) Region'
											);
											// Endpoints comes from http://docs.amazonwebservices.com/general/latest/gr/rande.html

											foreach ($aws_regions as $endpoint => $endpoint_name) {
												echo ('<option value="' . $endpoint . '"');
												if ($endpoint == get_option('wpro-aws-endpoint')) {
													echo(' selected="selected"');
												}
												echo ('>' . $endpoint_name . '</option>');
											}
										?>
									</select> 
								</td>
							</tr>
						</table>
					</div>
					<div class="wpro-service-div" id="wpro-service-klandestino-div" <?php if ($wproService != 'klandestino') echo ('style="display:none"'); ?> >
						<h3><?php echo __('Klandestino CDN Settings'); ?></h3>
						<table class="form-table">
							<tr>
								<th><label for="wpro-klandestino-name">Klandestino CDN Name</label></th> 
								<td><input name="wpro-klandestino-name" id="wpro-klandestino-name" type="text" value="<?php echo get_option('wpro-klandestino-name'); ?>" class="regular-text code" /></td>
							</tr>
							<tr>
								<th><label for="wpro-klandestino-secret">Klandestino CDN Secret</label></th> 
								<td><input name="wpro-klandestino-secret" id="wpro-klandestino-secret" type="text" value="<?php echo get_option('wpro-klandestino-secret'); ?>" class="regular-text code" /></td>
							</tr>
						</table>
					</div>
					<p class="submit"> 
						<input type="submit" name="submit" class="button-primary" value="<?php echo __('Save Changes'); ?>" /> 
					</p>
				</form>
			</div>
		<?php
	}

	/* * * * * * * * * * * * * * * * * * * * * * *
	  TAKING CARE OF UPLOADS:
	* * * * * * * * * * * * * * * * * * * * * * */

	function handle_upload($data) {

		$this->debug('WordpressReadOnly::handle_upload($data);');
		$this->debug('-> $data = ');
		$this->debug(print_r($data, true));

		$data['url'] = $this->url_normalizer($data['url']);

		if (!file_exists($data['file'])) return false;

		$response = $this->backend->upload($data['file'], $data['url'], $data['type']);
		if (!$response) return false;

		return $data;
	}

	public $upload_basedir = ''; // Variable for caching in the upload_dir()-method
	function upload_dir($data) {
//		$this->debug('WordpressReadOnly::upload_dir($data);');
//		$this->debug('-> $data = ');
//		$this->debug(print_r($data, true));

		if ($this->upload_basedir == '') {
			$this->upload_basedir = $this->tempdir . 'wpro' . time() . rand(0, 999999);
			while (is_dir($this->upload_basedir)) $this->upload_basedir = $this->tempdir . 'wpro' . time() . rand(0, 999999);
		}
		$data['basedir'] = $this->upload_basedir;
		switch (get_option('wpro-service')) {
		case 'klandestino':
			$data['baseurl'] = 'http://' . trim(str_replace('//', '/', 'cdn.klandestino.se/' . get_option('wpro-klandestino-name') . '/' . trim(get_option('wpro-folder'))), '/');
			break;
		default:
			if (get_option('wpro-aws-virthost')) {
				$data['baseurl'] = 'http://' . trim(str_replace('//', '/', get_option('wpro-aws-bucket') . '/' . trim(get_option('wpro-folder'))), '/');
			} else {
				$data['baseurl'] = 'http://' . trim(str_replace('//', '/', get_option('wpro-aws-bucket') . '.s3.amazonaws.com/' . trim(get_option('wpro-folder'))), '/');
			}
		}
		$data['path'] = $this->upload_basedir . $data['subdir'];
		$data['url'] = $data['baseurl'] . $data['subdir'];

//		$this->debug('-> RETURNS = ');
//		$this->debug(print_r($data, true));

		return $data;
	}

	function generate_attachment_metadata($data) {
		if (!is_array($data) || !isset($data['sizes']) || !is_array($data['sizes'])) return $data;

		$upload_dir = wp_upload_dir();
		$filepath = $upload_dir['basedir'] . '/' . preg_replace('/^(.+)\/[^\/]+$/', '\\1', $data['file']);
		foreach ($data['sizes'] as $size => $sizedata) {
			$file = $filepath . '/' . $sizedata['file'];
			$url = $upload_dir['baseurl'] . substr($file, strlen($upload_dir['basedir']));

			$mime = 'application/octet-stream';
			switch(substr($file, -4)) {
				case '.gif':
					$mime = 'image/gif';
					break;
				case '.jpg':
					$mime = 'image/jpeg';
					break;
				case '.png':
					$mime = 'image/png';
					break;
			}

			$this->backend->upload($file, $url, $mime);
		}

		return $data;
	}

	function load_image_to_edit_path($filepath) {

		$this->debug('WordpressReadOnly::load_image_to_edit_path("' . $filepath . '");');

		if (substr($filepath, 0, 7) == 'http://') {

			$ending = '';
			if (preg_match('/\.([^\.\/]+)$/', $filepath, $regs)) $ending = '.' . $regs[1];

			$tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;
			while (file_exists($tmpfile)) $tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;

			$filepath = $this->url_normalizer($filepath);

			$this->debug('-> Loading file from: ' . $filepath);
			$this->debug('-> Storing file at: ' . $tmpfile);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $filepath);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);

			$fh = fopen($tmpfile, 'w');
			fwrite($fh, curl_exec($ch));
			fclose($fh);

			$this->removeTemporaryLocalData($tmpfile);

			return $tmpfile;

		}
		return $filepath;
	}

	function save_image_file($dummy, $filename, $image, $mime_type, $post_id) {

		$this->debug('WordpressReadOnly::save_image_file("' . $dummy . '", "' . $filename . '", "' . $image . '", "' . $mime_type . '", "' . $post_id . '");');

		if (substr($filename, 0, strlen($this->tempdir)) != $this->tempdir) return false;
		$filename = substr($filename, strlen($this->tempdir));
		if (!preg_match('/^wpro[0-9]+(\/.+)$/', $filename, $regs)) return false;

		$filename = $regs[1];

		$tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999);
		while (file_exists($tmpfile)) $tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999);
		$this->debug('-> Storing image as temporary file: ' . $tmpfile);

		switch ($mime_type) {
			case 'image/jpeg':
				imagejpeg($image, $tmpfile, apply_filters('jpeg_quality', 90, 'edit_image'));
				break;
			case 'image/png':
				imagepng($image, $tmpfile);
				break;
			case 'image/gif':
				imagegif($image, $tmpfile);
				break;
			default:
				return false;
		}

		$upload = wp_upload_dir();
		$url = $upload['baseurl'];
		if (substr($url, -1) != '/') $url .= '/';
		while (substr($filename, 0, 1) == '/') $filename = substr($filename, 1);
		$url .= $filename;

		return $this->backend->upload($tmpfile, $this->url_normalizer($url), $mime_type);
	}

	function upload_bits($data) {

		$this->debug('WordpressReadOnly::upload_bits($data);');
		$this->debug('-> $data = ');
		$this->debug(print_r($data, true));

		$ending = '';
		if (preg_match('/\.([^\.\/]+)$/', $data['name'], $regs)) $ending = '.' . $regs[1];

		$tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;
		while (file_exists($tmpfile)) $tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;

		$fh = fopen($tmpfile, 'wb');
		fwrite($fh, $data['bits']);
		fclose($fh);

		$upload = wp_upload_dir();

		return array(
			'file' => $tmpfile,
			'url' => $this->url_normalizer($upload['url'] . '/' . $data['name']),
			'error' => false
		);
	}

	// Handle duplicate filenames:
	// Wordpress never calls the wp_handle_upload_overrides filter properly, so we do not have any good way of setting a callback for wp_unique_filename_callback, which would be the most beautiful way of doing this. So, instead we are usting the wp_handle_upload_prefilter to check for duplicates and rename the files...
	function handle_upload_prefilter($file) {

		$this->debug('WordpressReadOnly::handle_upload_prefilter($file);');
		$this->debug('-> $file = ');
		$this->debug(print_r($file, true));

		$upload = wp_upload_dir();

		$name = $file['name'];
		$path = trim($upload['url'], '/') . '/' . $name;

		$counter = 0;
		while ($this->backend->file_exists($path)) {
			if (preg_match('/\.([^\.\/]+)$/', $file['name'], $regs)) {
				$ending = '.' . $regs[1];
				$preending = substr($file['name'], 0, 0 - strlen($ending));
				$name = $preending . '_' . $counter . $ending;
			} else {
				$name = $file['name'] . '_' . $counter;
			}
			$path = trim($upload['url'], '/') . '/' . $name;
			$counter++;
		}

		$file['name'] = $name;

		return $file;
	}

	function shutdown() {
		$this->debug('WordpressReadOnly::shutdown()');

		$this->temporaryLocalData = array_merge($this->temporaryLocalData, $this->backend->temporaryLocalData);

		$this->debug('-> $this->temporaryLocalData = ');
		$this->debug(print_r($this->temporaryLocalData, true));

		$tempdir = sys_get_temp_dir();
		if (substr($tempdir, -1) == '/') $tempdir = substr($tempdir, 0, -1);

		foreach ($this->temporaryLocalData as $file) {

			if (substr($file, 0, strlen($tempdir) + 1) == $tempdir . '/') {

				while ($file != $tempdir) {
					if (substr($file, -1) == '/') {
						$file = substr($file, 0, -1);
					} else {
						if (is_file($file)) {
							if (@unlink($file) == false) break;
							$this->debug('-> Removed file: ' . $file);
						} else if (is_dir($file)) {
							if (@rmdir($file) == false) break;
							$this->debug('-> Removed directory: ' . $file);
						} else {
							break;
						}
						if (preg_match('/^(.+)\/[^\/]+$/', $file, $regs)) {
							$file = $regs[1];
						} else {
							break;
						}
					}
				}
			}
		}
	}

}
