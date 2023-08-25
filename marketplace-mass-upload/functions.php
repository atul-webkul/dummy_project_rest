<?php
/**
 * Plugin Name: Marketplace Product Mass Upload
 * Plugin URI: https://store.webkul.com/wordpress-woocommerce-marketplace-mass-upload.html
 * Description: WordPress WooCommerce Marketplace Product Mass Upload add-on is useful for bulk upload products. Using this Marketplace add-on Seller can upload the products in bulk using CSV.
 * Version: 1.0.0
 * Author: Webkul
 * Author URI: http://webkul.com
 * Domain Path: plugins/wp-marketplace-mass-upload
 * License: GNU/GPL for more info see license.txt included with plugin
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'WP_MASS_UPLOAD', plugin_dir_url(__FILE__));

if (!class_exists('MP_MASS_UPLOAD')) {
	

	/**
	* 
	*/
	class MP_MASS_UPLOAD {
		
		function __construct() {

			ob_start();
			
			register_activation_hook(__FILE__, array( $this, 'wk_mu_check_marketplace_is_installed' ) );

			require_once(sprintf("%s/includes/class-admin-menus.php", dirname(__FILE__)));

			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'wk_mu_plugin_settings_link' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'wk_mu_admin_scripts' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'wk_mu_front_scripts' ) );

			add_action('marketplace_list_seller_option',array($this,'add_wk_mu_tab_list'), 10, 1 );

			add_action('wp_head', array( $this, 'wk_mu_calling_pages') );

		}

		function wk_mu_plugin_settings_link($links) {
			
			$url = 'https://wordpressdemo.webkul.com';
			
			$settings_link = '<a href="'.$url.'" target="_blank" style="color:green;">' . __( 'More Add-ons', 'wk_mu' ) . '</a>';
			
			$links[] = $settings_link;
			
			return $links;

		}

		function wk_mu_check_marketplace_is_installed() {

			require_once(sprintf("%s/install.php", dirname(__FILE__)));

			$install_obj = new Mass_Upload_Install();

			$install_obj->wk_mu_install();

		}

		function wk_mu_admin_scripts() {

			wp_enqueue_script( 'wk_mass_admin_js', WP_MASS_UPLOAD. 'assets/js/plugin-admin.js', array( 'jquery' ) );

			wp_enqueue_style( 'wk_mass_admin_js', WP_MASS_UPLOAD.'assets/css/style.css');

		}

		function wk_mu_front_scripts() {

			wp_enqueue_script( 'wk_mass_front_js', WP_MASS_UPLOAD. 'assets/js/plugin-admin.js', array( 'jquery' ) );

			wp_enqueue_style( 'wk_mass_front_css', WP_MASS_UPLOAD.'assets/css/style.css');

		}

		function add_wk_mu_tab_list($page_name) {

			echo '<li class="wkmp-selleritem"><a href="'.home_url("/".$page_name."/mass-upload").'">Mass Upload</a></li>';

		}

		function wk_mu_calling_pages() {

			global $current_user,$wpdb; 
	        
	        $current_user = wp_get_current_user();
	        
	        $seller_info = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."mpsellerinfo WHERE user_id = '".$current_user->ID ."' and seller_value='seller'");
	        
	        $pagename = get_query_var('pagename');
	        
	        $main_page = get_query_var('main_page');   
	        
	        if( !empty($pagename) ){
	        
	            if( $main_page == "mass-upload" && ( $current_user->ID || $seller_info > 0 ) ) {
	                
	                require_once 'includes/admin/admin-mass-upload.php';
	               
	                add_shortcode( 'marketplace', 'wk_mu_csv' );
	            
	            }

	            if( $main_page == "run-profile" && ( $current_user->ID || $seller_info > 0 ) ) {
	                
	                require_once 'includes/front/run-profile.php';
	               
	                add_shortcode( 'marketplace', 'wk_mu_front_profile' );
	            
	            }

	            if( $main_page == "process-csv" && ( $current_user->ID || $seller_info > 0 ) ) {
	                
	                require_once 'includes/process-csv-data.php';
	               
	                add_shortcode( 'marketplace', 'wk_mu_process_csv' );
	            
	            }
	           
	        }

		}

	}

	new MP_MASS_UPLOAD();

}

