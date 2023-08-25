<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller ask to admin interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Seller_Ask_admin_Interface {
    /**
     * Seller asked queries list
     */
    public function mkwc_get_seller_asked_queries();

    /**
     * Submit query to admin
     */
    public function mkwc_seller_send_query( $data );
}
