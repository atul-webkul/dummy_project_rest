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
interface Mkwc_Cart_Set_Payment_Method_Interface
{
    /**
     * Cart payment method endpoint callback
     * @param
     * @return $cart
     */
     public function mkwc_cart_set_payment_method( $data );
}
