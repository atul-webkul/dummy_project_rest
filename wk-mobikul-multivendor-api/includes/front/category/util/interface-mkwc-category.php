<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles category api functions interface.
 */

namespace WkMobikulMultivendorApi\Includes\Front\Category\Util;

defined( 'ABSPATH' ) || exit;

interface Mkwc_Category_Interface {
    /**
     * Get category details by category_id
     * @param $data
     * @return $category_detail
     */
    public function mkwc_get_category( $data );

    /**
     * Get products by category id
     * @param $data
     * @return $category_products
     */
    public function mkwc_get_category_products( $data );
}
