<?php

/**
 * WordPress AtomPub API implementation.
 *
 * @package WordPress
 * @subpackage Publishing
 * @since 2.2.0
 */
class wp_atom_server {

	/**
	 * ATOM content type.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	var $ATOM_CONTENT_TYPE = 'application/atom+xml';

	/**
	 * Categories ATOM content type.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	var $CATEGORIES_CONTENT_TYPE = 'application/atomcat+xml';

	/**
	 * Service ATOM content type.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	var $SERVICE_CONTENT_TYPE = 'application/atomsvc+xml';

	/**
	 * ATOM XML namespace.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	var $ATOM_NS = 'http://www.w3.org/2005/Atom';

	/**
	 * ATOMPUB XML namespace.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	var $ATOMPUB_NS = 'http://www.w3.org/2007/app';

	/**
	 * Entries path.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	var $ENTRIES_PATH = "posts";

	/**
	 * Categories path.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	var $CATEGORIES_PATH = "categories";

	/**
	 * Media path.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	var $MEDIA_PATH = "attachments";

	/**
	 * Entry path.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	var $ENTRY_PATH = "post";

	/**
	 * Service path.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	var $SERVICE_PATH = "service";

	/**
	 * Media single path.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	var $MEDIA_SINGLE_PATH = "attachment";

	/**
	 * ATOMPUB parameters.
	 *
	 * @since 2.2.0
	 * @var array
	 */
	var $params = array();

	/**
	 * Supported ATOMPUB media types.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	var $media_content_types = array('image/*','audio/*','video/*');

	/**
	 * ATOMPUB content type(s).
	 *
	 * @since 2.2.0
	 * @var array
	 */
	var $atom_content_types = array('application/atom+xml');

	/**
	 * ATOMPUB methods.
	 *
	 * @since 2.2.0
	 * @var unknown_type
	 */
	var $selectors = array();

	/**
	 * Whether to do output.
	 *
	 * Support for head.
	 *
	 * @since 2.2.0
	 * @var bool
	 */
	var $do_output = true;

	/**
	 * Constructor - Sets up object properties.
	 *
	 * @since 2.2.0
	 * @return AtomServer
	 */
	function __construct() {

		$var_by_ref = explode( '/', $_SERVER['SCRIPT_NAME'] );
		$this->script_name = array_pop( $var_by_ref );
		$this->app_base = site_url( $this->script_name . '/' );

		$this->selectors = array(
			'@/service$@' =>
				array('GET' => 'get_service'),
			'@/categories$@' =>
				array('GET' => 'get_categories_xml'),
			'@/post/(\d+)$@' =>
				array('GET' => 'get_post',
						'PUT' => 'put_post',
						'DELETE' => 'delete_post'),
			'@/posts/?(\d+)?$@' =>
				array('GET' => 'get_posts',
						'POST' => 'create_post'),
			'@/attachments/?(\d+)?$@' =>
				array('GET' => 'get_attachment',
						'POST' => 'create_attachment'),
			'@/attachment/file/(\d+)$@' =>
				array('GET' => 'get_file',
						'PUT' => 'put_file',
						'DELETE' => 'delete_file'),
			'@/attachment/(\d+)$@' =>
				array('GET' => 'get_attachment',
						'PUT' => 'put_attachment',
						'DELETE' => 'delete_attachment'),
		);
	}

	/**
	 * Handle ATOMPUB request.
	 *
	 * @since 2.2.0
	 */
	function handle_request() {

		if ( !empty( $_SERVER['ORIG_PATH_INFO'] ) )
			$path = $_SERVER['ORIG_PATH_INFO'];
		else
			$path = $_SERVER['PATH_INFO'];

		$method = $_SERVER['REQUEST_METHOD'];

		$this->process_conditionals();
		//$this->process_conditionals();

		// exception case for HEAD (treat exactly as GET, but don't output)
		if ($method == 'HEAD') {
			$this->do_output = false;
			$method = 'GET';
		}

		// redirect to /service in case no path is found.
		if (strlen($path) == 0 || $path == '/')
			$this->redirect($this->get_service_url());

		// check to see if AtomPub is enabled
		if ( !get_option( 'enable_app' ) )
			$this->forbidden( sprintf( __( 'AtomPub services are disabled on this site. An admin user can enable them at %s' ), admin_url('options-writing.php') ) );

		// dispatch
		foreach ( $this->selectors as $regex => $funcs ) {
			if ( preg_match($regex, $path, $matches) ) {
				if ( isset($funcs[$method]) ) {

					// authenticate regardless of the operation and set the current
					// user. each handler will decide if auth is required or not.
					if ( !$this->authenticate() ) {
						$this->auth_required('Credentials required.');
					}

					array_shift($matches);
					call_user_func_array(array($this,$funcs[$method]), $matches);
					wp_die();
				} else {
					// only allow what we have handlers for...
					$this->not_allowed(array_keys($funcs));
				}
			}
		}

		// oops, nothing found
		$this->not_found();
	}

