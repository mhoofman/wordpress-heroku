<?php
/*
Plugin Name: WP-Markdown
Description: Allows you to use MarkDown in posts, BBPress forums and comments
Version: 1.4
Author: Stephen Harris
Author URI: http://stephenharris.info
*/
/*  Copyright 2011 Stephen Harris (stephen@harriswebsolutions.co.uk)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
class WordPress_Markdown {

	var $domain = 'markdown';

	//Version
	static $version ='1.4';

	//Options and defaults
	static $options = array(
		'post_types'=>array(),
		'markdownbar'=>array(),
		'prettify'=>0,
	);
	static $option_types = array(
		'post_types'=>'array',
		'markdownbar'=>'array',
		'prettify'=>'checkbox',
	);
	
	public $kses_removed = false;

	public function __construct() {
		register_activation_hook(__FILE__,array(__CLASS__, 'install' )); 
		register_uninstall_hook(__FILE__,array( __CLASS__, 'uninstall' )); 
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'set_current_user', array( $this, 'maybe_remove_kses' ), 99 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	static function install(){
		update_option("markdown_version",self::$version);
		add_option('markdown',self::$options);
	}

	static function uninstall(){
		delete_option("markdown_version");
		delete_option('markdown');
	}


	public function init() {
		//Allow translations
		load_plugin_textdomain( 'markdown', false, basename(dirname(__FILE__)).'/languages');

		//Markdown posts and comments
		add_filter('pre_comment_content',array($this,'pre_comment_content'),5);
		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ), 10, 2 );
		add_filter('bbp_new_reply_pre_content',array( $this, 'bbp_reply_pre_content' ), 5, 2 );
		add_filter('bbp_edit_reply_pre_content',array( $this, 'bbp_reply_pre_content' ), 5, 2 );
		add_filter('bbp_new_topic_pre_content',array( $this, 'bbp_topic_pre_content' ), 5, 2 );
		add_filter('bbp_edit_topic_pre_content',array( $this, 'bbp_topic_pre_content' ), 5, 2 );
		
		//See https://github.com/stephenharris/WP-MarkDown/issues/25
		if( $this->is_Markdownable('reply') ){
			remove_filter( 'bbp_new_reply_pre_content', 'bbp_code_trick',  20 );
			remove_filter( 'bbp_edit_reply_pre_content', 'bbp_code_trick',  20 );
			remove_filter( 'bbp_get_form_reply_content', 'bbp_code_trick_reverse',  10 );
		}
		
		if( $this->is_Markdownable('topic') ){
			remove_filter( 'bbp_new_topic_pre_content', 'bbp_code_trick', 20 );
			remove_filter( 'bbp_edit_topic_pre_content', 'bbp_code_trick', 20 );
			remove_filter( 'bbp_get_form_topic_content', 'bbp_code_trick_reverse', 10 );
		}
		
		$this->maybe_remove_kses();
		remove_filter( 'content_save_pre', 'balanceTags', 50 ); //Remove balanceTags and apply after MD -> HTML

		//Convert HTML to Markdown (posts, comments, BBPress front-end editing)
		add_filter( 'edit_post_content', array( $this, 'edit_post_content' ), 10, 2 );
		add_filter( 'comment_edit_pre',  array( $this, 'edit_comment_content' ));
		add_filter('bbp_get_form_reply_content',array( $this, 'bbpress_edit_reply' ));
		add_filter('bbp_get_form_topic_content',array( $this, 'bbpress_edit_topic' ));

		//Add button bar and prettyify to BBPress and comment fields
		if($this->is_bar_enabled('comment')){
			add_filter('comment_form_field_comment',array($this,'comment_field'));
		}
		if($this->is_bar_enabled('bbpress')){
			add_action('bbp_theme_before_reply_form_content',array( $this,'pre_textarea_prettify_bbpress_reply'));
			add_action('bbp_theme_after_reply_form_content',array( $this,'post_textarea_prettify_bbpress_reply'));
			add_action('bbp_theme_before_topic_form_content',array( $this,'pre_textarea_prettify_bbpress_topic'));
			add_action('bbp_theme_after_topic_form_content',array( $this,'post_textarea_prettify_bbpress_topic'));
		}
	
		//Register scripts
		add_action('wp_enqueue_scripts', array($this,'register_scripts'));
		
		//Ensures scripts/styles are queued (in particular on home page)
		if( $this->get_option( 'prettify') )
			add_filter( 'the_content', array( $this, 'the_content' ) );
		
	}

	/**
	 * {@see wp_filter_post_kses()} strips out all HTML tags that are not explicitly allowed
	 * for the current user. But this runs before markdown is converted to HTML, meaning that some tags
	 * in code blocks are stripped out. We remove the filter, and conditionally at it back at `wp_insert_post_data`.
	 */
	function maybe_remove_kses(){
		if ( remove_filter( 'content_save_pre', 'wp_filter_post_kses' ) ) {
			$this->kses_removed = true;
		}
	}
	/*
	* Settings
	*/
	function admin_init(){
		register_setting('writing',$this->domain, array($this,'validate'));
		add_settings_section( $this->domain.'_section', 'MarkDown', array($this,'settings'), 'writing'); 
		add_settings_field($this->domain.'_posttypes', __('Enable MarkDown for:', 'markdown'), array($this,'settings_posttypes'), 'writing', $this->domain.'_section');
		add_settings_field($this->domain.'_markdownbar', __('Enable MarkDown help bar for:', 'markdown'), array($this,'settings_markdownbar'), 'writing', $this->domain.'_section');
		add_settings_field($this->domain.'_prettify', __('Enable Prettify syntax highlighter:', 'markdown'), array($this,'settings_prettify'), 'writing', $this->domain.'_section');

		//Remove html tab for markdown posts
		add_filter( 'user_can_richedit', array($this,'can_richedit'), 99 );

		//Add admin scripts
		if($this->is_bar_enabled('posteditor')){
			add_action('admin_enqueue_scripts', array($this,'admin_scripts'),10,1);
		}
	}

	public function can_richedit($bool){
		$screen = get_current_screen();
		$post_type = $screen->post_type;
		if($this->is_Markdownable($post_type))
			return false;

		return $bool;
	}

	function settings(){
		//settings_fields('markdown'); 
		echo '<p>'.__("Select the post types or comments that will support Markdown. Comments and bbPress forums can also feature a Markdown 'help bar' and previewer. Automatic syntax highlighting can be provided by <a href='http://code.google.com/p/google-code-prettify/' target='_blank'>Prettify</a>.",$this->domain).'</p>';
	}

	function settings_posttypes(){
		$options = get_option($this->domain);
		$savedtypes = (array) $options['post_types'];
		$types=get_post_types(array('public'   => true),'objects'); 
		unset($types['attachment']);

		$id = "id={$this->domain}_posttypes'";
		foreach ($types as $type){
			echo "<label><input type='checkbox' {$id} ".checked(in_array($type->name,$savedtypes),true,false)."name='{$this->domain}[post_types][]' value='$type->name' />{$type->labels->name}</label></br>";
		}
		echo "<label><input type='checkbox' {$id} ".checked(in_array('comment',$savedtypes),true,false)."name='{$this->domain}[post_types][]' value='comment' />Comments</label></br>";	
	}

	function settings_markdownbar(){
		$options = get_option($this->domain);
		$savedtypes = (array) $options['post_types'];
		$barenabled = isset($options['markdownbar']) ? $options['markdownbar']  : self::$options['markdownbar'];
		$types=get_post_types(array('public'   => true),'objects'); 

		$id = "id={$this->domain}_markdownbar'";
		//If Forum, Topic, and Replies exist assume BBPress is activated:
		$type_names = array_keys($types);
		$bbpress = array_unique(array_merge($type_names,array('reply','forum','topic'))) === $type_names;

		echo "<label><input type='checkbox' {$id} ".checked(in_array('posteditor',$barenabled),true,false)."name='{$this->domain}[markdownbar][]' value='posteditor' />".__('Post editor',$this->domain)."</label></br>";				
		echo "<label><input type='checkbox' {$id} ".checked(in_array('comment',$barenabled)&&in_array('comment',$savedtypes),true,false)."name='{$this->domain}[markdownbar][]' value='comment' />".__('Comments',$this->domain)."</label></br>";
		echo "<label><input type='checkbox' {$id} ".checked(in_array('bbpress',$barenabled),true,false).disabled($bbpress,false,false)."name='{$this->domain}[markdownbar][]' value='bbpress' />".__('bbPress topics and replies',$this->domain)."</label></br>";
	}

	function settings_prettify(){
		$options = get_option($this->domain);
		$checked = (int) $options['prettify'];
		$id = "id={$this->domain}_prettify'";
		echo "<input type='checkbox' {$id} ".checked($checked,true,false)."name='{$this->domain}[prettify]' value='1' />";
	}

	function validate($options){
		$clean = array();
		
		foreach (self::$options as $option => $default){
			if(self::$option_types[$option]=='array'){
				$clean[$option] = isset($options[$option]) ? array_map('esc_attr',$options[$option]) : $default;
			}elseif(self::$option_types[$option]=='checkbox'){
				$clean[$option] = isset($options[$option]) ? (int) $options[$option] : $default;
			}
		}

		return $clean;
	}

	

	/*
	* Function to determine if markdown has been enabled for the current post_type or comment
	* If an integer is passed it assumed to be a post (not comment) ID. Otherwise it assumed to be the
	* the post type or 'comment' to test.
	*
	* @param (int|string) post ID or post type name or 'comment'
	* @return (true|false). True if markdown is enabled for this post type. False otherwise.
	* @since 1.0
	*/
	function is_Markdownable($id_or_type){
		if(is_int($id_or_type))
			$type = get_post_type($id_or_type);
		else
			$type = esc_attr($id_or_type);

		$options = get_option($this->domain);
		$savedtypes = (array) $options['post_types'];

		return in_array($type,$savedtypes);
	}

	function is_bar_enabled($id_or_type){
		if(is_int($id_or_type))
			$type = get_post_type($id_or_type);
		else
			$type = esc_attr($id_or_type);

		$options = get_option($this->domain);
		$barenabled = (array) $options['markdownbar'];

		return in_array($type,$barenabled);
	}
	/*
	* Function to determine if prettify should be loaded
	*/
	function load_prettify(){
		if( !$this->get_option( 'prettify') ) 
			return false;

		$savedtypes = (array) $this->get_option( 'post_types' );

		return is_singular($savedtypes);
	}
	
	function get_option( $option ){
		$options = get_option($this->domain);
		if( !isset( $options[$option] ) )
			return false;
		
		return $options[$option];
	}


	/*
	* Convert Markdown to HTML prior to insertion to database
	*/
	//For comments
	function pre_comment_content( $comment ){
		if( $this->is_Markdownable( 'comment' ) ){
			$comment = stripslashes( $comment );
			$comment = wpmarkdown_markdown_to_html( $comment );
			$comment = addslashes( $comment );
		}
		return $comment;
	}
	
	//For posts
	public function wp_insert_post_data( $data, $postarr ) {
		
		if( 
			$this->is_Markdownable( $data['post_type'] ) 
			|| ( $data['post_type'] =='revision' && $this->is_Markdownable( $data['post_parent'] ) ) 
		){
			$content = stripslashes( $data['post_content'] );
			$content = wpmarkdown_markdown_to_html( $content );
			$data['post_content'] = addslashes( $content );
		}
		
		//If we have removed kses - add it here
		if( $this->kses_removed ){
			$data['post_content'] = wp_filter_post_kses( $data['post_content'] );;
		}
		
		$data['post_content'] = balanceTags( $data['post_content'] );
		
		return $data;
	}

	//For bbPress replies (triggered before wp_kses)
	public function bbp_reply_pre_content( $content ) {		
		if( $this->is_Markdownable('reply') ){
			$content = stripslashes($content );
			$content = wpmarkdown_markdown_to_html( $content );
			$content = addslashes($content);
		}
		return $content;
	}

	//For bbPress topics (triggered before wp_kses)
	public function bbp_topic_pre_content( $content ) {		
		if( $this->is_Markdownable( 'topic' ) ){
			$content = stripslashes( $content );
			$content = wpmarkdown_markdown_to_html( $content );
			$content = addslashes( $content );
		}
		return $content;
	}


	/*
	* Convert HTML to MarkDown for editing
	*/

	//Post content
	public function edit_post_content( $content, $id ) {
		if( $this->is_Markdownable( (int) $id) ){
			$content = wpmarkdown_html_to_markdown( $content );
		}
		return $content;
	}

	//Comment content
	public function edit_comment_content( $content ) {
		if( $this->is_Markdownable( 'comment' ) ){
			$content = htmlspecialchars_decode( $content );
			$content = wpmarkdown_html_to_markdown( $content );
			$content = esc_html( $content );
		}
		return $content;
	}

	public function bbpress_edit_reply( $content = '' ) {
		return $this->bbpress_edit( $content, 'reply' );
	}
	public function bbpress_edit_topic( $content = '' ) {
		return $this->bbpress_edit( $content, 'topic' );
	}
	public function bbpress_edit( $content = '', $type = '' ) {
		if( $this->is_Markdownable( $type ) ){
			$content = htmlspecialchars_decode( $content );
			$content = wpmarkdown_html_to_markdown( $content );
			$content = esc_attr( $content );
		}
		return $content;
	}


	/*
	* Adds html for the textareas to make them PageDown compatible 
       * Adds the PageDown 'button bar'
	*/
	function pre_textarea_prettify_bbpress_reply(){
		if( $this->is_Markdownable( 'reply' ) ){
			add_filter( 'bbp_use_wp_editor', '__return_false' );
			echo self::pre_textarea_prettify( 'bbp_reply_content' );
		}
	}
	function post_textarea_prettify_bbpress_reply(){
		if( $this->is_Markdownable( 'reply' ) ){
			add_filter( 'bbp_use_wp_editor', '__return_false' );
			echo self::post_textarea_prettify( 'bbp_reply_content' );
		}
	}
	function pre_textarea_prettify_bbpress_topic(){
		if($this->is_Markdownable('topic')){
			add_filter('bbp_use_wp_editor','__return_false');
			echo self::pre_textarea_prettify('bbp_topic_content');
		}
	}
	function post_textarea_prettify_bbpress_topic(){
		if($this->is_Markdownable('topic')){
			add_filter('bbp_use_wp_editor','__return_false');
			echo self::post_textarea_prettify('bbp_topic_content');
		}
	}

	function comment_field($html){
		if($this->is_Markdownable('comment')){
			$html =self::pre_textarea_prettify('comment').$html.self::post_textarea_prettify('comment');
		}
		return $html;
	}

	function pre_textarea_prettify($id=""){
		
		wp_enqueue_script( 'wp-markdown-editor' );
		wp_enqueue_script( 'wp-markdown' );
		wp_enqueue_style( 'wp-markdown-editor' );
		$id = esc_attr($id);

		$help = apply_filters('wpmarkdown_help_text'," <p>To create code blocks or other preformatted text, indent by four spaces:</p>
        <pre class='wmd-help'><span class='wmd-help-spaces'>&nbsp;&nbsp;&nbsp;&nbsp;</span>This will be displayed in a monospaced font. The first four 
<span class='wmd-help-spaces'>&nbsp;&nbsp;&nbsp;&nbsp;</span>spaces will be stripped off, but all other whitespace
<span class='wmd-help-spaces'>&nbsp;&nbsp;&nbsp;&nbsp;</span>will be preserved.
<span class='wmd-help-spaces'>&nbsp;&nbsp;&nbsp;&nbsp;</span>
<span class='wmd-help-spaces'>&nbsp;&nbsp;&nbsp;&nbsp;</span>Markdown is turned off in code blocks:
<span class='wmd-help-spaces'>&nbsp;&nbsp;&nbsp;&nbsp;</span> [This is not a link](http://example.com)
</pre>
        <p>
            To create not a block, but an inline code span, use backticks:
        </p>
        <pre class='wmd-help'>Here is some inline `code`.</pre> <p>For more help see <a href='http://daringfireball.net/projects/markdown/syntax' rel='no-follow'> http://daringfireball.net/projects/markdown/syntax</a></p>");

		return "<div class='wmd-panel'><div id='wmd-button-bar{$id}'></div><div id='wmd-button-bar-help'>".$help."</div>";
	}
	function post_textarea_prettify($id=""){
		$id = esc_attr($id);
       	 return "<div id='wmd-preview{$id}' class='wmd-panel wmd-preview prettyprint'></div></div>";
	}

	/*
	* Register the scripts for the PageDown editor
	*/
	function register_scripts() {
		 //Markdown Preview and Prettify scripts
		$plugin_dir = plugin_dir_url(__FILE__);
		
		$min = (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG) ? '' : '.min';
		
		//Register editor scripts &
		wp_register_script( 'wp-markdown-convertor', $plugin_dir . "js/pagedown/Markdown.Converter{$min}.js", array(), self::$version );
		wp_register_script( 'wp-markdown-sanitizer', $plugin_dir . "js/pagedown/Markdown.Sanitizer{$min}.js", array(), self::$version );
		wp_register_script( 'wp-markdown-editor', $plugin_dir . "js/pagedown/Markdown.Editor{$min}.js", array('wp-markdown-convertor','wp-markdown-sanitizer'), self::$version );
		
		//Register prettify script
		wp_register_script( 'wp-markdown-prettify',$plugin_dir. "js/prettify.js", array('jquery'), self::$version );
		
		//Register editor style 
		wp_register_style( 'wp-markdown-editor', $plugin_dir.'css/markdown-editor.css', array(), self::$version );
		
		//Register prettify style
		wp_register_style( 'wp-markdown-prettify', apply_filters( 'wpmarkdown_prettify_style_src', $plugin_dir.'css/prettify.css' ), array(), self::$version );
		
		$markdown_dependancy = array('jquery');
		$options = get_option($this->domain);

		 //Load prettify if enabled and viewing an appropriate post.
		if( !empty( $options['prettify'] ) ){
			$markdown_dependancy[]= 'wp-markdown-prettify';

			if( !is_admin() && $this->load_prettify() ){	
				wp_enqueue_script( 'wp-markdown-prettify' );
				wp_enqueue_style( 'wp-markdown-editor' );
				wp_enqueue_style( 'wp-markdown-prettify' );
			}
		}
		
		//This script sets the ball rolling with the editor & preview
   		wp_register_script( 'wp-markdown', $plugin_dir . "js/markdown{$min}.js", $markdown_dependancy, self::$version );
	}
	
	/**
	 * This ensures the prettify styles & scripts are in the queue 
	 * When on a home page prettify wont already have been queued.
	 */
	function the_content( $content ){
		$post_id = get_the_ID();
		$post_type = get_post_type();
		$post_types = $this->get_option( 'post_types' ); 
		
		if( $this->get_option( 'prettify') && in_array( $post_type, $post_types ) ){
			wp_enqueue_style('wp-markdown-prettify');
			wp_enqueue_script( 'wp-markdown' ); //Sets the prettify ball rolling.
		}
		
		return $content;
	}


	function admin_scripts($hook){
		$screen = get_current_screen();
		$post_type = $screen->post_type;
    		if ( ('post-new.php' == $hook || 'post.php' == $hook) && $this->is_Markdownable($post_type) ){
				$this->register_scripts();
				wp_enqueue_script( 'wp-markdown-prettify' );
				wp_enqueue_script( 'wp-markdown-editor' );
				wp_enqueue_style( 'wp-markdown-editor' );
				wp_enqueue_style( 'wp-markdown-prettify' );
				add_action( 'admin_print_footer_scripts', array($this,'admin_footers_script'),100 );
		}
	}

	function admin_footers_script(){
	?> <script>
		jQuery(document).ready(function($) {                
			$('#wp-content-editor-container').after("<div id='wmd-previewcontent' class='wmd-panel wmd-preview prettyprint'></div>");
			$('#ed_toolbar').html("<div id='wmd-button-barcontent'></div>");
			var converter = new Markdown.getSanitizingConverter();
			var editor = new Markdown.Editor(converter, 'content');
			editor.run();
			$('.wmd-preview pre').addClass('prettyprint');
			prettyPrint();
			if (typeof prettyPrint == 'function') {
				prettyPrint();
				editor.hooks.chain("onPreviewRefresh", function () {
				        $('.wmd-preview pre').addClass('prettyprint');
					prettyPrint();
   				 });
			}
		});
		</script><?php
	}
}


/**
 * Converts HTML into markdown
 * 
 * @param string $html
 * @return string markdown
 */
function wpmarkdown_html_to_markdown( $html ){
	$md = new Markdownify_Extra;
	$markdown = $md->parseString( $html );
	return $markdown;
}

/**
 * Converts markdown into HTML
 *
 * @param string $markdown
 * @return string HTML
 */
function wpmarkdown_markdown_to_html( $markdown ){
	return Markdown( $markdown );
}

require_once( dirname( __FILE__) . '/markdown-extra.php' );
require_once( dirname( __FILE__) . '/markdownify/markdownify.php' );
require_once( dirname( __FILE__) . '/markdownify/markdownify_extra.php' );
$markdown = new WordPress_Markdown();
