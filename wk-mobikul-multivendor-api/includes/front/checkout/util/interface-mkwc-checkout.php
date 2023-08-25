<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles checkout api callback interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Checkout\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Checkout function interface
 */
interface Mkwc_Checkout_Interface
{
    /**
     * Payment method endpoint callback
     * @param
     * @return $payment_methods
     */
    public function mkwc_payment_methods();

    /**
     * Place Order endpoint callback
     * @param
     * @return $order_details
     */
    public function mkwc_place_order( $order );
}