	/**
	 * Retrieve XML for ATOMPUB service.
	 *
	 * @since 2.2.0
	 */
	function get_service() {

		if ( !current_user_can( 'edit_posts' ) )
			$this->auth_required( __( 'Sorry, you do not have the right to access this site.' ) );

		$entries_url = esc_attr($this->get_entries_url());
		$categories_url = esc_attr($this->get_categories_url());
		$media_url = esc_attr($this->get_attachments_url());
		$accepted_media_types = '';
		foreach ($this->media_content_types as $med) {
			$accepted_media_types = $accepted_media_types . "<accept>" . $med . "</accept>";
		}
		$atom_prefix="atom";
		$atom_blogname = get_bloginfo('name');
		$service_doc = <<<EOD
<service xmlns="$this->ATOMPUB_NS" xmlns:$atom_prefix="$this->ATOM_NS">
  <workspace>
    <$atom_prefix:title>$atom_blogname Workspace</$atom_prefix:title>
    <collection href="$entries_url">
      <$atom_prefix:title>$atom_blogname Posts</$atom_prefix:title>
      <accept>$this->ATOM_CONTENT_TYPE;type=entry</accept>
      <categories href="$categories_url" />
    </collection>
    <collection href="$media_url">
      <$atom_prefix:title>$atom_blogname Media</$atom_prefix:title>
      $accepted_media_types
    </collection>
  </workspace>
</service>

EOD;

		$this->output($service_doc, $this->SERVICE_CONTENT_TYPE);
	}

	/**
	 * Retrieve categories list in XML format.
	 *
	 * @since 2.2.0
	 */
	function get_categories_xml() {

		if ( !current_user_can( 'edit_posts' ) )
			$this->auth_required( __( 'Sorry, you do not have the right to access this site.' ) );

		$home = esc_attr(get_bloginfo_rss('url'));

		$categories = "";
		$cats = get_categories(array('hierarchical' => 0, 'hide_empty' => 0));
		foreach ( (array) $cats as $cat ) {
			$categories .= "    <category term=\"" . esc_attr($cat->name) . "\" />\n";
		}
		$output = <<<EOD
<app:categories xmlns:app="$this->ATOMPUB_NS"
	xmlns="$this->ATOM_NS"
	fixed="yes" scheme="$home">
	$categories
</app:categories>
EOD;
		$this->output($output, $this->CATEGORIES_CONTENT_TYPE);
	}

	/**
	 * Create new post.
	 *
	 * @since 2.2.0
	 */
	function create_post() {
		global $user_ID;
		$this->get_accepted_content_type($this->atom_content_types);

		$parser = new AtomParser();
		if ( !$parser->parse() )
			$this->client_error();

		$entry = array_pop($parser->feed->entries);

		$catnames = array();
		if ( !empty( $entry->categories ) ) {
			foreach ( $entry->categories as $cat ) {
				array_push($catnames, $cat["term"]);
			}
		}

		$wp_cats = get_categories(array('hide_empty' => false));

		$post_category = array();

		foreach ( $wp_cats as $cat ) {
			if ( in_array($cat->name, $catnames) )
				array_push($post_category, $cat->term_id);
		}

		$publish = ! ( isset( $entry->draft ) && 'yes' == trim( $entry->draft ) );

		$cap = ($publish) ? 'publish_posts' : 'edit_posts';

		if ( !current_user_can($cap) )
			$this->auth_required(__('Sorry, you do not have the right to edit/publish new posts.'));

		$blog_ID = get_current_blog_id();
		$post_status = ($publish) ? 'publish' : 'draft';
		$post_author = (int) $user_ID;

		$post_title   = '';
		$post_content = '';
		$post_excerpt = '';
		$pubtimes     = '';

		if ( isset( $entry->title ) && is_array( $entry->title ) && !empty( $entry->title[1] ) )
			$post_title = (string) $entry->title[1];
		if ( isset( $entry->content ) && is_array( $entry->content ) && !empty( $entry->content[1] ) )
			$post_content = (string) $entry->content[1];
		if ( isset( $entry->summary ) && is_array( $entry->summary ) && !empty( $entry->summary[1] ) )
			$post_excerpt = (string) $entry->summary[1];
		if ( !empty( $entry->published ) )
			$pubtimes = (string) $entry->published;

		$pubtimes = $this->get_publish_time( $pubtimes );

		$post_date = $pubtimes[0];
		$post_date_gmt = $pubtimes[1];

		if ( isset( $_SERVER['HTTP_SLUG'] ) )
			$post_name = $_SERVER['HTTP_SLUG'];

		$post_data = compact('blog_ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_category', 'post_status', 'post_excerpt', 'post_name');

		$this->escape($post_data);

		$postID = wp_insert_post($post_data);
		if ( is_wp_error( $postID ) )
			$this->internal_error($postID->get_error_message());

		if ( !$postID )
			$this->internal_error(__('Sorry, your entry could not be posted. Something wrong happened.'));

		// getting warning here about unable to set headers
		// because something in the cache is printing to the buffer
		// could we clean up wp_set_post_categories or cache to not print
		// this could affect our ability to send back the right headers
		@wp_set_post_categories($postID, $post_category);

		do_action( 'atompub_create_post', $postID, $entry );

		$output = $this->get_entry($postID);

		$this->created($postID, $output);
	}

