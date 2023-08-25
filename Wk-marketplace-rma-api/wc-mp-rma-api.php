<?php
/**
 * Plugin Name: Marketplace RMA Api
 * Description: simple rest api
 * Author: This API gives data related to marketplace refund data, create refund, show refund list for seller etc.
 * Version: 1.0.0
 * Author: Webkul
 * Author URI: https://webkul.com
 * Text Domain: wkmpapi
 * Requires at least: 5.0
 * Requires PHP: 7.3
 * WC requires at least: 5.0
 *
 * License: license.txt included with plugin
 * License URI: https://store.webkul.com/license.html
 *
 * @package Marketplace RMA Api


/**
 * Defined namspace
 */
defined( 'WKMP_RMA_API_FILE' ) || define( 'WKMP_RMA_API_FILE', __FILE__ );
defined( 'WKMP_RMA_API_PLUGIN_FILE' ) || define( 'WKMP_RMA_API_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );

/**
 * Includes autoload files
 */
require WKMP_RMA_API_PLUGIN_FILE . '/inc/class-wkmpapi-autoload.php';


// Include the main wkmpapi class.
if ( ! class_exists( 'Wkwceupi', false ) ) {
	include_once WKMP_RMA_API_PLUGIN_FILE . '/includes/class-wkmpapi.php';
	WKMPAPI::get_instance();
}
