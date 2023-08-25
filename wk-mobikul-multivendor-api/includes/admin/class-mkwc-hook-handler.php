<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all admin end action hooks.
 */

namespace WkMobikulMultivendorApi\Includes\Admin;

use WkMobikulMultivendorApi\Includes\Admin;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Hook_Handler' ) ) {
    /**
     * Admin end hook handler class
     */
    class Mkwc_Hook_Handler
    {
        public function __construct()
        {
            $function_handler = new Admin\Mkwc_Function_Handler;

            add_action( 'admin_menu', array( $function_handler, 'mkwc_add_dashboard_menu' ), 99 );

            add_action( 'admin_enqueue_scripts', array( $function_handler, 'mkwc_admin_script' ) );

            add_action( 'admin_init', array( $function_handler, 'mkwc_register_settings' ) );

            add_action( 'admin_init', array( $function_handler, 'mkwc_admin_banner_ajax' ) );

            add_action( 'mkwc_save_notification_data' , array( $function_handler , 'mkwc_save_notification_data' ) , 1 );

            add_action( 'mkwc_save_banner_data' , array( $function_handler , 'mkwc_save_banner_data' ) , 1 );

            add_action( 'mkwc_save_category_data' , array( $function_handler , 'mkwc_save_category_data' ) , 1 );

            add_action( 'product_cat_add_form_fields', array ( $function_handler, 'mkwc_add_category_icon' ), 10, 2 );

            add_action( 'product_cat_edit_form_fields', array ( $function_handler, 'mkwc_edit_category_icon' ), 10, 2 );

            add_action( 'create_product_cat', array ( $function_handler, 'mkwc_save_taxonomy_custom_meta' ), 10, 2 );

            add_action( 'edited_product_cat', array ( $function_handler, 'mkwc_save_taxonomy_custom_meta' ), 10, 2 );
        }
    }
}
