<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles cart add api callback interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Cart\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Cart add function interface
 */
interface Mkwc_Cart_Interface
{
    /**
     * Cart add endpoint callback
     * @param
     * @return $cart_key
     */
     public function mkwc_cart( $product_data );
}