	/**
	 * Retrieve post.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function get_post($postID) {
		global $entry;

		if ( !current_user_can( 'edit_post', $postID ) )
			$this->auth_required( __( 'Sorry, you do not have the right to access this post.' ) );

		$this->set_current_entry($postID);
		$output = $this->get_entry($postID);
		$this->output($output);

	}

	/**
	 * Update post.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function put_post($postID) {
		// checked for valid content-types (atom+xml)
		// quick check and exit
		$this->get_accepted_content_type($this->atom_content_types);

		$parser = new AtomParser();
		if ( !$parser->parse() )
			$this->bad_request();

		$parsed = array_pop($parser->feed->entries);

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		if ( !current_user_can('edit_post', $entry['ID']) )
			$this->auth_required(__('Sorry, you do not have the right to edit this post.'));

		$publish = ! ( isset($parsed->draft) && 'yes' == trim($parsed->draft) );
		$post_status = ($publish) ? 'publish' : 'draft';

		extract($entry);

		$post_title = $parsed->title[1];
		$post_content = $parsed->content[1];
		$post_excerpt = $parsed->summary[1];
		$pubtimes = $this->get_publish_time($entry->published);
		$post_date = $pubtimes[0];
		$post_date_gmt = $pubtimes[1];
		$pubtimes = $this->get_publish_time($parsed->updated);
		$post_modified = $pubtimes[0];
		$post_modified_gmt = $pubtimes[1];

		$postdata = compact('ID', 'post_content', 'post_title', 'post_category', 'post_status', 'post_excerpt', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt');
		$this->escape($postdata);

		$result = wp_update_post($postdata);

		if ( !$result )
			$this->internal_error(__('For some strange yet very annoying reason, this post could not be edited.'));

		do_action( 'atompub_put_post', $ID, $parsed );

		$this->ok();
	}

	/**
	 * Remove post.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function delete_post($postID) {

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		if ( !current_user_can('edit_post', $postID) )
			$this->auth_required(__('Sorry, you do not have the right to delete this post.'));

		if ( $entry['post_type'] == 'attachment' ) {
			$this->delete_attachment($postID);
		} else {
			$result = wp_delete_post($postID);

			if ( !$result ) {
				$this->internal_error(__('For some strange yet very annoying reason, this post could not be deleted.'));
			}

			$this->ok();
		}

	}

	/**
	 * Retrieve attachment.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Optional. Post ID.
	 */
	function get_attachment($postID = null) {
		if ( !current_user_can( 'upload_files' ) )
			$this->auth_required( __( 'You do not have permission to upload files.' ) );

		if ( !isset($postID) ) {
			$this->get_attachments();
		} else {
			$this->set_current_entry($postID);
			$output = $this->get_entry($postID, 'attachment');
			$this->output($output);
		}
	}

	/**
	 * Create new attachment.
	 *
	 * @since 2.2.0
	 */
	function create_attachment() {

		$type = $this->get_accepted_content_type();

		if ( !current_user_can('upload_files') )
			$this->auth_required( __( 'You do not have permission to upload files.' ) );

		$fp = fopen("php://input", "rb");
		$bits = null;
		while ( !feof($fp) ) {
			$bits .= fread($fp, 4096);
		}
		fclose($fp);

		$slug = '';
		if ( isset( $_SERVER['HTTP_SLUG'] ) )
			$slug = $_SERVER['HTTP_SLUG'];
		elseif ( isset( $_SERVER['HTTP_TITLE'] ) )
			$slug = $_SERVER['HTTP_TITLE'];
		elseif ( empty( $slug ) ) // just make a random name
			$slug = substr( md5( uniqid( microtime() ) ), 0, 7);
		$ext = preg_replace( '|.*/([a-z0-9]+)|', '$1', $_SERVER['CONTENT_TYPE'] );
		$slug = sanitize_file_name( "$slug.$ext" );
		$file = wp_upload_bits( $slug, null, $bits);

		$url = $file['url'];
		$file = $file['file'];

		do_action('wp_create_file_in_uploads', $file); // replicate

		// Construct the attachment array
		$attachment = array(
			'post_title' => $slug,
			'post_content' => $slug,
			'post_status' => 'attachment',
			'post_parent' => 0,
			'post_mime_type' => $type,
			'guid' => $url
			);

		// Save the data
		$postID = wp_insert_attachment($attachment, $file);

		if (!$postID)
			$this->internal_error(__('Sorry, your entry could not be posted. Something wrong happened.'));

		$output = $this->get_entry($postID, 'attachment');

		$this->created($postID, $output, 'attachment');
	}

