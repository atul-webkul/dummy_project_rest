<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller dashboard interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Dashboard_Interface {
    /**
     * App homepage data
     */
    public function mkwc_get_dashboard();
}
