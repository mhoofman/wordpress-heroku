<?php
/*
Plugin Name:  Disable WordPress Core Updates
Description:  Disables the WordPress core update checking and notification system.
Plugin URI:   http://lud.icro.us/disable-wordpress-core-update/
Version:      1.4
Author:       John Blackbourn
Author URI:   http://johnblackbourn.com/
Props:        Matt Mullenweg and _ck_

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/

# 2.3 to 2.7:
add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ), 2 );
add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );

# 2.8 to 3.0:
remove_action( 'wp_version_check', 'wp_version_check' );
remove_action( 'admin_init', '_maybe_update_core' );
add_filter( 'pre_transient_update_core', create_function( '$a', "return null;" ) );

# 3.0:
add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );

?>