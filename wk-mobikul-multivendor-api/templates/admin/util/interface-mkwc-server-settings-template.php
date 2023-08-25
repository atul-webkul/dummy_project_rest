<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles admin settings template interface.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Server_Settings_Template_Interface {
    /**
     * Manage banners admin menu
     */
    public function mkwc_manage_app_banners();

    /**
     * Manage featured products admin menu
     */
    public function mkwc_manage_app_featured_products();

    /**
     * Manage featured categories admin menu
     */
    public function mkwc_manage_app_featured_categories();

    /**
     * Manage notifications admin menu
     */
    public function mkwc_manage_app_notifications();

    /**
     * Manage settings admin menu
     */
    public function mkwc_manage_app_settings();

    /**
    * Manage banner type
    */
    public function mkwc_select_banner_type();

    /**
    * Carousels banner type
    */
    public function mkwc_select_carousels_type();
}
