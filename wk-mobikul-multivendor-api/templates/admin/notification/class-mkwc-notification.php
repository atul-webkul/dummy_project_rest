<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles add banner functions
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Notification;

use WkMobikulMultivendorApi\Templates\Admin\Notification\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Notification' ) ) {
    /**
     *
     */
    class Mkwc_Notification implements Util\Mkwc_Notification_Interface
    {
        public function mkwc_add_notification_form()
        {
            include 'mkwc-add-notification.phtml';
        }
    }

}
