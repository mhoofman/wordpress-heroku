<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

$db = parse_url($_ENV["DATABASE_URL"]);

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', trim($db["path"],"/"));

/** MySQL database username */
define('DB_USER', $db["user"]);

/** MySQL database password */
define('DB_PASSWORD', $db["pass"]);

/** MySQL hostname */
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
define('AUTH_KEY',         'vpkdA8. jDEw;)*P.Y*5fdFuzNt.=N>l>j-=joY}G[3t`a|1dLO#t_,Ay?RXol>a');
define('SECURE_AUTH_KEY',  'EN+])[`+1[~b) ,aX1/X#IVjp|TgN^0*._Y.Z>*]qGP0=%R>RNIP=&ap;0|$[_i~');
define('LOGGED_IN_KEY',    'JU70-!j9@t~yD+{f2qzL^+l8xam/U-Ma9<=g+u44F)joz$rc$Apr:-sp#jN[<=-=');
define('NONCE_KEY',        '+-%/Qq.Z6 5+PSji>Ac7N--<7txEG-!r+Dr/462Wu5D eh3$^s:b`7?&joze~|_H');
define('AUTH_SALT',        'iv[S0JH+P|=1QGq>@}0O[SA(h-gjcp{Un;)$1 X<N]vhklNY{t#95[y-Q*eD>9G<');
define('SECURE_AUTH_SALT', 'oP5L^~>f7j Kw-f`ws36}K8$}p_Cb@DA0H+[;&~P|X$5;:`Sxq  ptT:D( Xc;J}');
define('LOGGED_IN_SALT',   '2ep0SQ~MI}J$%dTL|(4qC0EDzG1*^!YX-/So}Qc;9~4.<$QA4ibjQZ(.=42-Zn+4');
define('NONCE_SALT',       'tRK}zlSur^wQ>7#^r+;2yTC<6*G+D|_7?0s3N=F][-)(~Z`*//ljX<q8=Hd],~tJ');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'rs_';

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
