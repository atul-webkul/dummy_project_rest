<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller shop_followers interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Seller_Shop_Details_Interface {
    /**
     * Seller shop details
     */
    public function mkwc_get_seller_shop_details();
}
