<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Heroku Postgres settings - from Heroku Environment ** //
$db = parse_url($_ENV["DATABASE_URL"]);

// $db = parse_url("postgres://yzsvnjpxdlarax:AzYW8gHmWmHe1oprxRWclqBrEx@ec2-54-235-70-146.compute-1.amazonaws.com:5432/d1p42j00d9bblr");

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
// define('DB_NAME', trim($db["path"],"/"));
define('DB_NAME', trim($db["path"],"/"));

/** MySQL database username */
// define('DB_USER', $db["user"]);
define('DB_USER', $db["user"]);

/** MySQL database password */
// define('DB_PASSWORD', $db["pass"]);
define('DB_PASSWORD', $db["pass"]);

/** MySQL hostname */
// define('DB_HOST', $db["host"]);
define('DB_HOST', $db["host"]);
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '7t]_yVM,# )=Oaug|-g;F2_m2[TIR<-+bVLjDi}A)G.4Hhwjmp&f{x=~5B pMdJO');
define('SECURE_AUTH_KEY',  'RsY ^e}FdHT|5h&m%ny-U(Wzl7N6q&+KsVNHS%it],y[#JGH%.f+zYSXlclC+IKm');
define('LOGGED_IN_KEY',    'Rds]c~}R]7NGjxGPGis6f:Rc~2z3:#]1)Hc6 _zUu*OQ|JC3iQFX ]}02 NC_E)p');
define('NONCE_KEY',        ',3f*|}-s^-` as.y-0xTANjID<]3|@UY5s_J@Ft&g^btA}XYYQ0=`I+J-*0&w#$l');
define('AUTH_SALT',        ' T0BPSWqjm|^4vn uF,@;ShH-?gv#M[%{m|k|Y<h;+EN<gp0etn!2K%0T<bJ^lcX');
define('SECURE_AUTH_SALT', '+C{,&btzj}T^Cp[8NmR5CIZdw7|YLe.:/LS@YE~G9k&.oX|{3MrzH+|@-e=$-!?W');
define('LOGGED_IN_SALT',   'd<PKq~#|&}{dX(j*$uRn?|9o([|%/tT,2>6R*&|_dLK*oI0xrp(+eA3 Q2j$hNPJ');
define('NONCE_SALT',       'um)N`FvX>%$*y,Kdx!>d!:O+]PHc:BE8/s+k|,i<y[zx?pXbA`tly7(|~Y6$gW+h');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