	/**
	 * Update attachment.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function put_attachment($postID) {
		// checked for valid content-types (atom+xml)
		// quick check and exit
		$this->get_accepted_content_type($this->atom_content_types);

		$parser = new AtomParser();
		if (!$parser->parse()) {
			$this->bad_request();
		}

		$parsed = array_pop($parser->feed->entries);

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		if ( !current_user_can('edit_post', $entry['ID']) )
			$this->auth_required(__('Sorry, you do not have the right to edit this post.'));

		extract($entry);

		$post_title = $parsed->title[1];
		$post_content = $parsed->summary[1];
		$pubtimes = $this->get_publish_time($parsed->updated);
		$post_modified = $pubtimes[0];
		$post_modified_gmt = $pubtimes[1];

		$postdata = compact('ID', 'post_content', 'post_title', 'post_category', 'post_status', 'post_excerpt', 'post_modified', 'post_modified_gmt');
		$this->escape($postdata);

		$result = wp_update_post($postdata);

		if ( !$result )
			$this->internal_error(__('For some strange yet very annoying reason, this post could not be edited.'));

		$this->ok();
	}

	/**
	 * Remove attachment.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function delete_attachment($postID) {

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		if ( !current_user_can('edit_post', $postID) )
			$this->auth_required(__('Sorry, you do not have the right to delete this post.'));

		$location = get_post_meta($entry['ID'], '_wp_attached_file', true);
		$filetype = wp_check_filetype($location);

		if ( !isset($location) || 'attachment' != $entry['post_type'] || empty($filetype['ext']) )
			$this->internal_error(__('Error occurred while accessing post metadata for file location.'));

		// delete file
		@unlink($location);

		// delete attachment
		$result = wp_delete_post($postID);

		if ( !$result )
			$this->internal_error(__('For some strange yet very annoying reason, this post could not be deleted.'));

		$this->ok();
	}

	/**
	 * Retrieve attachment from post.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function get_file($postID) {

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		// then whether user can edit the specific post
		if ( !current_user_can('edit_post', $postID) )
			$this->auth_required(__('Sorry, you do not have the right to edit this post.'));

		$location = get_post_meta($entry['ID'], '_wp_attached_file', true);
		$location = get_option ('upload_path') . '/' . $location;
		$filetype = wp_check_filetype($location);

		if ( !isset($location) || 'attachment' != $entry['post_type'] || empty($filetype['ext']) )
			$this->internal_error(__('Error occurred while accessing post metadata for file location.'));

		status_header('200');
		header('Content-Type: ' . $entry['post_mime_type']);
		header('Connection: close');

		if ( $fp = fopen($location, "rb") ) {
			status_header('200');
			header('Content-Type: ' . $entry['post_mime_type']);
			header('Connection: close');

			while ( !feof($fp) ) {
				echo fread($fp, 4096);
			}

			fclose($fp);
		} else {
			status_header ('404');
		}

		wp_die();
	}

	/**
	 * Upload file to blog and add attachment to post.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function put_file($postID) {

		// first check if user can upload
		if ( !current_user_can('upload_files') )
			$this->auth_required(__('You do not have permission to upload files.'));

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		// then whether user can edit the specific post
		if ( !current_user_can('edit_post', $postID) )
			$this->auth_required(__('Sorry, you do not have the right to edit this post.'));

		$upload_dir = wp_upload_dir( );
		$location = get_post_meta($entry['ID'], '_wp_attached_file', true);
		$filetype = wp_check_filetype($location);

		$location = "{$upload_dir['basedir']}/{$location}";

		if (!isset($location) || 'attachment' != $entry['post_type'] || empty($filetype['ext']))
			$this->internal_error(__('Error occurred while accessing post metadata for file location.'));

		$fp = fopen("php://input", "rb");
		$localfp = fopen($location, "w+");
		while ( !feof($fp) ) {
			fwrite($localfp,fread($fp, 4096));
		}
		fclose($fp);
		fclose($localfp);

		$ID = $entry['ID'];
		$pubtimes = $this->get_publish_time($entry->published);
		$post_date = $pubtimes[0];
		$post_date_gmt = $pubtimes[1];
		$pubtimes = $this->get_publish_time($parsed->updated);
		$post_modified = $pubtimes[0];
		$post_modified_gmt = $pubtimes[1];

		$post_data = compact('ID', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt');
		$result = wp_update_post($post_data);

		if ( !$result )
			$this->internal_error(__('Sorry, your entry could not be posted. Something wrong happened.'));

		wp_update_attachment_metadata( $postID, wp_generate_attachment_metadata( $postID, $location ) );

		$this->ok();
	}

	/**
	 * Retrieve entries URL.
	 *
	 * @since 2.2.0
	 *
	 * @param int $page Page ID.
	 * @return string
	 */
	function get_entries_url($page = null) {
		if ( isset($GLOBALS['post_type']) && ( $GLOBALS['post_type'] == 'attachment' ) )
			$path = $this->MEDIA_PATH;
		else
			$path = $this->ENTRIES_PATH;
		$url = $this->app_base . $path;
		if ( isset($page) && is_int($page) )
			$url .= "/$page";
		return $url;
	}

