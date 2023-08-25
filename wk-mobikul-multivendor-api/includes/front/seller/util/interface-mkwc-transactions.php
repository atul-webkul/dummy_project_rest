<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles seller transactions interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Seller\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Transactions_Interface {
    /**
     * Seller transactions data
     */
    public function mkwc_get_transactions();

    /**
     * Return seller transactions
     */
    public function mkwc_get_transaction_by_id( $params );
}
