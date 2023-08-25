<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles add banner interface.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Banner\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Banner_Interface {
    /**
     * Add banner
     */
    public function mkwc_add_banner_form();
}
