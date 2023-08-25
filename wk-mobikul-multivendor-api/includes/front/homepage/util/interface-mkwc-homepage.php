<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles homepage functions interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Homepage\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Homepage_Interface {
    /**
     * App homepage data
     */
    public function mkwc_get_homepage();

    /**
     * Get homepage banners
     * @param $width
     * @return $banners
     */
    public function mkwc_get_banners( $width );

    /**
     * Get new products
     * @param $width
     * @return array $new_products
     */
    public function mkwc_get_new_products( $width );

    /**
     * Get featured products
     * @param $width
     * @return array $featured_products
     */
    public function mkwc_get_featured_products( $width );

    /**
     * WooCommerce default settings
     */
    public function mkwc_default_settings();

    /**
     * Get store categories
     * @return $categories
     */
    public function mkwc_get_categories();
}
