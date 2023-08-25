<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller notifications interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Seller_Notifications_Interface {
    /**
     * Seller notification list
     */
    public function mkwc_get_seller_notifications();
}
