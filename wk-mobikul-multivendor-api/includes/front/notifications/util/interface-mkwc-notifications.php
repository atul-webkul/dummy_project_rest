<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles external links api callback interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Notifications\Util;

defined( 'ABSPATH' ) || exit;

/**
 * media function interface
 */
interface Mkwc_Notifications_Interface
{
    /**
     * Notifications endpoint callback
     * @param $data
     * @return $id
     */
    public function mkwc_get_notifications();
}
