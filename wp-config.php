<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true); //Added by WP-Cache Manager
define( 'WPCACHEHOME', '/var/www/vhosts/ptspaa.com/httpdocs/sumberselera/berjutakejutan/ss/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('DB_NAME', 'wordpress_4e');

/** MySQL database username */
define('DB_USER',       'wordpress_2a3');

/** MySQL database password */
define('DB_PASSWORD',       'H37GenM!6g');

/** MySQL hostname */
define('DB_HOST', '192.168.23.208:3306');

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
define('AUTH_KEY',       '^l7GBPiVW4otTjZ05wNxuvuKsP#NX50ONBLtwT4l(kZFO(#1izFyNXJqvPVrdEg(');
define('SECURE_AUTH_KEY',       'ZOiN8JG2w768(&xX&0iDF@e!yLx*Gm#jW%HvBKz)%qnfYI9frK1f17s)1DDoQl(O');
define('LOGGED_IN_KEY',       '@rGp6FLQscFf&%4R3tH3(%R@W)#*q(5nPUZFBh)o6k1hkLWqv5b6ZD8uXE9cnIoE');
define('NONCE_KEY',       'UzSsAusg%@8sM4M2RjG1&6qa7ke)yDbKV#RlV#dRhTndzS@n7P%@u&D7JycFpmA&');
define('AUTH_SALT',       '1fQ&gf8Hi8axdO1Z57JKuxTXzVnWW6lHXLX3ibntgt9(lL5H1@Vr(xtngfCb##3E');
define('SECURE_AUTH_SALT',       'bPfq@3#NP6S6Z6ym@FMZqc2g8*FRoW@f2Fp0o^#L20tXRA5ox8R(MZRA*TULO5Mm');
define('LOGGED_IN_SALT',       'G6(&CugWcsV30kHBXJqozNvsYMj#B&wkWT8b4hQMYE9EqO5@wqEFx*ZK^r5A2ZD%');
define('NONCE_SALT',       'p@K5UqmpH)IHVQox6inLbJPZk6NjmjnvHpWRJc4@Hddjz*Yo9quGsOp(Nk2&m1ih');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define( 'WP_ALLOW_MULTISITE', true );

define ('FS_METHOD', 'direct');
