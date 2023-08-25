<?php

/**
 * Plugin Name: Mobikul Marketplace
 * Description: Mobikul Marketplace converts your marketplace store for WooCommerce into a mobile app Android/IOS.
 * Author: Webkul
 * Author URI: https://webkul.com
 * Plugin URI: https://store.webkul.com/woocommerce-multi-vendor-mobile-app.html
 * Version: 2.0.0
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.x
 * License: license.txt included with plugin
 * License URI: https://store.webkul.com/license.html
 * Text Domain: mobikul_marketplace
 */

// mkwc: Mobikul WooCommerce

defined( 'ABSPATH' ) || exit;

! defined( 'MKWC_URL' ) && define( 'MKWC_URL', plugin_dir_url( __FILE__ ) );

! defined( 'MKWC_FILE' ) && define( 'MKWC_FILE', plugin_dir_path( __FILE__ ) );

! defined( 'MKWC_SCRIPT_VERSION' ) && define( 'MKWC_SCRIPT_VERSION', '1.0.0' );

! defined( 'MKWC_NAMESPACE' ) && define( 'MKWC_NAMESPACE', 'mobikul/v1' );

register_activation_hook( __FILE__, 'mkwc_plugin_activate_create_db' );

add_action(
	'plugins_loaded',
	function () {
		WC()->frontend_includes();
	}
);

if ( ! function_exists( 'mkwc_multivendor_api_install' ) ) {
	function mkwc_multivendor_api_install() {
		if ( ! class_exists( 'Marketplace' ) ) {
			add_action( 'admin_notices', 'mkwc_install_marketplace_admin_notice' );
		} else {
			new MkWcMultiVendorApi();
			do_action( 'mkwc_init' );
		}
		load_plugin_textdomain( 'mobikul_marketplace', false, basename( dirname( __FILE__ ) ) . '/languages' );

	}
	add_action( 'plugins_loaded', 'mkwc_multivendor_api_install', 11 );
}

/**
 * Create required sql tables.
 */
function mkwc_plugin_activate_create_db() {
	 require_once MKWC_FILE . 'helper/class-mkwc-sql-create-table.php';
}

/**
 * Admin notice function for Marketplace not found
 */
function mkwc_install_marketplace_admin_notice() {  ?>
	<div class="error">
		<p><?php echo sprintf( __( 'Mobikul Marketplace is enabled but not effective. It requires <a href="%s" target="_blank">Marketplace Plugin</a> in order to work.', 'mobikul_marketplace' ), 'https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/19214408' ); ?></p>
	</div>
	<?php
}

if ( ! class_exists( 'MkWcMultiVendorApi' ) ) {
	class MkWcMultiVendorApi {

		public function __construct() {
			add_action( 'mkwc_init', array( $this, 'mkwc_includes' ) );

			add_action( 'wp_login', array( $this, 'mkwc_update_session_login' ), 10, 2 );

			add_action( 'wp_logout', array( $this, 'mkwc_update_session_logout' ), 10, 2 );
		}

		public function mkwc_includes() {
			require_once MKWC_FILE . 'includes/mkwc-file-handler.php';
		}

		/**
		 * Update user sesion on login.
		 *
		 * @param  $user
		 * @return $user_meta
		 */
		public function mkwc_update_session_login( $user_login, $user ) {
			$cookie = WC()->session->get_session_cookie();

			if ( $cookie && isset( $cookie[0] ) ) {
				update_user_meta( $user->ID, 'user_web_session', $cookie[0] );
			} else {
				update_user_meta( $user->ID, 'user_web_session', '' );
			}
		}

		/**
		 * Update user sesion on logout.
		 *
		 * @param  $curent_user
		 * @return $user_meta
		 */
		public function mkwc_update_session_logout() {
			$user = wp_get_current_user();

			update_user_meta( $user->ID, 'user_web_session', '' );
		}
	}
}

/**
 * Debug error log.
 *
 * @param  $data
 * @return $data
 */
function mkwc_error_log( $data ) {
	$log_request_data  = file_get_contents( MKWC_FILE . 'request.txt' );
	$log_request_data .= print_r( $data, true );
	file_put_contents( MKWC_FILE . 'request.txt', $log_request_data );
}


