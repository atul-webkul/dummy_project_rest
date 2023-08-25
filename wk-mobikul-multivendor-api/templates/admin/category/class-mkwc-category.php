<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles add banner functions
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Category;

use WkMobikulMultivendorApi\Templates\Admin\Category\Util;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Category' ) ) {
    /**
     *
     */
    class Mkwc_Category implements Util\Mkwc_Category_Interface
    {
        public function mkwc_add_category_form()
        {
            include 'mkwc-add-category.phtml';
        }
    }

}
