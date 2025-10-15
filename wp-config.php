<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'sim' );

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
define( 'AUTH_KEY',         '{&4o&}^L]Fge~C5/ pBS8BB?(K_5/$@gtj{.E&b+,fh~5O%T^~lZbx{:yUpC6s<&' );
define( 'SECURE_AUTH_KEY',  'Z@A]^qhoSuly)JfdY-6Af_X%nlc{1_LNw*[@#FZ@XYS5W4{9?= =%k!ZgaYc*RDF' );
define( 'LOGGED_IN_KEY',    ';2MaBIbG%G&Nwq8Y@q)VI|tsMZBQ6{6)g_Q1Y#{7VT24e}wo)V#(|T)~.Zm6CU$z' );
define( 'NONCE_KEY',        'lms=Toe=~c+#eu/g*EfJ,aLWat6B)-<(]~<FBBe+L1M1FCRb6aVku*zF)rQ |XnT' );
define( 'AUTH_SALT',        'i)-$/}|F#:ccS`U]7nn1Y!T:7_ >@z1Cx<OiCcn!G2;~m0Cf-C^>~0{vM%5LSPV_' );
define( 'SECURE_AUTH_SALT', '[#Sw4gte[@NHw=48XeFi7w.O4]?hmGD]#xEf4y)[@>>9Ff31jo-?L;iZV8PRjX^V' );
define( 'LOGGED_IN_SALT',   'kC>-zd7?o2M`sR*lou-?4z4my6l,htppb9d&qkP+@H/6j6?s?%DZ4#fc>hP-tT1[' );
define( 'NONCE_SALT',       'MSY0).NDCswu4I~kR?v;mRlm|^X?)9|u7)0Ki&:THCoxy?8$.9hKAL<S:~@L?OWE' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

define('WP_HOME', 'http://localhost/sim');
define('WP_SITEURL', 'http://localhost/sim');

// define('WP_HOME', 'https://subelongated-jamal-violably.ngrok-free.dev/sim');
// define('WP_SITEURL', 'https://subelongated-jamal-violably.ngrok-free.dev/sim');

// if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
//     $_SERVER['HTTPS'] = 'on';
// }

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
