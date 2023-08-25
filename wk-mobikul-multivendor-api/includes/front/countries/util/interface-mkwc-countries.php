<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles users api callback interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Countries\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Countries function interface
 */
interface Mkwc_Countries_Interface
{
    /**
     * Get countries endpoint callback
     * @return $countries
     *
     */
    public function mkwc_get_countries();

}