	/**
	 * Display entries URL.
	 *
	 * @since 2.2.0
	 *
	 * @param int $page Page ID.
	 */
	function the_entries_url($page = null) {
		echo $this->get_entries_url($page);
	}

	/**
	 * Retrieve categories URL.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed $deprecated Not used.
	 * @return string
	 */
	function get_categories_url($deprecated = '') {
		if ( !empty( $deprecated ) )
			_deprecated_argument( __FUNCTION__, '2.5' );
		return $this->app_base . $this->CATEGORIES_PATH;
	}

	/**
	 * Display category URL.
	 *
	 * @since 2.2.0
	 */
	function the_categories_url() {
		echo $this->get_categories_url();
	}

	/**
	 * Retrieve attachment URL.
	 *
	 * @since 2.2.0
	 *
	 * @param int $page Page ID.
	 * @return string
	 */
	function get_attachments_url($page = null) {
		$url = $this->app_base . $this->MEDIA_PATH;
		if (isset($page) && is_int($page)) {
			$url .= "/$page";
		}
		return $url;
	}

	/**
	 * Display attachment URL.
	 *
	 * @since 2.2.0
	 *
	 * @param int $page Page ID.
	 */
	function the_attachments_url($page = null) {
		echo $this->get_attachments_url($page);
	}

	/**
	 * Retrieve service URL.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	function get_service_url() {
		return $this->app_base . $this->SERVICE_PATH;
	}

	/**
	 * Retrieve entry URL.
	 *
	 * @since 2.7.0
	 *
	 * @param int $postID Post ID.
	 * @return string
	 */
	function get_entry_url($postID = null) {
		if (!isset($postID)) {
			global $post;
			$postID = (int) $post->ID;
		}

		$url = $this->app_base . $this->ENTRY_PATH . "/$postID";

		return $url;
	}

	/**
	 * Display entry URL.
	 *
	 * @since 2.7.0
	 *
	 * @param int $postID Post ID.
	 */
	function the_entry_url($postID = null) {
		echo $this->get_entry_url($postID);
	}

	/**
	 * Retrieve media URL.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 * @return string
	 */
	function get_media_url($postID = null) {
		if (!isset($postID)) {
			global $post;
			$postID = (int) $post->ID;
		}

		$url = $this->app_base . $this->MEDIA_SINGLE_PATH ."/file/$postID";

		return $url;
	}

	/**
	 * Display the media URL.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function the_media_url($postID = null) {
		echo $this->get_media_url($postID);
	}

	/**
	 * Set the current entry to post ID.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 */
	function set_current_entry($postID) {
		global $entry;

		if (!isset($postID)) {
			// $this->bad_request();
			$this->not_found();
		}

		$entry = wp_get_single_post($postID,ARRAY_A);

		if (!isset($entry) || !isset($entry['ID']))
			$this->not_found();

		return;
	}

	/**
	 * Display posts XML.
	 *
	 * @since 2.2.0
	 *
	 * @param int $page Optional. Page ID.
	 * @param string $post_type Optional, default is 'post'. Post Type.
	 */
	function get_posts($page = 1, $post_type = 'post') {
		$feed = $this->get_feed($page, $post_type);
		$this->output($feed);
	}

	/**
	 * Display attachment XML.
	 *
	 * @since 2.2.0
	 *
	 * @param int $page Page ID.
	 * @param string $post_type Optional, default is 'attachment'. Post type.
	 */
	function get_attachments($page = 1, $post_type = 'attachment') {
		$GLOBALS['post_type'] = $post_type;
		$feed = $this->get_feed($page, $post_type);
		$this->output($feed);
	}

