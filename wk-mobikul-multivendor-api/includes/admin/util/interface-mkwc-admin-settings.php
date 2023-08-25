<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles admin settings interface.
 */

namespace WkMobikulMultivendorApi\Includes\Admin\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Admin_Settings_interface
{
    /**
     * Add Menu
     */
    public function mkwc_add_dashboard_menu();

    /**
     * Enqueue scripts at admin end.
     */
    public function mkwc_admin_script();

    /**
     * Register configuration fields as settings
     */
    public function mkwc_register_settings();

    /**
     * Add ajax functions.
     */
    public function mkwc_admin_banner_ajax();

    /**
     * Save notification function.
     */
    public function mkwc_save_notification_data( $data );

    /**
     * Save banner function .
     */
    public function mkwc_save_banner_data( $data );

    /**
     * Save category function.
     */
    public function mkwc_save_category_data( $data );
}
