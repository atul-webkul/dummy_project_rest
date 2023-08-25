<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles products api functions interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Products\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Products_Interface {
    /**
     * Store product list endpoint callback
     */
    public function mkwc_get_products();

    /**
     * Get product by id
     * @param $data
     * @return $product
     */
    public function mkwc_get_product( $data );

    /**
     * Get product by search query endpoint callback
     * @return $products
     */
    public function mkwc_get_search_product();

    /**
     * Get product review by product id
     * @param $data
     * @return $reviews
     */
    public function mkwc_get_product_reviews( $data );

    /**
     * Set product review
     * @param $post_data object
     * @return $status
     */
    public function mkwc_set_product_review( $data );
}