	/**
	 * Retrieve feed XML.
	 *
	 * @since 2.2.0
	 *
	 * @param int $page Page ID.
	 * @param string $post_type Optional, default is post. Post type.
	 * @return string
	 */
	function get_feed($page = 1, $post_type = 'post') {
		global $post, $wp, $wp_query, $posts, $wpdb, $blog_id;
		ob_start();

		$this->ENTRY_PATH = $post_type;

		if (!isset($page)) {
			$page = 1;
		}
		$page = (int) $page;

		$count = get_option('posts_per_rss');

		wp('posts_per_page=' . $count . '&offset=' . ($count * ($page-1)) . '&orderby=modified&post_status=any');

		$post = $GLOBALS['post'];
		$posts = $GLOBALS['posts'];
		$wp = $GLOBALS['wp'];
		$wp_query = $GLOBALS['wp_query'];
		$wpdb = $GLOBALS['wpdb'];
		$blog_id = (int) $GLOBALS['blog_id'];

		$last_page = $wp_query->max_num_pages;
		$next_page = (($page + 1) > $last_page) ? null : $page + 1;
		$prev_page = ($page - 1) < 1 ? null : $page - 1;
		$last_page = ((int)$last_page == 1 || (int)$last_page == 0) ? null : (int) $last_page;
		$self_page = $page > 1 ? $page : null;
?><feed xmlns="<?php echo $this->ATOM_NS ?>" xmlns:app="<?php echo $this->ATOMPUB_NS ?>" xml:lang="<?php bloginfo_rss( 'language' ); ?>" <?php do_action('app_ns'); ?> >
<id><?php $this->the_entries_url() ?></id>
<updated><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT'), false); ?></updated>
<title type="text"><?php bloginfo_rss('name') ?></title>
<subtitle type="text"><?php bloginfo_rss("description") ?></subtitle>
<link rel="first" type="<?php echo $this->ATOM_CONTENT_TYPE ?>" href="<?php $this->the_entries_url() ?>" />
<?php if (isset($prev_page)): ?>
<link rel="previous" type="<?php echo $this->ATOM_CONTENT_TYPE ?>" href="<?php $this->the_entries_url($prev_page) ?>" />
<?php endif; ?>
<?php if (isset($next_page)): ?>
<link rel="next" type="<?php echo $this->ATOM_CONTENT_TYPE ?>" href="<?php $this->the_entries_url($next_page) ?>" />
<?php endif; ?>
<link rel="last" type="<?php echo $this->ATOM_CONTENT_TYPE ?>" href="<?php $this->the_entries_url($last_page) ?>" />
<link rel="self" type="<?php echo $this->ATOM_CONTENT_TYPE ?>" href="<?php $this->the_entries_url($self_page) ?>" />
<rights type="text">Copyright <?php echo date('Y'); ?></rights>
<?php do_action('app_head'); ?>
<?php if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$this->echo_entry();
			}
		}
