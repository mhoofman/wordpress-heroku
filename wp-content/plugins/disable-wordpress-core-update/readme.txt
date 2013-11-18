=== Disable WordPress Core Updates ===
Contributors: johnbillion
Tags: disable, core update
Requires at least: 2.3
Tested up to: 3.7
Stable tag: trunk

Disables the WordPress core update checking and notification system.

== Description ==

Completely disables the core update checking system in WordPress. The plugin prevents WordPress from checking for updates, and prevents any notifications from being displayed in the admin area. Ideal for administrators of multiple WordPress installations.

= Please note! =

It's *very* important that you keep your WordPress installation(s) up to date. If you don't, your blog or website could be susceptible to security vulnerabilities or performance issues. If you use this plugin, you must make sure you keep yourself informed of new WordPress releases and update your WordPress installation(s) as new versions are released.

== Installation ==

1. Unzip the ZIP file and drop the folder straight into your `wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Why would I want to disable the core update system? =

Most people will not want to (and should not) disable this feature. It's a fantastic feature of WordPress and I'm fully in support of it. However, administrators who maintain multiple installations of WordPress on behalf of other people (eg. clients) may not want update notifications to be shown to the users of these installations. This plugin is for them.

= Does this plugin disable the automatic updates in WordPress 3.7 and higher? =

Yes, this plugin completely disables all core update checks, so the automatic update system will be disabled too.

= Can I disable the plugin update notifications too? =

Yes. Please see the [Disable WordPress Plugin Updates](http://wordpress.org/extend/plugins/disable-wordpress-plugin-updates/) plugin.

= Can I disable the theme update notifications too? =

Yes. Please see the [Disable WordPress Theme Updates](http://wordpress.org/extend/plugins/disable-wordpress-theme-updates/) plugin.

== Changelog ==

= 1.4 =
* Preemptive support for WordPress 3.0.

= 1.3 =
* Support for WordPress 2.8.

= 1.2 =
* Bugfix to completely prevent any communication with api.wordpress.org.

= 1.1 =
* Initial release.
