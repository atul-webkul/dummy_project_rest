<?php
/**
 *  Plugin Name: Project Manager API
 *  Description: Rest API for project Manager plugin
 *  Author: Webkul
 *  Author URI:
 *  Plugin URI:
 *  Version: 1.0.0
 *  Author URI: https://webkul.com/
 *  Text Domain: wkmbsc
 */

/**
 * Defined namespace
 */
defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

defined( 'WKMBSC_PLUGIN_FILE' ) || define( 'WKMBSC_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
defined( 'WKMBSC_PLUGIN_BASENAME' ) || define( 'WKMBSC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );



// Load core auto-loader.
require __DIR__ . '/inc/class-wkmbsc-autoload.php';

// Include the main Wkmbsc class.
if ( ! class_exists( 'Wkmbsc', false ) ) {
	include_once WKMBSC_PLUGIN_FILE . '/includes/class-wkmbsc.php';
	WKMBSC::get_instance();
}


