=== WP Read-Only ===

* Contributors: alfreddatakillen
* Tags: wordpress, amazon, s3, readonly
* Requires at least: 3.3
* Tested up to: 3.3.1
* Stable tag: 1.0
* License: GPLv2

Plugin for running your Wordpress site without Write Access to the
web directory. Amazon S3 is used for uploads/binary storage.

== Description ==

This plugin was made with cluster/load balancing server setups in
mind - where you do not want your WordPress to write anything to
the local web directory.

WPRO will put your media uploads on Amazon S3. Unlike other
S3 plugins, this plugin does not require your uploads to first be
stored in your server's upload directory, so this plugin will work
fine on WordPress sites where the web server have read-only access
to the web directory.

*	Wordpress image editing will still work fine (just somewhat slower).
*	Full support for XMLRPC uploads.

This plugin was made for Wordpress sites deployed in a (load balancing)
cluster across multiple webservers, where you do not want your WordPress
to write anything to the local web directory.

Note: You still need write access to the system /tmp directory for
this plugin to work. It will use the system /tmp directory for
temporary storage during uploads, image editing/scaling, etc.

= Wordpress MU =

We did not test this plugin in a Wordpress MU environment.
It will probably not work out-of-the-box for Wordpress MU.

== Installation ==

1. Put the plugin in the Wordpress `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Enter your Amazon S3 settings in `Settings` > `WPRO Settings`.

== Frequently Asked Questions ==

= Will this plugin work in Wordpress MU environments? =

Probably not, but I am not sure. Input and/or code is very welcome!

= Where do I report bugs? = 

Report any issues at the github issue tracker:
https://github.com/alfreddatakillen/wpro/issues

= Where do I contribute with code, bug fixes, etc.? =

At github:
https://github.com/alfreddatakillen/wpro

= What should I think of when digging the code? =

If you define the constant WPRO_DEBUG in your wp-config.php, then
some debug data will be written to /tmp/wpro-debug

= What about the license? =

Read more about GPLv2 here:
http://www.gnu.org/licenses/gpl-2.0.html

= Do you like beer? =

If we meet some day, and you think this stuff is worth it, you may buy
me a beer in return. (GPLv2 still applies.)

== Changelog ==

= 1.0 =

*	The first public release.

