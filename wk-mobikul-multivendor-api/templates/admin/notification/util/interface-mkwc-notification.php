<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles add banner interface.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Notification\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Notification_Interface {
    /**
     * Add Notification
     */
    public function mkwc_add_notification_form();
}
