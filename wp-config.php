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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
//define( 'DB_NAME', 'bitnami_wordpress' );
define( 'DB_NAME', 'chn_stage_db' );

/** MySQL database username */
define( 'DB_USER', 'bn_wordpress' );

/** MySQL database password */
define( 'DB_PASSWORD', '9dacda7689' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define('AUTH_KEY', 'a00e5745e326933917ea393c5d48e4116cdff46627a2fec63a1232888f0c4971');
define('SECURE_AUTH_KEY', 'df84a2fc74452c15f1b895e539526fee82c44c14a543a7b9e6bad87c1e8362f2');
define('LOGGED_IN_KEY', '5b8a1a12ce18565231568cdf117e8e0d99faed4a1f35359884018a9edd63328a');
define('NONCE_KEY', '3886673b70bca62661315457818abcdc2796a020b6c8f7d6645b9ca8ae3c43e0');
define('AUTH_SALT', '3f44686608b5e264c2b9568b5fa529335bd561cf9d546bcaced353e2545edfe1');
define('SECURE_AUTH_SALT', '865b5fdc959ced86e46b801ddfca391e66f80cf818cb54762e00764a4198d93d');
define('LOGGED_IN_SALT', '741bb1b76dea1315157487ea6c7b65c7f1f5db1dbdd01874f52013d2c42d714a');
define('NONCE_SALT', '4154c7b80829401da6f441e1ab3e303e9712711ba2ab6294954bff642aebfae6');

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
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */

define( 'WP_DEBUG', true );
@ini_set('log_errors', 'On');
@ini_set('error_log',  '/opt/bitnami/apps/wordpress/htdocs/wp-content/custom-error-log.log');


//Don't show errors to site visitors.
@ini_set('display_errors', 'Off');
if ( !defined('WP_DEBUG_DISPLAY') ) {
	define('WP_DEBUG_DISPLAY', false);
}
error_log("test log");

/* That's all, stop editing! Happy publishing. */

define('FS_METHOD', 'direct');
unset ($_SERVER['PATH_INFO']);
/**
 * The WP_SITEURL and WP_HOME options are configured to access from any hostname or IP address.
 * If you want to access only from an specific domain, you can modify them. For example:
 *  define('WP_HOME','http://example.com');
 *  define('WP_SITEURL','http://example.com');
 *
*/

if ( defined( 'WP_CLI' ) ) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

define('WP_SITEURL', 'https://' . $_SERVER['HTTP_HOST'] . '/');
define('WP_HOME', 'https://' . $_SERVER['HTTP_HOST'] . '/');


/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

define('WP_TEMP_DIR', '/opt/bitnami/apps/wordpress/tmp');




//  Disable pingback.ping xmlrpc method to prevent Wordpress from participating in DDoS attacks
//  More info at: https://docs.bitnami.com/general/apps/wordpress/troubleshooting/xmlrpc-and-pingback/

if ( !defined( 'WP_CLI' ) ) {
    // remove x-pingback HTTP header
    add_filter('wp_headers', function($headers) {
        unset($headers['X-Pingback']);
        return $headers;
    });
    // disable pingbacks
    add_filter( 'xmlrpc_methods', function( $methods ) {
            unset( $methods['pingback.ping'] );
            return $methods;
    });
    add_filter( 'auto_update_translation', '__return_false' );
}
