<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'test' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'g4@2y}wi)3~eG3~WSt/v*AD7=:WGJtnSvN3}@7cKS6h;$es]{3_vpes2KleA-qAn' );
define( 'SECURE_AUTH_KEY',  'OivhmUZgbQ1=DT8)8O0`Ex9a,R;67V#Q_B=#<Ph<+*x;uBaIX,jI4;T#YMYS*P]l' );
define( 'LOGGED_IN_KEY',    ':kw5C57AaGRBF0UGk6TBNdU,6T))2TK<2Q=}e^w9+_su[se}:QP~*iIXA52[,i~L' );
define( 'NONCE_KEY',        ',!GmL_gCa{+Z<uB**i/WF*`C`hih=}0{xRa7(@b<<_k^VDcvLnoKO_TL#W1Jw=G{' );
define( 'AUTH_SALT',        'NY<g$_;FD,VB3N6,pBnUCO#,W+d3J}asg 2O]uTxddcrD#IV#x5$~Y|kK;fMi}W+' );
define( 'SECURE_AUTH_SALT', 'j*2T>|O{{w{C=[U#iow@K fWHC6}dd)bBhqxW[sH]] B)n{p!Y6t _u&Y`Dz[iBs' );
define( 'LOGGED_IN_SALT',   'f5 }vyAe]+sY&,ROyvKf46cu*b;yV]/DDc%YR95WyXP4cLz4M`)3KRAI~vH~+i6)' );
define( 'NONCE_SALT',       'p;`$%mt!f2p8,_h/FA0x}?&5/bi<q|~gAIo4>jmQMp1KCYaE-c(X0jzJd,`C>q=,' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
