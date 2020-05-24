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
define( 'DB_NAME', 'sigma_studio_db' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'v&S)K[%KQF]~d|j%MM_wFtU~{~#Etm>#B5{1Z>_GxLZS{/3`bA>4G(!2PRfBj^DE' );
define( 'SECURE_AUTH_KEY',  '#d#y).)xeL0X/3wz_%E9G;.f1+z<7zZ,LH+l5?&9,R$9|]Ik;vnu?XV^A<!Nk>=B' );
define( 'LOGGED_IN_KEY',    '_fCmd~aVt|e:DNw8n)i7QtbgZRLnSJL*fsKhATN;3Sh6([QX|WoY-e,rTJ+N. HZ' );
define( 'NONCE_KEY',        'Ta/]#.UU|N$&K<1ZzImSr0ys!|,Du*_b4~VXI:8q3ursa6v[Hp[o_x^j8|,g=N:B' );
define( 'AUTH_SALT',        ' 0#$behu=<#R5&I,=~nu*3H`v@>c =y)^mgT+TS1&Zp9YskapqC8Ub0}hlQIyD#y' );
define( 'SECURE_AUTH_SALT', '?Bge7}n 5+0Ygi r&0`*mPm1-5~R9>W%#qpcAS/N8AcNhB-[{cBN.vG,@7~?zH^^' );
define( 'LOGGED_IN_SALT',   '?^*+$2?nD~krG7s?A$jdMwH]ptYR6?!2Yfc=kV|XB]%]yuxGdN}e{~SO;avF%n0D' );
define( 'NONCE_SALT',       'cO=kP/(bX9c)#a{P<?Jl3X{~zRdL:tW9!Yrpbih`TZHpH{G}gGoy-?A{JgH-#LO!' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
// define( 'WP_DEBUG', false );
ini_set('display_errors','Off');
ini_set('error_reporting', E_ALL );
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
