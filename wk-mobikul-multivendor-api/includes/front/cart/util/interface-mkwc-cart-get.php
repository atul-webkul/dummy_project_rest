<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles cart get api callback interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Cart\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Cart add function interface
 */
interface Mkwc_Cart_Get_Interface
{
    /**
     * Cart Get endpoint callback
     * @param
     * @return $cart_key
     */
     public function mkwc_cart_get();

     /**
      * Cart Get Shipping methods of register user endpoint callback
      * @param $id
      * @return $shipping methods
      */
     public function mkwc_cart_get_shipping_methods();
}
