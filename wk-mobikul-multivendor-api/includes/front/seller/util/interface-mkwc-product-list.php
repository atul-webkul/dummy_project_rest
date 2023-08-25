<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller products list interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Product_List_Interface {
    /**
     * Seller product list
     */
    public function mkwc_get_product_list();
}
