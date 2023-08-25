<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles add banner functions
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Banner;

use WkMobikulMultivendorApi\Templates\Admin\Banner\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Banner' ) ) {
    /**
     *
     */
    class Mkwc_Banner implements Util\Mkwc_Banner_Interface
    {
        public function mkwc_add_banner_form()
        {
            include 'mkwc-add-banner.phtml';
        }
    }

}