?></feed>
<?php
		$feed = ob_get_contents();
		ob_end_clean();
		return $feed;
	}

	/**
	 * Display entry XML.
	 *
	 * @since 2.2.0
	 *
	 * @param int $postID Post ID.
	 * @param string $post_type Optional, default is post. Post type.
	 * @return string.
	 */
	function get_entry($postID, $post_type = 'post') {
		ob_start();
		switch($post_type) {
			case 'post':
				$varname = 'p';
				break;
			case 'attachment':
				$this->ENTRY_PATH = 'attachment';
				$varname = 'attachment_id';
				break;
		}
		query_posts($varname . '=' . $postID);
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$this->echo_entry();
				$entry = ob_get_contents();
				break;
			}
		}
		ob_end_clean();

		return $entry;
	}

	/**
	 * Display post content XML.
	 *
	 * @since 2.3.0
	 */
	function echo_entry() { ?>
<entry xmlns="<?php echo $this->ATOM_NS ?>"
       xmlns:app="<?php echo $this->ATOMPUB_NS ?>" xml:lang="<?php bloginfo_rss( 'language' ); ?>">
	<id><?php the_guid( $GLOBALS['post']->ID ); ?></id>
<?php list($content_type, $content) = prep_atom_text_construct(get_the_title()); ?>
	<title type="<?php echo $content_type ?>"><?php echo $content ?></title>
	<updated><?php echo get_post_modified_time('Y-m-d\TH:i:s\Z', true); ?></updated>
	<published><?php echo get_post_time('Y-m-d\TH:i:s\Z', true); ?></published>
	<app:edited><?php echo get_post_modified_time('Y-m-d\TH:i:s\Z', true); ?></app:edited>
	<app:control>
		<app:draft><?php echo ($GLOBALS['post']->post_status == 'draft' ? 'yes' : 'no') ?></app:draft>
	</app:control>
	<author>
		<name><?php the_author()?></name>
<?php if ( get_the_author_meta('url') && get_the_author_meta('url') != 'http://' ) { ?>
		<uri><?php the_author_meta('url') ?></uri>
<?php } ?>
	</author>
<?php if ($GLOBALS['post']->post_type == 'attachment') { ?>
	<link rel="edit-media" href="<?php $this->the_media_url() ?>" />
	<content type="<?php echo $GLOBALS['post']->post_mime_type ?>" src="<?php the_guid() ; ?>"/>
<?php } else { ?>
	<link href="<?php the_permalink_rss() ?>" />
<?php if ( strlen( $GLOBALS['post']->post_content ) ) :
list($content_type, $content) = prep_atom_text_construct(get_the_content()); ?>
	<content type="<?php echo $content_type ?>"><?php echo $content ?></content>
<?php endif; ?>
<?php } ?>
	<link rel="edit" href="<?php $this->the_entry_url() ?>" />
	<?php the_category_rss( 'atom' ); ?>
<?php list($content_type, $content) = prep_atom_text_construct(get_the_excerpt()); ?>
	<summary type="<?php echo $content_type ?>"><?php echo $content ?></summary>
	<?php do_action('app_entry'); ?>
</entry>
<?php }

	/**
	 * Set 'OK' (200) status header.
	 *
	 * @since 2.2.0
	 */
	function ok() {
		header('Content-Type: text/plain');
		status_header('200');
		wp_die();
	}

	/**
	 * Set 'No Content' (204) status header.
	 *
	 * @since 2.2.0
	 */
	function no_content() {
		header('Content-Type: text/plain');
		status_header('204');
		echo "Moved to Trash.";
		wp_die();
	}

	/**
	 * Display 'Internal Server Error' (500) status header.
	 *
	 * @since 2.2.0
	 *
	 * @param string $msg Optional. Status string.
	 */
	function internal_error($msg = 'Internal Server Error') {
		header('Content-Type: text/plain');
		status_header('500');
		echo $msg;
		wp_die();
	}

	/**
	 * Set 'Bad Request' (400) status header.
	 *
	 * @since 2.2.0
	 */
	function bad_request() {
		header('Content-Type: text/plain');
		status_header('400');
		wp_die();
	}

	/**
	 * Set 'Length Required' (411) status header.
	 *
	 * @since 2.2.0
	 */
	function length_required() {
		header("HTTP/1.1 411 Length Required");
		header('Content-Type: text/plain');
		status_header('411');
		wp_die();
	}

	/**
	 * Set 'Unsupported Media Type' (415) status header.
	 *
	 * @since 2.2.0
	 */
	function invalid_media() {
		header("HTTP/1.1 415 Unsupported Media Type");
		header('Content-Type: text/plain');
		wp_die();
	}

	/**
	 * Set 'Forbidden' (403) status header.
	 *
	 * @since 2.6.0
	 */
	function forbidden($reason='') {
		header('Content-Type: text/plain');
		status_header('403');
		echo $reason;
		wp_die();
	}

	/**
	 * Set 'Not Found' (404) status header.
	 *
	 * @since 2.2.0
	 */
	function not_found() {
		header('Content-Type: text/plain');
		status_header('404');
		wp_die();
	}

	/**
	 * Set 'Not Allowed' (405) status header.
	 *
	 * @since 2.2.0
	 */
	function not_allowed($allow) {
		header('Allow: ' . join(',', $allow));
		status_header('405');
		wp_die();
	}

	/**
	 * Display Redirect (302) content and set status headers.
	 *
	 * @since 2.3.0
	 */
	function redirect($url) {
		$escaped_url = esc_attr($url);
		$content = <<<EOD
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
  <head>
    <title>302 Found</title>
  </head>
<body>
  <h1>Found</h1>
  <p>The document has moved <a href="$escaped_url">here</a>.</p>
  </body>
</html>

EOD;
		header('HTTP/1.1 302 Moved');
		header('Content-Type: text/html');
		header('Location: ' . $url);
		echo $content;
		wp_die();

	}

	/**
	 * Set 'Client Error' (400) status header.
	 *
	 * @since 2.2.0
	 */
	function client_error($msg = 'Client Error') {
		header('Content-Type: text/plain');
		status_header('400');
		wp_die();
	}

	/**
	 * Set created status headers (201).
	 *
	 * Sets the 'content-type', 'content-location', and 'location'.
	 *
	 * @since 2.2.0
	 */
	function created($post_ID, $content, $post_type = 'post') {
		$edit = $this->get_entry_url($post_ID);
		switch($post_type) {
			case 'post':
				$ctloc = $this->get_entry_url($post_ID);
				break;
			case 'attachment':
				$edit = $this->app_base . "attachments/$post_ID";
				break;
		}
		header("Content-Type: $this->ATOM_CONTENT_TYPE");
		if (isset($ctloc))
			header('Content-Location: ' . $ctloc);
		header('Location: ' . $edit);
		status_header('201');
		echo $content;
		wp_die();
	}

	/**
	 * Set 'Auth Required' (401) headers.
	 *
	 * @since 2.2.0
	 *
	 * @param string $msg Status header content and HTML content.
	 */
	function auth_required($msg) {
		nocache_headers();
		header('WWW-Authenticate: Basic realm="WordPress Atom Protocol"');
		header("HTTP/1.1 401 $msg");
		header('Status: 401 ' . $msg);
		header('Content-Type: text/html');
		$content = <<<EOD
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
  <head>
    <title>401 Unauthorized</title>
  </head>
<body>
    <h1>401 Unauthorized</h1>
    <p>$msg</p>
  </body>
</html>

EOD;
		echo $content;
		wp_die();
	}

	/**
	 * Display XML and set headers with content type.
	 *
	 * @since 2.2.0
	 *
	 * @param string $xml Display feed content.
	 * @param string $ctype Optional, default is 'atom+xml'. Feed content type.
	 */
	function output($xml, $ctype = 'application/atom+xml') {
		status_header('200');
		$xml = '<?xml version="1.0" encoding="' . strtolower(get_option('blog_charset')) . '"?>'."\n".$xml;
		header('Connection: close');
		header('Content-Length: '. strlen($xml));
		header('Content-Type: ' . $ctype);
		header('Content-Disposition: attachment; filename=atom.xml');
		header('Date: '. date('r'));
		if ($this->do_output)
			echo $xml;
		wp_die();
	}

	/**
	 * Sanitize content for database usage.
	 *
	 * @since 2.2.0
	 *
	 * @param array $array Sanitize array and multi-dimension array.
	 */
	function escape(&$array) {
		global $wpdb;

		foreach ($array as $k => $v) {
				if (is_array($v)) {
						$this->escape($array[$k]);
				} else if (is_object($v)) {
						//skip
				} else {
						$array[$k] = $wpdb->escape($v);
				}
		}
	}

	/**
	 * Access credential through various methods and perform login.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	function authenticate() {

		// if using mod_rewrite/ENV hack
		// http://www.besthostratings.com/articles/http-auth-php-cgi.html
		if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
				explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		} else if (isset($_SERVER['REDIRECT_REMOTE_USER'])) {
			// Workaround for setups that do not forward HTTP_AUTHORIZATION
			// See http://trac.wordpress.org/ticket/7361
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
				explode(':', base64_decode(substr($_SERVER['REDIRECT_REMOTE_USER'], 6)));
		}

		// If Basic Auth is working...
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

			$user = wp_authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
			if ( $user && !is_wp_error($user) ) {
				wp_set_current_user($user->ID);
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve accepted content types.
	 *
	 * @since 2.2.0
	 *
	 * @param array $types Optional. Content Types.
	 * @return string
	 */
	function get_accepted_content_type($types = null) {

		if (!isset($types)) {
			$types = $this->media_content_types;
		}

		if (!isset($_SERVER['CONTENT_LENGTH']) || !isset($_SERVER['CONTENT_TYPE'])) {
			$this->length_required();
		}

		$type = $_SERVER['CONTENT_TYPE'];
		list($type,$subtype) = explode('/',$type);
		list($subtype) = explode(";",$subtype); // strip MIME parameters

		foreach($types as $t) {
			list($acceptedType,$acceptedSubtype) = explode('/',$t);
			if ($acceptedType == '*' || $acceptedType == $type) {
				if ($acceptedSubtype == '*' || $acceptedSubtype == $subtype)
					return $type . "/" . $subtype;
			}
		}

		$this->invalid_media();
	}

	/**
	 * Process conditionals for posts.
	 *
	 * @since 2.2.0
	 */
	function process_conditionals() {

		if (empty($this->params)) return;
		if ($_SERVER['REQUEST_METHOD'] == 'DELETE') return;

		switch($this->params[0]) {
			case $this->ENTRY_PATH:
				global $post;
				$post = wp_get_single_post($this->params[1]);
				$wp_last_modified = get_post_modified_time('D, d M Y H:i:s', true);
				$post = null;
				break;
			case $this->ENTRIES_PATH:
				$wp_last_modified = mysql2date('D, d M Y H:i:s', get_lastpostmodified('GMT'), 0).' GMT';
				break;
			default:
				return;
		}
		$wp_etag = md5($wp_last_modified);
		@header("Last-Modified: $wp_last_modified");
		@header("ETag: $wp_etag");

		// Support for Conditional GET
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
			$client_etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
		else
			$client_etag = false;

		$client_last_modified = trim( $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		// If string is empty, return 0. If not, attempt to parse into a timestamp
		$client_modified_timestamp = $client_last_modified ? strtotime($client_last_modified) : 0;

		// Make a timestamp for our most recent modification...
		$wp_modified_timestamp = strtotime($wp_last_modified);

		if ( ($client_last_modified && $client_etag) ?
		(($client_modified_timestamp >= $wp_modified_timestamp) && ($client_etag == $wp_etag)) :
		(($client_modified_timestamp >= $wp_modified_timestamp) || ($client_etag == $wp_etag)) ) {
			status_header( 304 );
			wp_die();
		}
	}

	/**
	 * Convert RFC3339 time string to timestamp.
	 *
	 * @since 2.3.0
	 *
	 * @param string $str String to time.
	 * @return bool|int false if format is incorrect.
	 */
	function rfc3339_str2time($str) {

		$match = false;
		if (!preg_match("/(\d{4}-\d{2}-\d{2})T(\d{2}\:\d{2}\:\d{2})\.?\d{0,3}(Z|[+-]+\d{2}\:\d{2})/", $str, $match))
			return false;

		if ($match[3] == 'Z')
			$match[3] = '+0000';

		return strtotime($match[1] . " " . $match[2] . " " . $match[3]);
	}

	/**
	 * Retrieve published time to display in XML.
	 *
	 * @since 2.3.0
	 *
	 * @param string $published Time string.
	 * @return string
	 */
	function get_publish_time($published) {

		$pubtime = $this->rfc3339_str2time($published);

		if (!$pubtime) {
			return array(current_time('mysql'),current_time('mysql',1));
		} else {
			return array(date("Y-m-d H:i:s", $pubtime), gmdate("Y-m-d H:i:s", $pubtime));
		}
	}

}
