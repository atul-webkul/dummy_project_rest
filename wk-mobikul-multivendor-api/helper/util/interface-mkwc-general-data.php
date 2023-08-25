<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles general data helper functions interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_General_Data_Interface {
    /**
     * Return $price in proper format as per WooCommerce configuration
     * @param $price
     * @return $price
     */
    public function mkwc_get_formatted_price( $price = '' );

    /**
     * Return $price for variable product in format
     * @param $from, $to
     * @return $formatted_price
     */
    public function mkwc_get_formatted_price_range( $min_price = 0, $max_price = 0 );

    /**
     * Return price for variable product
     * @param $product_object
     * @return $formatted_price
     */
    public function mkwc_get_variable_product_price( $product_data );

    /**
     * Get dominant color
     */
    public function mkwc_get_dominant_Color($url);
}
