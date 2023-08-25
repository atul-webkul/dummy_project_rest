<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles front action callbacks.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Front_Functions_interface {
    /**
     * Register Routes
     */
    public function mkwc_register_routes();

    /**
     * Check seller api requests have seller id
     */
    public function mkwc_check_seller_id();

    /**
     * Regsiter Seller API Endpoints
     */
    public function mkwc_seller_api_endpoints();

    /**
     * Check product api requests have width param
     */
    public function mkwc_check_width_product_apis();

    /**
     * Register category api endpoints
     */
    public function mkwc_category_api_endpoints();

    /**
     * Register homepage endpoints
     */
    function mkwc_homepage_api_endpoints();

    /**
     * Countries api endpoints
     */
    public function mkwc_countries_api_endpoints();

    /**
     * Cart API Endpoints
     */
    public function mkwc_cart_api_endpoints();

    /**
     * Checkout API Endpoints
     */
    public function mkwc_checkout_api_endpoints();

    /**
     * Media upload API Endpoints
     */
    public function mkwc_media_upload_endpoints();

    /**
     * External links API Endpoints
     */
    public function mkwc_external_links_endpoints();

    /**
     * Notifications API Endpoints
     */
    public function mkwc_notifications_endpoints();
}
