<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all cart data interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Cart_Data_Interface {
    /**
     * Save seller query to admin
     * @param $seller_id, $subject, $message
     * @return bool
     */
    public function mkwc_cart_request_handler( $product_id, $user_id, $quantity = 1, $variation_id = 0, $variations = array(), $coupons = array() );

    /**
     * get cart item by customer id
     * @param $customer_id
     * @return $cart_data
     */
    public function mkwc_registered_cart_items( $customer_id );

    /**
     * get coupon message by coupon code
     * @param $coupon_codes
     * @return $message
     */
    public function mkwc_get_coupon_message( $coupon_codes );
}
