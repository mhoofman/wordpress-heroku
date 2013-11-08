=== Google XML Sitemaps ===
Contributors: arnee
Donate link: http://www.arnebrachhold.de/redir/sitemap-paypal
Tags: seo, google, sitemaps, google sitemaps, yahoo, msn, ask, live, xml sitemap, xml
Requires at least: 2.1
Tested up to: 3.7
Stable tag: 3.2.9

This plugin will generate a special XML sitemap which will help search engines to better index your blog.

== Description ==

This plugin will generate a special XML sitemap which will help search engines like Google, Bing, Yahoo and Ask.com to better index your blog. With such a sitemap, it's much easier for the crawlers to see the complete structure of your site and retrieve it more efficiently. The plugin supports all kinds of WordPress generated pages as well as custom URLs. Additionally it notifies all major search engines every time you create a post about the new content.

Related Links:

* <a href="http://www.arnebrachhold.de/projects/wordpress-plugins/google-xml-sitemaps-generator/" title="Google XML Sitemaps Plugin for WordPress">Plugin Homepage</a>
* <a href="http://www.arnebrachhold.de/projects/wordpress-plugins/google-xml-sitemaps-generator/changelog/" title="Changelog of the Google XML Sitemaps Plugin for WordPress">Changelog</a>
* <a href="http://www.arnebrachhold.de/2006/04/07/google-sitemaps-faq-sitemap-issues-errors-and-problems/" title="Google Sitemaps FAQ">Plugin and sitemaps FAQ</a>
* <a href="http://wordpress.org/tags/google-sitemap-generator?forum_id=10">Support Forum</a>

*This release is not compatible with the new multisite feature of WordPress 3.0 yet. The plugin will remain inactive as long as this feature is enabled. If you are using this feature, try out the new <a href="http://www.arnebrachhold.de/redir/sitemap-dl-beta/">Beta version</a> which fully supports multisite mode as well as network activation!*

*This release is compatible with all WordPress versions since 2.1. If you are still using an older one, use <a href="http://downloads.wordpress.org/plugin/google-sitemap-generator.2.7.1.zip">version 2.7.1</a> instead.*

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Use your favorite FTP program to create two files in your WordPress directory (that's where the wp-config.php is) named sitemap.xml and sitemap.xml.gz and make them writable via CHMOD 666. More information about CHMOD and how to make files writable is available at the [WordPress Codex](http://codex.wordpress.org/Changing_File_Permissions) and on [stadtaus.com](http://www.stadtaus.com/en/tutorials/chmod-ftp-file-permissions.php). Making your whole blog directory writable is NOT recommended anymore due to security reasons.
4. Activate the plugin at the plugin administration page
5. Open the plugin configuration page, which is located under Options -> XML-Sitemap and build the sitemap the first time. If you get a permission error, check the file permissions of the newly created files.
6. The plugin will automatically update your sitemap of you publish a post, so theres nothing more to do :)

== Frequently Asked Questions ==

= There are no comments yet (or I've disabled them) and all my postings have a priority of zero! =

Please disable automatic priority calculation and define a static priority for posts.

= Do I always have to click on "Rebuild Sitemap" if I modified a post? =

No, if you edit/publish/delete a post, your sitemap is automatically regenerated

= So much configuration options... Do I need to change them? =

No, only if you want to. Default values should be ok!

= Does this plugin work with all WordPress versions? =

This version works with WordPress 2.1 and better. If you're using an older version, please check the [Google Sitemaps Plugin Homepage](http://www.arnebrachhold.de/projects/wordpress-plugins/google-xml-sitemaps-generator/ "Google (XML) Sitemap Generator Plugin Homepage") for the legacy releases. There is a working release for every WordPress version since 1.5

= I get an fopen and / or permission denied error or my sitemap files could not be written =

If you get permission errors, make sure that the script has the right to overwrite the sitemap.xml and sitemap.xml.gz files. Try to create the sitemap.xml resp. sitemap.xml.gz at manually and upload them with a ftp program and set the rights with CHMOD to 666 (or 777 if 666 still doesn't work). Then restart sitemap generation on the administration page. A good tutorial for changing file permissions can be found on the [WordPress Codex](http://codex.wordpress.org/Changing_File_Permissions) and at [stadtaus.com](http://www.stadtaus.com/en/tutorials/chmod-ftp-file-permissions.php).

= My question isn't answered here =

Most of the plugin options are described at the [plugin homepage](http://www.arnebrachhold.de/projects/wordpress-plugins/google-xml-sitemaps-generator/) as well as the dedicated [Google Sitemap FAQ](http://www.arnebrachhold.de/2006/04/07/google-sitemaps-faq-sitemap-issues-errors-and-problems/ "List of common questions / problems regarding Google (XML) Sitemaps")

= My question isn't even answered there =

Please post your question at the [WordPress support forum](http://wordpress.org/tags/google-sitemap-generator?forum_id=10) and tag your post with "google-sitemap-generator".

= What's new in the latest version? =

The changelog is maintained on [here](http://www.arnebrachhold.de/projects/wordpress-plugins/google-xml-sitemaps-generator/changelog/ "Google (XML) Sitemap Generator Plugin Changelog")

== Changelog ==

Until it appears here, the changelog is maintained on [the plugin website](http://www.arnebrachhold.de/projects/wordpress-plugins/google-xml-sitemaps-generator/changelog/ "Google (XML) Sitemap Generator Plugin Changelog")

== Screenshots ==

1. Administration interface in WordPress 2.7
2. Administration interface in WordPress 2.5
3. Administration interface in WordPress 2.0

== License ==

Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](http://www.arnebrachhold.de/redir/sitemap-paypal "Donate with PayPal") for the time I've spent writing and supporting this plugin. And I really don't want to know how many hours of my life this plugin has already eaten ;)

== Translations ==

The plugin comes with various translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the sitemap.pot file which contains all definitions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).