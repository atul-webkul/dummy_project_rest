<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles external links api callback interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Externallinks\Util;

defined( 'ABSPATH' ) || exit;

/**
 * media function interface
 */
interface Mkwc_External_Links_Interface
{
    /**
     * External Links endpoint callback
     * @param $data
     * @return $id
     */
    public function mkwc_get_external_links();
}
