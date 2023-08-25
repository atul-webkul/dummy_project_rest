<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles all seller post data interface.
 */

namespace WkMobikulMultivendorApi\Helper\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Seller_Post_Data_Interface {
    /**
     * Save seller query to admin
     * @param $seller_id, $subject, $message
     * @return bool
     */
    public function mkwc_set_seller_query_admin( $seller_id, $subject, $message );
}