function wkmp_get_seller_id_by_shop_address( $shop_address ) {
	global $wpdb;
	$wpdb_obj  = $wpdb;
	$seller_id = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key=%s AND meta_value=%s", esc_sql( 'shop_address' ), esc_sql( $shop_address ) ) );

	return apply_filters( 'wkmp_get_seller_id_by_shop_address', $seller_id, $shop_address );
}

add_filter( 'wp_image_editors', 'change_graphic_lib', 999 );
function change_graphic_lib( $array ) {
	return array( 'WP_Image_Editor_GD', 'WP_Image_Editor_Imagick' );
}

// add_action( 'init', 'test' );

function test() {
	global $wpdb;
	// $oo                            = pmpro_getMembershipLevelsForUser( 1732 );
	$wpdb->pmpro_membership_levels = $wpdb->prefix . 'pmpro_membership_levels';
	$wpdb->pmpro_memberships_users = $wpdb->prefix . 'pmpro_memberships_users';
	echo 'demo';
		$include_inactive = true;
		$levels           = $wpdb->get_results(
			"SELECT
				l.id AS ID,
				l.id as id,
				mu.id as subscription_id,
				l.name,
				l.description,
				l.confirmation,
				l.expiration_number,
				l.expiration_period,
				mu.initial_payment,
				mu.billing_amount,
				mu.cycle_number,
				mu.cycle_period,
				mu.billing_limit,
				mu.trial_amount,
				mu.trial_limit,
				mu.code_id as code_id,
				UNIX_TIMESTAMP(CONVERT_TZ(startdate, '+00:00', @@global.time_zone)) as startdate,
				UNIX_TIMESTAMP(CONVERT_TZ(enddate, '+00:00', @@global.time_zone)) as enddate
			FROM {$wpdb->pmpro_membership_levels} AS l
			JOIN {$wpdb->pmpro_memberships_users} AS mu ON (l.id = mu.membership_id)
			WHERE mu.user_id = 1732" . ( $include_inactive ? '' : " AND mu.status = 'active'
			GROUP BY ID" )
		);

	echo '<pre>';
	print_r( $levels );
	echo '</pre>';
}


// add_filter('pmpro_levels_array', 'chn_pmpro_levels_array1', 1, 99);

function chn_pmpro_levels_array1( $pmpro_levels ) {

	global $wpdb, $current_user;

	$current_user_level = 0;
	if ( isset( $current_user->membership_level->ID ) ) {
		$current_user_level = $current_user->membership_level->ID;
	}

	if ( is_user_logged_in() && ! is_admin() && ! current_user_can( 'manage_options' ) ) {

		$user_id = get_current_user_id();

		$user_meta = get_userdata( $user_id );

		$user_roles = $user_meta->roles; // array of roles the user is part of.

		$user_role = "'" . implode( "','", $user_roles ) . "'";

		if ( in_array( 'subscriber', $user_roles ) ) {
			$assigned_role = $wpdb->get_results( 'SELECT plan_id FROM ' . $wpdb->prefix . "assigned_plans WHERE role IN ('wk_marketplace_seller')" );
		} else {
			$assigned_role = $wpdb->get_results( 'SELECT plan_id FROM ' . $wpdb->prefix . 'assigned_plans WHERE role IN (' . $user_role . ')' );
		}
		$assigned_roles_arr = array();
		foreach ( $assigned_role as $data ) {
			$assigned_roles_arr[] = $data->plan_id;
		}

		$newpmpro_levels = array();
		foreach ( $pmpro_levels as $key => $data ) {
			if ( in_array( $data->id, $assigned_roles_arr ) ) {
				$newpmpro_levels[] = array(
					'id'                => $data->id,
					'name'              => $data->name,
					'description'       => $data->description,
					'confirmation'      => $data->confirmation,
					'initial_payment'   => $data->initial_payment,
					'cycle_number'      => $data->cycle_number,
					'cycle_period'      => $data->cycle_period,
					'billing_limit'     => $data->billing_limit,
					'trial_amount'      => $data->trial_amount,
					'trial_limit'       => $data->trial_limit,
					'allow_signups'     => $data->allow_signups,
					'expiration_number' => $data->expiration_number,
					'expiration_period' => $data->expiration_period,

				);
			}
		}

		return $newpmpro_levels;

	}

	return $pmpro_levels;
}

