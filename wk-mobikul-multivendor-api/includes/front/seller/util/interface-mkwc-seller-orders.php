<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller orders interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Seller_Orders_Interface {
    /**
     * Seller Orders list
     */
    public function mkwc_get_seller_orders_list();

    /**
     * Get seller order details by id
     * @param $params
     */
    public function mkwc_get_order_by_id( $param );
}
