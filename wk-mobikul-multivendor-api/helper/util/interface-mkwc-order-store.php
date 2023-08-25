<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles order store helper function interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Order_Store_Interface {
    /**
     * Get orders by customer id
     * @param $customer_id
     * @return $order_ids
     */
    public function mkwc_get_customer_orders( $customer_id );
}
