<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles add carousels functions
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Carousels;

use WkMobikulMultivendorApi\Templates\Admin\Carousels\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Carousels' ) ) {
    /**
     *
     */
    class Mkwc_Carousels implements Util\Mkwc_Carousels_Interface
    {
        public function mkwc_add_carousels_form()
        {
            include 'mkwc-add-carousels.phtml';
        }
    }

}
