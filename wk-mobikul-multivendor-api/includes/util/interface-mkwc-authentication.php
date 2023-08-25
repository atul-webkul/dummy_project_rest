<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles request authentication interface.
 */

namespace WkMobikulMultivendorApi\Includes\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Authentication_Interface {
    /**
     * Request authenticate function
     */
    public function mkwc_authenticate_request();

    /**
     * Generate random string
     * @param $length
     * @return $random_string
     */
    function mkwc_generate_random_string( $length = 50 );
}
